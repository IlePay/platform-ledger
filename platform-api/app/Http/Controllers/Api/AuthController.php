<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|unique:users',
            'pin' => 'required|string|min:4|max:6',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        // Crée le compte ledger
        $ledgerAccount = $this->ledger->createAccount(
    "user_" . str_replace('+', '', $validated['phone']),
            'USER',
            'XAF'
        );

        if (!$ledgerAccount) {
            return response()->json([
                'message' => 'Failed to create ledger account'
            ], 500);
        }

        // Crée l'utilisateur
        $user = User::create([
    'name' => trim("{$validated['first_name']} {$validated['last_name']}") ?: $validated['phone'],
            'phone' => $validated['phone'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'pin' => Hash::make($validated['pin']),
            'ledger_account_id' => $ledgerAccount['id'],
            'kyc_level' => 'BASIC',
            'daily_limit' => 50000,
            'monthly_limit' => 500000,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'ledger_account' => $ledgerAccount,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->pin, $user->pin)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is inactive'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
