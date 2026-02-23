<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'rate_to_xaf', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Convertir montant de cette devise vers XAF
    public function toXAF($amount): float
    {
        return $amount * $this->rate_to_xaf;
    }

    // Convertir XAF vers cette devise
    public function fromXAF($amountXAF): float
    {
        return $amountXAF / $this->rate_to_xaf;
    }
}