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
        return ['database', 'mail']; // Ajout de 'mail'
    }

    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $body = \App\Models\EmailTemplate::renderTemplate('payment_received', [
            'merchant_name' => $notifiable->business_name ?? $notifiable->full_name,
            'customer_name' => $this->fromName,
            'amount' => number_format($this->amount, 0, ',', ' '),
        ]) ?? "Vous avez reçu {$this->amount} XAF de {$this->fromName}.";

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Paiement de {$this->amount} XAF reçu")
            ->greeting("Bonjour,")
            ->line($body)
            ->action('Voir mon dashboard', url('/merchant/dashboard'));
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