<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\LedgerClient;

class DashboardController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function index()
    {
        $user = auth()->user();

        $account = null;
        if ($user->ledger_account_id) {
            $account = $this->ledger->getAccount($user->ledger_account_id);
        }

        // Récupère les transactions (envoyées + reçues)
        $transactions = Transaction::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats rapides
        $totalSent = Transaction::where('from_user_id', $user->id)
            ->where('status', 'COMPLETED')
            ->sum('amount');

        $totalReceived = Transaction::where('to_user_id', $user->id)
            ->where('status', 'COMPLETED')
            ->sum('amount');

        return view('client.dashboard.index', compact(
            'user', 'account', 'transactions',
            'totalSent', 'totalReceived'
        ));
    }

    public function transfer()
    {
        $user = auth()->user();

        $account = null;
        if ($user->ledger_account_id) {
            $account = $this->ledger->getAccount($user->ledger_account_id);
        }

        return view('client.dashboard.transfer', compact('user', 'account'));
    }

    public function sendMoney(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'to_phone' => 'required|string',
            'amount' => 'required|numeric|min:100',
            'pin' => 'required|string',
        ]);

        $user = auth()->user();

        if (!\Hash::check($validated['pin'], $user->pin)) {
            return back()->withErrors(['error' => 'Code PIN incorrect']);
        }

        if (!$user->canTransfer($validated['amount'])) {
            return back()->withErrors(['error' => 'Montant supérieur à votre limite quotidienne']);
        }

        $recipient = \App\Models\User::where('phone', $validated['to_phone'])->first();
        if (!$recipient) {
            return back()->withErrors(['error' => 'Destinataire introuvable']);
        }

        $idempotencyKey = \Str::uuid()->toString();

        $transfer = $this->ledger->createTransfer(
            $idempotencyKey,
            $user->ledger_account_id,
            $recipient->ledger_account_id,
            $validated['amount'],
            'XAF',
            "Transfert vers {$recipient->full_name}"
        );

        if (!$transfer) {
            return back()->withErrors(['error' => 'Échec du transfert - solde insuffisant ?']);
        }

        // Enregistre la transaction
        Transaction::create([
            'ledger_transaction_id' => $transfer['id'],
            'idempotency_key' => $idempotencyKey,
            'from_user_id' => $user->id,
            'to_user_id' => $recipient->id,
            'from_account_id' => $user->ledger_account_id,
            'to_account_id' => $recipient->ledger_account_id,
            'amount' => $validated['amount'],
            'currency' => 'XAF',
            'type' => 'TRANSFER',
            'status' => 'COMPLETED',
            'description' => "Transfert vers {$recipient->full_name}",
            'completed_at' => now(),
        ]);

        // Notifie le destinataire
        $recipient->notify(new \App\Notifications\PaymentReceived(
            $validated['amount'],
            $user->full_name,
            "Transfert reçu"
        ));

        return redirect()->route('client.dashboard')
            ->with('success', "Transfert de {$validated['amount']} XAF effectué !");
    }
}