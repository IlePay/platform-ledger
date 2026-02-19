<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function showRegister()
    {
        return view('client.auth.register');
    }

    public function showRegisterMerchant()
    {
        return view('client.auth.register-merchant');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'pin' => 'required',
        ]);

        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !\Hash::check($credentials['pin'], $user->pin)) {
            if ($user) {
                LoginHistory::log($user->id, $request, false, 'Invalid credentials');
            }
            return back()->withErrors(['error' => 'Identifiants incorrects']);
        }

        // Si 2FA activé
        if ($user->two_factor_enabled) {
            $code = $user->generate2FACode();
            
            if ($user->sms_notifications) {
                app(\App\Services\SMS\SmsManager::class)->send(
                    $user->phone,
                    "🔐 IlePay: Votre code de connexion est {$code}. Valide 5 minutes."
                );
            }
            
            session(['2fa_user_id' => $user->id]);
            return redirect()->route('2fa.verify');
        }

        // Login direct
        auth()->login($user);
        LoginHistory::log($user->id, $request, true);
        
        if ($user->isSuspiciousLogin($request)) {
            $user->notify(new \App\Notifications\SuspiciousLogin($request->ip(), now()));
        }

        return redirect()->route('client.dashboard');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'pin' => 'required|string|min:4|max:6|confirmed',
        ]);

        $ledger = app(\App\Services\LedgerClient::class);
        $accountId = $ledger->createAccount($validated['phone'], 'XAF', "Compte {$validated['first_name']} {$validated['last_name']}");

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'pin' => Hash::make($validated['pin']),
            'ledger_account_id' => $accountId,
            'account_type' => 'PERSONAL',
        ]);

        auth()->login($user);
        return redirect()->route('client.dashboard')->with('success', 'Compte créé avec succès !');
    }

    public function registerMerchant(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'pin' => 'required|string|min:4|max:6|confirmed',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:RESTAURANT,MARKET,BOUTIQUE,SERVICE,OTHER',
        ]);

        $ledger = app(\App\Services\LedgerClient::class);
        $accountId = $ledger->createAccount($validated['phone'], 'XAF', "Compte marchand {$validated['business_name']}");

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'pin' => Hash::make($validated['pin']),
            'ledger_account_id' => $accountId,
            'account_type' => 'MERCHANT',
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'qr_code' => strtoupper(\Str::random(8)),
        ]);

        auth()->login($user);
        return redirect()->route('merchant.dashboard')->with('success', 'Compte marchand créé !');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
