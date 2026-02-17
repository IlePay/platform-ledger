<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LedgerClient;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function balance(Request $request)
    {
        $user = $request->user();

        if (!$user->ledger_account_id) {
            return response()->json([
                'message' => 'No ledger account found'
            ], 404);
        }

        $account = $this->ledger->getAccount($user->ledger_account_id);

        if (!$account) {
            return response()->json([
                'message' => 'Failed to fetch balance'
            ], 500);
        }

        return response()->json([
            'balance' => $account['balance'],
            'available_balance' => $account['available_balance'],
            'currency' => $account['currency'],
            'account_id' => $account['id'],
        ]);
    }
}
