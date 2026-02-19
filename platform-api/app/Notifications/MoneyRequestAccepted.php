<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MoneyRequestAccepted extends Notification
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
            'title' => '✅ Demande acceptée !',
            'message' => "{$this->fromName} a accepté votre demande de " . number_format($this->amount, 0, ',', ' ') . " XAF",
            'amount' => $this->amount,
            'from' => $this->fromName,
            'type' => 'MONEY_REQUEST_ACCEPTED',
        ];
    }
}