<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MoneyRequest;
use App\Models\User;
use App\Models\Transaction;
use App\Services\LedgerClient;
use Illuminate\Http\Request;

class MoneyRequestController extends Controller
{
    public function __construct(private LedgerClient $ledger) {}

    // Formulaire demander argent
    public function create()
    {
        return view('client.money-request.create');
    }

    // Créer la demande
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payer_phone' => 'required|string',
            'amount' => 'required|numeric|min:100',
            'message' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $payer = User::where('phone', $validated['payer_phone'])->first();

        if (!$payer) {
            return back()->withErrors(['error' => 'Utilisateur introuvable']);
        }

        if ($payer->id === $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous demander de l\'argent à vous-même']);
        }

        $moneyRequest = MoneyRequest::create([
            'requester_id' => $user->id,
            'payer_id' => $payer->id,
            'amount' => $validated['amount'],
            'message' => $validated['message'],
            'expires_at' => now()->addDays(7), // Expire dans 7 jours
        ]);

        // Notifie le payeur
        $payer->notify(new \App\Notifications\MoneyRequested(
            $user->full_name,
            $validated['amount'],
            $validated['message']
        ));

        // SMS au payeur
        if ($payer->sms_notifications) {
            app(\App\Services\SMS\SmsManager::class)->send(
                $payer->phone,
                "💸 {$user->full_name} vous demande " . number_format($validated['amount'], 0) . " XAF. Ouvrez IlePay pour accepter/refuser."
            );
        }

        return redirect()->route('client.dashboard')->with('success', 'Demande envoyée à ' . $payer->full_name);
    }

    // Mes demandes envoyées
    public function sent()
    {
        $requests = MoneyRequest::where('requester_id', auth()->id())
            ->with('payer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.money-request.sent', compact('requests'));
    }

    // Demandes reçues
    public function received()
    {
        $requests = MoneyRequest::where('payer_id', auth()->id())
            ->with('requester')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.money-request.received', compact('requests'));
    }

    // Accepter
    public function accept($id, Request $request)
    {
        $moneyRequest = MoneyRequest::findOrFail($id);

        if ($moneyRequest->payer_id !== auth()->id()) {
            abort(403);
        }

        if (!$moneyRequest->isPending()) {
            return back()->withErrors(['error' => 'Cette demande n\'est plus en attente']);
        }

        if ($moneyRequest->isExpired()) {
            $moneyRequest->update(['status' => 'EXPIRED']);
            return back()->withErrors(['error' => 'Cette demande a expiré']);
        }

        $validated = $request->validate(['pin' => 'required']);

        if (!\Hash::check($validated['pin'], auth()->user()->pin)) {
            return back()->withErrors(['error' => 'PIN incorrect']);
        }

        $user = auth()->user();

        if (!$user->canTransfer($moneyRequest->amount)) {
            return back()->withErrors(['error' => 'Limite dépassée']);
        }

        // Créer le transfert
        $transfer = $this->ledger->createTransfer(
            \Str::uuid(),
            $user->ledger_account_id,
            $moneyRequest->requester->ledger_account_id,
            $moneyRequest->amount,
            'XAF',
            "Paiement demande: " . ($moneyRequest->message ?? '')
        );

        if (!$transfer) {
            return back()->withErrors(['error' => 'Échec du transfert']);
        }

        $transaction = Transaction::create([
            'ledger_transaction_id' => $transfer['id'],
            'idempotency_key' => $transfer['idempotency_key'],
            'from_user_id' => $user->id,
            'to_user_id' => $moneyRequest->requester_id,
            'from_account_id' => $user->ledger_account_id,
            'to_account_id' => $moneyRequest->requester->ledger_account_id,
            'amount' => $moneyRequest->amount,
            'currency' => 'XAF',
            'type' => 'TRANSFER',
            'status' => 'COMPLETED',
            'description' => "Paiement demande: " . ($moneyRequest->message ?? ''),
            'completed_at' => now(),
        ]);

        $moneyRequest->update([
            'status' => 'ACCEPTED',
            'responded_at' => now(),
            'transaction_id' => $transaction->id,
        ]);

        // Notifie le requester
        $moneyRequest->requester->notify(new \App\Notifications\MoneyRequestAccepted(
            $user->full_name,
            $moneyRequest->amount
        ));

        return redirect()->route('money-request.received')->with('success', 'Paiement envoyé !');
    }

    // Refuser
    public function decline($id)
    {
        $moneyRequest = MoneyRequest::findOrFail($id);

        if ($moneyRequest->payer_id !== auth()->id()) {
            abort(403);
        }

        if (!$moneyRequest->isPending()) {
            return back()->withErrors(['error' => 'Cette demande n\'est plus en attente']);
        }

        $moneyRequest->update([
            'status' => 'DECLINED',
            'responded_at' => now(),
        ]);

        // Notifie le requester
        $moneyRequest->requester->notify(new \App\Notifications\MoneyRequestDeclined(
            auth()->user()->full_name,
            $moneyRequest->amount
        ));

        return back()->with('success', 'Demande refusée');
    }
}