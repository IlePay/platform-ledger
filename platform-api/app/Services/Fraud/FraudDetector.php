<?php

namespace App\Services\Fraud;

use App\Models\FraudAlert;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Blacklist;

class FraudDetector
{
    // Vérifier transaction avant création
    public function checkTransaction($userId, $amount, $recipientId, $ipAddress)
    {
        $user = User::find($userId);
        $alerts = [];

        // 1. Blacklist check
        if (Blacklist::isBlacklisted('IP', $ipAddress)) {
            $alerts[] = [
                'type' => 'BLACKLIST',
                'severity' => 'CRITICAL',
                'description' => "IP blacklistée: {$ipAddress}",
            ];
        }

        if (Blacklist::isBlacklisted('PHONE', $user->phone)) {
            $alerts[] = [
                'type' => 'BLACKLIST',
                'severity' => 'CRITICAL',
                'description' => "Téléphone blacklisté: {$user->phone}",
            ];
        }

        // 2. Velocity check (trop de transactions en peu de temps)
        $recentTxCount = Transaction::where('from_user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentTxCount >= 5) {
            $alerts[] = [
                'type' => 'VELOCITY',
                'severity' => 'HIGH',
                'description' => "{$recentTxCount} transactions en 10 minutes",
                'metadata' => ['count' => $recentTxCount],
            ];
        }

        // 3. Montant anormal
        $avgAmount = Transaction::where('from_user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('amount') ?? 1000;

        if ($amount > $avgAmount * 10) {
            $alerts[] = [
                'type' => 'HIGH_AMOUNT',
                'severity' => 'MEDIUM',
                'description' => "Montant inhabituel: {$amount} XAF (avg: {$avgAmount})",
                'metadata' => ['amount' => $amount, 'avg' => $avgAmount],
            ];
        }

        // 4. Nouveau compte avec grosse transaction
        if ($user->created_at->diffInDays(now()) < 1 && $amount > 50000) {
            $alerts[] = [
                'type' => 'SUSPICIOUS_PATTERN',
                'severity' => 'HIGH',
                'description' => "Compte créé il y a moins de 24h, montant élevé",
            ];
        }

        return $alerts;
    }

    // Logger les alertes
    public function logAlerts($userId, $transactionId, $alerts)
    {
        foreach ($alerts as $alert) {
            FraudAlert::create_alert(
                $userId,
                $transactionId,
                $alert['type'],
                $alert['severity'],
                $alert['description'],
                $alert['metadata'] ?? null
            );
        }
    }

    // Auto-bloquer si CRITICAL
    public function autoBlockIfCritical($userId, $alerts)
    {
        foreach ($alerts as $alert) {
            if ($alert['severity'] === 'CRITICAL') {
                $user = User::find($userId);
                $user->update([
                    'is_blocked' => true,
                    'block_reason' => 'Auto-blocage: ' . $alert['description'],
                    'blocked_at' => now(),
                ]);
                return true;
            }
        }
        return false;
    }
}
