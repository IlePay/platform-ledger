<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MoneyRequestDeclined extends Notification
{
    use Queueable;

    public function __construct(private string $fromName, private float $amount) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '❌ Demande refusée',
            'message' => "{$this->fromName} a refusé votre demande de " . number_format($this->amount, 0, ',', ' ') . " XAF",
            'amount' => $this->amount,
            'from' => $this->fromName,
            'type' => 'MONEY_REQUEST_DECLINED',
        ];
    }
}