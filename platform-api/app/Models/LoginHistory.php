<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    public $timestamps = false;
    
    protected $table = 'login_history';
    
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'location',
        'was_successful',
        'failure_reason',
        'created_at',
    ];

    protected $casts = [
        'was_successful' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log($userId, $request, $successful = true, $reason = null)
    {
        return self::create([
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => self::detectDevice($request->userAgent()),
            'was_successful' => $successful,
            'failure_reason' => $reason,
            'created_at' => now(),
        ]);
    }

    public static function detectDevice($userAgent)
    {
        if (stripos($userAgent, 'mobile') !== false) return 'Mobile';
        if (stripos($userAgent, 'tablet') !== false) return 'Tablet';
        return 'Desktop';
    }
}