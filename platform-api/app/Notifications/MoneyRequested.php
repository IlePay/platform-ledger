<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MoneyRequested extends Notification
{
    use Queueable;

    public function __construct(
        private string $fromName,
        private float $amount,
        private ?string $message
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '💸 Demande d\'argent',
            'message' => "{$this->fromName} vous demande " . number_format($this->amount, 0, ',', ' ') . " XAF" . ($this->message ? " - {$this->message}" : ''),
            'amount' => $this->amount,
            'from' => $this->fromName,
            'type' => 'MONEY_REQUESTED',
        ];
    }
}