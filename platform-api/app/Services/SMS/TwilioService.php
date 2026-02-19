<?php

namespace App\Services\SMS;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService implements SmsServiceInterface
{
    private Client $client;
    private string $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');

        $this->client = new Client($sid, $token);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);

            Log::info('SMS sent via Twilio', ['to' => $to]);
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
