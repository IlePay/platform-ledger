<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'first_name',
        'last_name',
        'pin',
        'ledger_account_id',
        'kyc_level',
        'kyc_verified_at',
        'kyc_data',
        'daily_limit',
        'monthly_limit',
        'is_active',
        'is_verified',
        'phone_verified_at',
        'email_verified_at',
        'role',
        'account_type',         
        'business_name',     
        'business_type',  
        'qr_code',
        'total_sales',
        'sales_count',  
        'avatar',
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'kyc_data' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['ADMIN', 'SUPER_ADMIN']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['phone', 'kyc_level', 'is_active'])
            ->logOnlyDirty();
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function isMerchant(): bool
    {
        return $this->account_type === 'MERCHANT';
    }

    public function canTransfer(float $amount): bool
    {
        return $this->is_active && $amount <= $this->daily_limit;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name ?: $this->phone;
    }

    public function favoriteContacts()
    {
        return $this->belongsToMany(User::class, 'favorite_contacts', 'user_id', 'contact_id')
            ->withPivot('nickname')
            ->withTimestamps();
    }

    public function isFavorite($contactId): bool
    {
        return $this->favoriteContacts()->where('contact_id', $contactId)->exists();
    }
    public function transactionsReceived()
    {
        return $this->hasMany(Transaction::class, 'to_user_id');
    }

    public function transactionsSent()
    {
        return $this->hasMany(Transaction::class, 'from_user_id');
    }

    // Calcul limites restantes
    public function getRemainingDailyLimit(): float
    {
        $todaySpent = Transaction::where('from_user_id', $this->id)
            ->whereDate('created_at', today())
            ->where('status', 'COMPLETED')
            ->sum('amount');
        
        return max(0, $this->daily_limit - $todaySpent);
    }

    public function getRemainingMonthlyLimit(): float
    {
        $monthSpent = Transaction::where('from_user_id', $this->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'COMPLETED')
            ->sum('amount');
        
        return max(0, $this->monthly_limit - $monthSpent);
    }

    public function getDailyLimitPercentage(): float
    {
        if ($this->daily_limit == 0) return 0;
        $remaining = $this->getRemainingDailyLimit();
        return (($this->daily_limit - $remaining) / $this->daily_limit) * 100;
    }
}