<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefundReceived extends Notification
{
    use Queueable;

    public function __construct(
        private float $amount,
        private string $fromName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => '💰 Remboursement reçu !',
            'message' => "{$this->fromName} vous a remboursé " . number_format($this->amount, 0, ',', ' ') . " XAF",
            'amount' => $this->amount,
            'from' => $this->fromName,
            'type' => 'REFUND_RECEIVED',
        ];
    }
}