<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return view('client.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);
        
        // Retirer l'avatar de validated pour le gérer séparément
        unset($validated['avatar']);

        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Upload nouveau et l'ajouter manuellement
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;  // Maintenant c'est un string!
            
            \Log::info('Avatar path to save', ['path' => $path, 'type' => gettype($path)]);
        }

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès');
    }

    public function updatePhone(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'pin' => 'required|string',
        ]);

        if (!Hash::check($validated['pin'], $user->pin)) {
            return back()->withErrors(['error' => 'Code PIN incorrect']);
        }

        $user->update(['phone' => $validated['phone']]);

        return back()->with('success', 'Numéro de téléphone mis à jour');
    }

    public function updatePin(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_pin' => 'required|string',
            'new_pin' => 'required|string|min:4|max:6|confirmed',
        ]);

        if (!Hash::check($validated['current_pin'], $user->pin)) {
            return back()->withErrors(['error' => 'PIN actuel incorrect']);
        }

        $user->update([
            'pin' => Hash::make($validated['new_pin']),
        ]);

        return back()->with('success', 'Code PIN mis à jour avec succès');
    }

    public function updateNotifications(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'sms_notifications' => 'boolean',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'Préférences de notifications mises à jour');
    }

    public function updateLimits(Request $request)
    {
        // Demande KYC upgrade si limites insuffisantes
        $user = auth()->user();
        
        return view('client.profile.kyc-upgrade', compact('user'));
    }
}