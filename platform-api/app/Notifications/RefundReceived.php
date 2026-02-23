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
    public function toMail($notifiable): MailMessage
    {
        $body = \App\Models\EmailTemplate::renderTemplate('refund_received', [
            'customer_name' => $notifiable->full_name,
            'merchant_name' => $this->merchantName,
            'amount' => number_format($this->amount, 0, ',', ' '),
        ]);
        
        $subject = \App\Models\EmailTemplate::getSubject('refund_received', [
            'amount' => number_format($this->amount, 0, ',', ' '),
        ]);
        
        if (!$body) {
            $body = "Vous avez été remboursé {$this->amount} XAF par {$this->merchantName}.";
        }
        
        if (!$subject) {
            $subject = "Remboursement de {$this->amount} XAF";
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$notifiable->full_name},")
            ->line($body)
            ->action('Voir mon historique', url('/dashboard'))
            ->line('Merci d\'utiliser IlePay !');
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