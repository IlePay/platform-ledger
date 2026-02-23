<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueReport extends Model
{
    protected $fillable = [
        'report_date',
        'period_type',
        'total_gmv',
        'total_commission',
        'transaction_count',
        'new_users_count',
        'active_merchants',
        'metadata',
    ];

    protected $casts = [
        'report_date' => 'date',
        'metadata' => 'array',
    ];

    // Générer rapport quotidien
    public static function generateDaily($date)
    {
        $gmv = Transaction::whereDate('created_at', $date)
            ->where('status', 'COMPLETED')
            ->sum('amount');

        $commission = Transaction::whereDate('created_at', $date)
            ->where('status', 'COMPLETED')
            ->where('type', 'PAYMENT') // Commission sur paiements marchands
            ->sum('amount') * 0.015; // 1.5%

        $txCount = Transaction::whereDate('created_at', $date)
            ->where('status', 'COMPLETED')
            ->count();

        $newUsers = User::whereDate('created_at', $date)->count();

        $activeMerchants = Transaction::whereDate('created_at', $date)
            ->where('type', 'PAYMENT')
            ->distinct('to_user_id')
            ->count();

        return self::updateOrCreate(
            ['report_date' => $date, 'period_type' => 'DAILY'],
            [
                'total_gmv' => $gmv,
                'total_commission' => $commission,
                'transaction_count' => $txCount,
                'new_users_count' => $newUsers,
                'active_merchants' => $activeMerchants,
            ]
        );
    }
}