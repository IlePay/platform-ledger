<?php

namespace App\Services\SMS;

interface SmsServiceInterface
{
    public function send(string $to, string $message): bool;
}
