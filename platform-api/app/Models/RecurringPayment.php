<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringPayment extends Model
{
    protected $fillable = [
        'payer_id',
        'merchant_id',
        'amount',
        'currency',
        'frequency',
        'next_payment_date',
        'auto_pay',
        'is_active',
        'description',
        'metadata',
        'last_payment_at',
    ];

    protected $casts = [
        'next_payment_date' => 'date',
        'last_payment_at' => 'datetime',
        'auto_pay' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    // Calculer prochaine date
    public function calculateNextDate(): string
    {
        return match($this->frequency) {
            'MONTHLY' => now()->addMonth()->format('Y-m-d'),
            'QUARTERLY' => now()->addMonths(3)->format('Y-m-d'),
            'YEARLY' => now()->addYear()->format('Y-m-d'),
            default => now()->addMonth()->format('Y-m-d'),
        };
    }
}
