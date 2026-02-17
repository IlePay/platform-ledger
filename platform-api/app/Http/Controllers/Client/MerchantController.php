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
}