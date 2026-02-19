<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FavoriteContact;
use App\Models\User;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $favorites = $user->favoriteContacts()->get();
        
        // Contacts suggérés (utilisateurs avec qui il a le plus de transactions)
        $suggested = User::whereHas('transactionsReceived', function($q) use ($user) {
            $q->where('from_user_id', $user->id);
        })
        ->whereNotIn('id', $favorites->pluck('id'))
        ->where('id', '!=', $user->id)
        ->withCount(['transactionsReceived' => function($q) use ($user) {
            $q->where('from_user_id', $user->id);
        }])
        ->orderBy('transactions_received_count', 'desc')
        ->limit(5)
        ->get();
        
        return view('client.contacts.index', compact('favorites', 'suggested'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'contact_phone' => 'required|string',
            'nickname' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $contact = User::where('phone', $validated['contact_phone'])->first();

        if (!$contact) {
            return back()->withErrors(['error' => 'Utilisateur introuvable']);
        }

        if ($contact->id === $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous ajouter vous-même']);
        }

        if ($user->isFavorite($contact->id)) {
            return back()->withErrors(['error' => 'Ce contact est déjà dans vos favoris']);
        }

        FavoriteContact::create([
            'user_id' => $user->id,
            'contact_id' => $contact->id,
            'nickname' => $validated['nickname'] ?? null, // Fix ici
        ]);

        return back()->with('success', $contact->full_name . ' ajouté aux favoris !');
    }

    public function remove($id)
    {
        $user = auth()->user();
        
        FavoriteContact::where('user_id', $user->id)
            ->where('contact_id', $id)
            ->delete();

        return back()->with('success', 'Contact retiré des favoris');
    }

    public function quickSend($id)
    {
        $contact = User::findOrFail($id);
        
        if (!auth()->user()->isFavorite($contact->id)) {
            abort(403);
        }

        return view('client.contacts.quick-send', compact('contact'));
    }
}
