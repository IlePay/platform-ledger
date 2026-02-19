<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuspiciousLogin extends Notification
{
    use Queueable;

    public function __construct(
        private string $ip,
        private \Carbon\Carbon $time
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '🔐 Connexion inhabituelle détectée',
            'message' => "Connexion depuis {$this->ip} à {$this->time->format('H:i')}. Si ce n'était pas vous, sécurisez votre compte.",
            'ip' => $this->ip,
            'type' => 'SUSPICIOUS_LOGIN',
        ];
    }
}