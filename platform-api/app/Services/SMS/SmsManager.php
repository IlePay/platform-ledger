<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Log;

class SmsManager
{
    private SmsServiceInterface $provider;

    public function __construct()
    {
        $driver = config('services.sms.driver', 'log'); // twilio, africastalking, log

        $this->provider = match($driver) {
            'twilio' => new TwilioService(),
            'africastalking' => new AfricasTalkingService(),
            default => new LogService(), // Pour tests
        };
    }

    public function send(string $to, string $message): bool
    {
        // Vérifie que le numéro est valide
        if (!$this->isValidPhone($to)) {
            Log::warning('Invalid phone number', ['to' => $to]);
            return false;
        }

        return $this->provider->send($to, $message);
    }

    public function sendPaymentReceived(string $to, float $amount, string $from): bool
    {
        $message = "💰 IlePay: Vous avez reçu " . number_format($amount, 0, ',', ' ') . " XAF de $from";
        return $this->send($to, $message);
    }

    public function sendPaymentSent(string $to, float $amount, string $toName): bool
    {
        $message = "📤 IlePay: Paiement de " . number_format($amount, 0, ',', ' ') . " XAF envoyé à $toName";
        return $this->send($to, $message);
    }

    public function sendRefundReceived(string $to, float $amount, string $from): bool
    {
        $message = "💸 IlePay: Remboursement de " . number_format($amount, 0, ',', ' ') . " XAF reçu de $from";
        return $this->send($to, $message);
    }

    private function isValidPhone(string $phone): bool
    {
        // Format international requis : +237XXXXXXXXX
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
    }
}
