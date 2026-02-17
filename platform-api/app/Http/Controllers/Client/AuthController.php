<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function showRegister()
    {
        return view('client.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'pin' => 'required|string|min:4|max:6|confirmed',
        ]);

        // Crée compte ledger
        $ledgerAccount = $this->ledger->createAccount(
            "user_" . str_replace('+', '', $validated['phone']),
            'USER',
            'XAF'
        );

        if (!$ledgerAccount) {
            return back()->withErrors(['error' => 'Erreur lors de la création du compte']);
        }

        // Crée utilisateur
        $user = User::create([
            'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'pin' => Hash::make($validated['pin']),
            'ledger_account_id' => $ledgerAccount['id'],
            'kyc_level' => 'BASIC',
            'daily_limit' => 50000,
            'monthly_limit' => 500000,
            'is_active' => true,
            'role' => 'USER',
        ]);

        Auth::login($user);

        return redirect()->route('client.dashboard');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['pin'], $user->pin)) {
            return back()->withErrors(['error' => 'Identifiants incorrects']);
        }

        Auth::login($user);

        return redirect()->route('client.dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function showRegisterMerchant()
{
    return view('client.auth.register-merchant');
}

public function registerMerchant(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone' => 'required|string|unique:users,phone',
        'business_name' => 'required|string|max:255',
        'business_type' => 'required|string',
        'pin' => 'required|string|min:4|max:6|confirmed',
    ]);

    // Crée compte ledger
    $ledgerAccount = $this->ledger->createAccount(
        "merchant_" . str_replace('+', '', $validated['phone']),
        'MERCHANT',
        'XAF'
    );

    if (!$ledgerAccount) {
        return back()->withErrors(['error' => 'Erreur lors de la création du compte']);
    }

    // Crée utilisateur marchand avec limites élevées
        $user = User::create([
        'name' => $validated['business_name'],
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'phone' => $validated['phone'],
        'pin' => Hash::make($validated['pin']),
        'ledger_account_id' => $ledgerAccount['id'],
        'account_type' => 'MERCHANT',
        'business_name' => $validated['business_name'],
        'business_type' => $validated['business_type'],
        'qr_code' => 'M' . strtoupper(\Str::random(8)), // ← AJOUTE ÇA
        'kyc_level' => 'STANDARD',
        'daily_limit' => 500000,
        'monthly_limit' => 5000000,
        'is_active' => true,
        'role' => 'USER',
    ]);

    Auth::login($user);

    return redirect()->route('client.dashboard');
}
}