<?php

namespace App\Services\SMS;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class AfricasTalkingService implements SmsServiceInterface
{
    private $sms;
    private string $from;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        $this->from = config('services.africastalking.from');

        $at = new AfricasTalking($username, $apiKey);
        $this->sms = $at->sms();
    }

    public function send(string $to, string $message): bool
    {
        try {
            $result = $this->sms->send([
                'to' => $to,
                'message' => $message,
                'from' => $this->from,
            ]);

            Log::info('SMS sent via AfricasTalking', ['to' => $to, 'result' => $result]);
            return true;
        } catch (\Exception $e) {
            Log::error('AfricasTalking SMS failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
