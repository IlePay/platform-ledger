<?php

namespace App\Console\Commands;

use App\Models\RecurringPayment;
use App\Models\Transaction;
use App\Services\LedgerClient;
use Illuminate\Console\Command;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Traiter les paiements récurrents automatiques';

    public function handle(LedgerClient $ledger)
    {
        $payments = RecurringPayment::where('is_active', true)
            ->where('auto_pay', true)
            ->where('next_payment_date', '<=', today())
            ->with(['payer', 'merchant'])
            ->get();

        $this->info("Traitement de {$payments->count()} paiements...");

        foreach ($payments as $payment) {
            $this->processPayment($payment, $ledger);
        }

        $this->info('Terminé !');
    }

    private function processPayment(RecurringPayment $payment, LedgerClient $ledger)
    {
        $payer = $payment->payer;
        $merchant = $payment->merchant;

        // Vérifier le solde
        $account = $ledger->getAccount($payer->ledger_account_id);
        if (!$account || $account['balance'] < $payment->amount) {
            $this->error("Solde insuffisant pour {$payer->full_name}");
            return;
        }

        // Créer le transfert
        $transfer = $ledger->createTransfer(
            \Str::uuid()->toString(),
            $payer->ledger_account_id,
            $merchant->ledger_account_id,
            $payment->amount,
            $payment->currency,
            $payment->description ?? "Paiement récurrent"
        );

        if (!$transfer) {
            $this->error("Échec transfert pour {$payer->full_name}");
            return;
        }

        // Enregistrer transaction
        Transaction::create([
            'ledger_transaction_id' => $transfer['id'],
            'idempotency_key' => $transfer['idempotency_key'],
            'from_user_id' => $payer->id,
            'to_user_id' => $merchant->id,
            'from_account_id' => $payer->ledger_account_id,
            'to_account_id' => $merchant->ledger_account_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'type' => 'PAYMENT',
            'status' => 'COMPLETED',
            'description' => $payment->description ?? "Paiement récurrent",
            'metadata' => $payment->metadata,
            'completed_at' => now(),
        ]);

        // Mettre à jour paiement récurrent
        $payment->update([
            'last_payment_at' => now(),
            'next_payment_date' => $payment->calculateNextDate(),
        ]);

        $this->info("✓ Paiement traité pour {$payer->full_name}");
    }
}