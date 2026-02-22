<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudAlert extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'alert_type',
        'severity',
        'description',
        'metadata',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Créer alerte
    public static function create_alert($userId, $transactionId, $type, $severity, $description, $metadata = null)
    {
        return self::create([
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'alert_type' => $type,
            'severity' => $severity,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}