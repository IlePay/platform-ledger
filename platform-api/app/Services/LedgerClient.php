<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LedgerClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ledger.url');
        $this->timeout = config('services.ledger.timeout', 30);
    }

    public function createAccount(string $externalId, string $type = 'USER', string $currency = 'XAF'): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/accounts", [
                    'external_id' => $externalId,
                    'type' => $type,
                    'currency' => $currency,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Ledger account creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Ledger API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getAccount(string $accountId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/accounts/{$accountId}");

            return $response->successful() ? $response->json() : null;
        } catch (Exception $e) {
            Log::error('Ledger get account error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function createTransfer(
        string $idempotencyKey,
        string $fromAccountId,
        string $toAccountId,
        float $amount,
        string $currency = 'XAF'
    ): ?array {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/transfers", [
                    'idempotency_key' => $idempotencyKey,
                    'from_account_id' => $fromAccountId,
                    'to_account_id' => $toAccountId,
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Ledger transfer failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Ledger transfer error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function health(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
