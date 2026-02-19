<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\LedgerClient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private LedgerClient $ledger) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $account = $user->ledger_account_id ? $this->ledger->getAccount($user->ledger_account_id) : null;

        $search = $request->get('search');
        $type = $request->get('type');
        $period = $request->get('period', 'all');

        $query = Transaction::where(function($q) use ($user) {
            $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id);
        })->with(['fromUser', 'toUser']);

        if ($search) {
            $query->where(function($q) use ($search) {
                if (is_numeric($search)) $q->where('amount', $search);
                $q->orWhereHas('fromUser', fn($q) => $q->where('first_name', 'ILIKE', "%{$search}%")->orWhere('last_name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('toUser', fn($q) => $q->where('first_name', 'ILIKE', "%{$search}%")->orWhere('last_name', 'ILIKE', "%{$search}%"))
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if ($type) $query->where('type', $type);

        match($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()]),
            'month' => $query->whereMonth('created_at', now()->month),
            default => null
        };

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);
        $totalSent = Transaction::where('from_user_id', $user->id)->where('status', 'COMPLETED')->sum('amount');
        $totalReceived = Transaction::where('to_user_id', $user->id)->where('status', 'COMPLETED')->sum('amount');

        return view('client.dashboard.index', compact('user', 'account', 'transactions', 'totalSent', 'totalReceived', 'search', 'type', 'period'));
    }

    public function exportTransactions(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'all');
        
        $query = Transaction::where(fn($q) => $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))->with(['fromUser', 'toUser']);
        
        match($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()]),
            'month' => $query->whereMonth('created_at', now()->month),
            default => null
        };
        
        $transactions = $query->orderBy('created_at', 'desc')->get();
        $filename = 'transactions-' . now()->format('Y-m-d') . '.csv';
        
        return response()->stream(function() use ($transactions, $user) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Date', 'Type', 'De/À', 'Montant', 'Statut']);
            
            foreach ($transactions as $tx) {
                $isSent = $tx->from_user_id === $user->id;
                $other = $isSent ? ($tx->toUser?->full_name ?? 'Inconnu') : ($tx->fromUser?->full_name ?? 'Inconnu');
                fputcsv($file, [$tx->created_at->format('d/m/Y H:i'), $isSent ? 'Envoyé' : 'Reçu', $other, ($isSent ? '-' : '+') . $tx->amount, $tx->status]);
            }
            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""]);
    }

    public function transfer()
    {
        $user = auth()->user();
        $account = $user->ledger_account_id ? $this->ledger->getAccount($user->ledger_account_id) : null;
        
        return view('client.dashboard.transfer', compact('user', 'account'));
    }

    public function sendMoney(Request $request)
    {
        $validated = $request->validate(['recipient_phone' => 'required', 'amount' => 'required|numeric|min:100', 'pin' => 'required']);
        $user = auth()->user();

        if (!\Hash::check($validated['pin'], $user->pin)) return back()->withErrors(['error' => 'PIN incorrect']);
        
        $recipient = \App\Models\User::where('phone', $validated['recipient_phone'])->first();
        if (!$recipient) return back()->withErrors(['error' => 'Destinataire introuvable']);
        if (!$user->canTransfer($validated['amount'])) return back()->withErrors(['error' => 'Limite dépassée']);

        $transfer = $this->ledger->createTransfer(\Str::uuid(), $user->ledger_account_id, $recipient->ledger_account_id, $validated['amount'], 'XAF', "Transfert");
        if (!$transfer) return back()->withErrors(['error' => 'Échec']);

        Transaction::create(['ledger_transaction_id' => $transfer['id'], 'idempotency_key' => $transfer['idempotency_key'], 'from_user_id' => $user->id, 'to_user_id' => $recipient->id, 'from_account_id' => $user->ledger_account_id, 'to_account_id' => $recipient->ledger_account_id, 'amount' => $validated['amount'], 'currency' => 'XAF', 'type' => 'TRANSFER', 'status' => 'COMPLETED', 'description' => "Transfert", 'completed_at' => now()]);

        $recipient->notify(new \App\Notifications\PaymentReceived($validated['amount'], $user->full_name, "Transfert"));
        if ($recipient->sms_notifications) app(\App\Services\SMS\SmsManager::class)->sendPaymentReceived($recipient->phone, $validated['amount'], $user->full_name);
        if ($user->sms_notifications) app(\App\Services\SMS\SmsManager::class)->sendPaymentSent($user->phone, $validated['amount'], $recipient->full_name);

        return redirect()->route('client.dashboard')->with('success', "Transfert de {$validated['amount']} XAF envoyé !");
    }
}