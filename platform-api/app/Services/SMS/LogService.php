<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Log;

class LogService implements SmsServiceInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info('SMS (LOG MODE)', [
            'to' => $to,
            'message' => $message,
        ]);

        return true;
    }
}
