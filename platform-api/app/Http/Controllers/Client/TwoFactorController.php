<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function showVerify()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        return view('client.auth.2fa-verify');
    }

    public function verify(Request $request)
    {
        $userId = session('2fa_user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $validated = $request->validate(['code' => 'required|digits:6']);
        
        $user = User::findOrFail($userId);
        
        if (!$user->verify2FACode($validated['code'])) {
            return back()->withErrors(['error' => 'Code invalide ou expiré']);
        }
        
        // Login
        auth()->login($user);
        session()->forget('2fa_user_id');
        
        // Log succès
        LoginHistory::log($user->id, $request, true);
        
        return redirect()->route('client.dashboard')->with('success', 'Connexion sécurisée réussie !');
    }

    public function resend()
    {
        $userId = session('2fa_user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $code = $user->generate2FACode();
        
        if ($user->sms_notifications) {
            app(\App\Services\SMS\SmsManager::class)->send(
                $user->phone,
                "🔐 IlePay: Votre nouveau code est {$code}. Valide 5 minutes."
            );
        }
        
        return back()->with('success', 'Code renvoyé !');
    }

    public function toggle(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate(['pin' => 'required']);
        
        if (!\Hash::check($validated['pin'], $user->pin)) {
            return back()->withErrors(['error' => 'PIN incorrect']);
        }
        
        $user->two_factor_enabled = !$user->two_factor_enabled;
        $user->save();
        
        $status = $user->two_factor_enabled ? 'activée' : 'désactivée';
        
        return back()->with('success', "Authentification 2FA {$status}");
    }

    public function history()
    {
        $history = auth()->user()->loginHistory()->paginate(20);
        
        return view('client.security.history', compact('history'));
    }
}
