<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketReply extends Notification
{
    use Queueable;

    public function __construct(
        private string $ticketNumber,
        private string $message
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '💬 Nouvelle réponse - Ticket ' . $this->ticketNumber,
            'message' => 'Support IlePay a répondu à votre ticket',
            'type' => 'TICKET_REPLY',
        ];
    }
}