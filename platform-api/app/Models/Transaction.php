<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_transaction_id',
        'idempotency_key',
        'from_user_id',
        'to_user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'currency',
        'type',
        'status',
        'description',
        'metadata',
        'completed_at',
        'refunded_at',
        'parent_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
    public function parentTransaction()
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    public function refundTransaction()
    {
        return $this->hasOne(Transaction::class, 'parent_transaction_id');
    }

    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }
}