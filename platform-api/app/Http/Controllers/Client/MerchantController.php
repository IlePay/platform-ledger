<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Services\LedgerClient;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function refund(Request $request, $transactionId)
{
    $transaction = \App\Models\Transaction::findOrFail($transactionId);
    
    // Vérifie que c'est le marchand qui a reçu le paiement
    if ($transaction->to_user_id !== auth()->id()) {
        abort(403, 'Non autorisé');
    }
    
    // Vérifie pas déjà remboursé
    if ($transaction->isRefunded()) {
        return back()->withErrors(['error' => 'Transaction déjà remboursée']);
    }
    
    $validated = $request->validate([
        'amount' => 'required|numeric|min:1|max:' . $transaction->amount,
        'reason' => 'nullable|string|max:255',
    ]);
    
    // Crée le transfert de remboursement
    $refund = $this->ledger->createTransfer(
        \Str::uuid()->toString(),
        auth()->user()->ledger_account_id, // Marchand
        $transaction->fromUser->ledger_account_id, // Client original
        $validated['amount'],
        'XAF',
        $validated['reason'] ?? "Remboursement transaction #{$transaction->id}"
    );
    
    if (!$refund) {
        return back()->withErrors(['error' => 'Échec du remboursement - solde insuffisant ?']);
    }
    
    // Enregistre le remboursement
    \App\Models\Transaction::create([
        'ledger_transaction_id' => $refund['id'],
        'idempotency_key' => $refund['idempotency_key'],
        'from_user_id' => auth()->id(),
        'to_user_id' => $transaction->from_user_id,
        'from_account_id' => auth()->user()->ledger_account_id,
        'to_account_id' => $transaction->fromUser->ledger_account_id,
        'amount' => $validated['amount'],
        'currency' => 'XAF',
        'type' => 'REFUND',
        'status' => 'COMPLETED',
        'description' => $validated['reason'] ?? "Remboursement",
        'parent_transaction_id' => $transaction->id,
        'completed_at' => now(),
    ]);
    
    // Marque l'originale comme remboursée
    $transaction->update(['refunded_at' => now()]);
    
    // Notifie le client
    $transaction->fromUser->notify(new \App\Notifications\RefundReceived(
        $validated['amount'],
        auth()->user()->business_name ?? auth()->user()->full_name
    ));

    if ($transaction->fromUser->sms_notifications) {
    app(\App\Services\SMS\SmsManager::class)->sendRefundReceived(
        $transaction->fromUser->phone,
        $validated['amount'],
        auth()->user()->business_name ?? auth()->user()->full_name
    );
    }
    
    return back()->with('success', "Remboursement de {$validated['amount']} XAF effectué !");
}

    // Page publique de paiement (scan QR)
    public function paymentPage($qrCode)
    {
        $merchant = User::where('qr_code', $qrCode)
            ->where('account_type', 'MERCHANT')
            ->where('is_active', true)
            ->firstOrFail();

        return view('client.merchant.pay', [
            'merchant' => $merchant,
        ]);
    }

   public function downloadQrCode()
{
    $user = auth()->user();
    
    if ($user->account_type !== 'MERCHANT') {
        abort(403);
    }
    
    $url = url('/pay/' . $user->qr_code);
    
    // Utilise l'API Google Charts pour générer le QR Code
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
        'size' => '400x400',
        'data' => $url,
        'color' => '2D4B9E',
        'bgcolor' => 'FFFFFF',
        'margin' => 20,
        'format' => 'png'
    ]);
    
    // Télécharge l'image
    $imageContent = file_get_contents($qrCodeUrl);
    
    return response($imageContent)
        ->header('Content-Type', 'image/png')
        ->header('Content-Disposition', 'attachment; filename="qrcode-' . $user->qr_code . '.png"');
}

    // Traitement du paiement
    public function processPay(Request $request, $qrCode)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'pin' => 'required|string',
        ]);

        $customer = auth()->user();
        $merchant = User::where('qr_code', $qrCode)->firstOrFail();

        // Vérifier le PIN
        if (!\Hash::check($validated['pin'], $customer->pin)) {
            return back()->withErrors(['error' => 'Code PIN incorrect']);
        }

        // Vérifier la limite
        if (!$customer->canTransfer($validated['amount'])) {
            return back()->withErrors(['error' => 'Montant supérieur à votre limite']);
        }

        // Créer le transfert
        $transfer = $this->ledger->createTransfer(
            \Str::uuid()->toString(),
            $customer->ledger_account_id,
            $merchant->ledger_account_id,
            $validated['amount'],
            'XAF',
            "Paiement chez {$merchant->business_name}"
        );

        if (!$transfer) {
            return back()->withErrors(['error' => 'Échec du paiement']);
        }

        // Enregistrer la transaction
        Transaction::create([
            'ledger_transaction_id' => $transfer['id'],
            'idempotency_key' => $transfer['idempotency_key'],
            'from_user_id' => $customer->id,
            'to_user_id' => $merchant->id,
            'from_account_id' => $customer->ledger_account_id,
            'to_account_id' => $merchant->ledger_account_id,
            'amount' => $validated['amount'],
            'currency' => 'XAF',
            'type' => 'TRANSFER',
            'status' => 'COMPLETED',
            'description' => "Paiement chez {$merchant->business_name}",
            'completed_at' => now(),
        ]);

        // Mettre à jour les stats du marchand
        $merchant->increment('sales_count');
        $merchant->increment('total_sales', $validated['amount']);
        // Envoie notification au marchand
        $merchant->notify(new \App\Notifications\PaymentReceived(
            $validated['amount'],
            $customer->full_name,
            "Paiement chez {$merchant->business_name}"
        ));

        // SMS au marchand
        if ($merchant->sms_notifications) {
            app(\App\Services\SMS\SmsManager::class)->sendPaymentReceived(
                $merchant->phone,
                $validated['amount'],
                $customer->full_name
            );
        }

        return redirect()->route('client.dashboard')
            ->with('success', "Paiement de {$validated['amount']} XAF effectué chez {$merchant->business_name} !");
    }

    // Dashboard marchand
    public function dashboard()
{
    $user = auth()->user();

    if ($user->account_type !== 'MERCHANT') {
        return redirect()->route('client.dashboard');
    }

    $account = null;
    if ($user->ledger_account_id) {
        $account = $this->ledger->getAccount($user->ledger_account_id);
    }

    $sales = \App\Models\Transaction::where('to_user_id', $user->id)
        ->where('status', 'COMPLETED')
        ->with('fromUser')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    $todaySales = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereDate('created_at', today())
        ->sum('amount');

    $todayCount = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereDate('created_at', today())
        ->count();

    $weeklySales = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereBetween('created_at', [now()->startOfWeek(), now()])
        ->sum('amount');

    $weeklyCount = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereBetween('created_at', [now()->startOfWeek(), now()])
        ->count();

    $monthlySales = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->sum('amount');

    $monthlyCount = \App\Models\Transaction::where('to_user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->count();

    return view('client.merchant.dashboard', compact(
        'user', 'account', 'sales',
        'todaySales', 'todayCount',
        'weeklySales', 'weeklyCount',
        'monthlySales', 'monthlyCount'
    ));
}

public function export(Request $request)
{
    $user = auth()->user();
    
    if ($user->account_type !== 'MERCHANT') {
        abort(403);
    }
    
    $format = $request->get('format', 'pdf'); // pdf ou csv
    $period = $request->get('period', 'all'); // today, week, month, all
    
    // Filtrage par période
    $query = \App\Models\Transaction::where('to_user_id', $user->id)
        ->where('status', 'COMPLETED')
        ->with('fromUser')
        ->orderBy('created_at', 'desc');
    
    switch ($period) {
        case 'today':
            $query->whereDate('created_at', today());
            $periodLabel = "Aujourd'hui";
            break;
        case 'week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()]);
            $periodLabel = "Cette semaine";
            break;
        case 'month':
            $query->whereMonth('created_at', now()->month);
            $periodLabel = "Ce mois";
            break;
        default:
            $periodLabel = "Toutes les transactions";
    }
    
    $transactions = $query->get();
    $total = $transactions->sum('amount');
    
    if ($format === 'csv') {
        return $this->exportCSV($transactions, $user, $periodLabel);
    }
    
    return $this->exportPDF($transactions, $user, $periodLabel, $total);
}

private function exportPDF($transactions, $user, $periodLabel, $total)
{
    $pdf = \PDF::loadView('client.merchant.export-pdf', [
        'transactions' => $transactions,
        'merchant' => $user,
        'period' => $periodLabel,
        'total' => $total,
        'date' => now()->format('d/m/Y'),
    ]);
    
    $filename = 'transactions-' . $user->qr_code . '-' . now()->format('Y-m-d') . '.pdf';
    
    return $pdf->download($filename);
}

private function exportCSV($transactions, $user, $periodLabel)
{
    $filename = 'transactions-' . $user->qr_code . '-' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $callback = function() use ($transactions) {
        $file = fopen('php://output', 'w');
        
        // En-têtes CSV
        fputcsv($file, ['Date', 'Client', 'Montant (XAF)', 'Description', 'Statut']);
        
        // Données
        foreach ($transactions as $tx) {
            fputcsv($file, [
                $tx->created_at->format('d/m/Y H:i'),
                $tx->fromUser ? $tx->fromUser->full_name : 'Inconnu',
                number_format($tx->amount, 0, ',', ' '),
                $tx->description ?? '',
                $tx->status,
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}
}