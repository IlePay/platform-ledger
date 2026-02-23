<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::insert([
            [
                'key' => 'payment_received',
                'name' => 'Paiement reçu',
                'subject' => 'Nouveau paiement de {{ amount }} XAF',
                'body' => "Bonjour {{ merchant_name }},\n\nVous avez reçu un paiement de {{ amount }} XAF de la part de {{ customer_name }}.\n\nMerci d'utiliser IlePay !",
                'variables' => json_encode(['merchant_name', 'customer_name', 'amount']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'refund_received',
                'name' => 'Remboursement reçu',
                'subject' => 'Remboursement de {{ amount }} XAF',
                'body' => "Bonjour {{ customer_name }},\n\nVous avez été remboursé {{ amount }} XAF par {{ merchant_name }}.\n\nMerci d'utiliser IlePay !",
                'variables' => json_encode(['customer_name', 'merchant_name', 'amount']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'key' => 'transfer_received',
                'name' => 'Transfert P2P reçu',
                'subject' => 'Vous avez reçu {{ amount }} XAF',
                'body' => "Bonjour {{ recipient_name }},\n\n{{ sender_name }} vous a envoyé {{ amount }} XAF.\n\nMerci d'utiliser IlePay !",
                'variables' => json_encode(['recipient_name', 'sender_name', 'amount']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'money_request_accepted',
                'name' => 'Demande d\'argent acceptée',
                'subject' => '{{ payer_name }} a accepté votre demande',
                'body' => "Bonjour {{ requester_name }},\n\n{{ payer_name }} a accepté votre demande de {{ amount }} XAF.\n\nLe montant a été transféré sur votre compte.",
                'variables' => json_encode(['requester_name', 'payer_name', 'amount']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ticket_reply',
                'name' => 'Réponse au ticket support',
                'subject' => 'Nouvelle réponse sur votre ticket {{ ticket_number }}',
                'body' => "Bonjour {{ user_name }},\n\nNous avons répondu à votre ticket {{ ticket_number }}.\n\nConsultez votre ticket pour voir la réponse.",
                'variables' => json_encode(['user_name', 'ticket_number']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        
    }
}