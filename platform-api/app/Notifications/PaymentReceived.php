<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        private float $amount,
        private string $fromName,
        private string $description = ''
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => '💰 Paiement reçu !',
            'message' => "{$this->fromName} vous a envoyé " . number_format($this->amount, 0, ',', ' ') . " XAF",
            'amount' => $this->amount,
            'from' => $this->fromName,
            'description' => $this->description,
            'type' => 'PAYMENT_RECEIVED',
        ];
    }
}