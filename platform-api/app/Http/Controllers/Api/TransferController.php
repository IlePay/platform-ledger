<?php

namespace App\Http/Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'to_phone' => 'required|string|exists:users,phone',
            'amount' => 'required|numeric|min:100',
            'pin' => 'required|string',
        ]);

        $user = $request->user();

        // Vérifier le PIN
        if (!\Hash::check($validated['pin'], $user->pin)) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 403);
        }

        // Vérifier la limite
        if (!$user->canTransfer($validated['amount'])) {
            return response()->json([
                'message' => 'Amount exceeds daily limit or account inactive'
            ], 403);
        }

        // Récupérer le destinataire
        $recipient = User::where('phone', $validated['to_phone'])->first();

        if (!$recipient->ledger_account_id) {
            return response()->json([
                'message' => 'Recipient has no wallet'
            ], 400);
        }

        // Créer le transfert
        $idempotencyKey = Str::uuid()->toString();

        $transfer = $this->ledger->createTransfer(
            $idempotencyKey,
            $user->ledger_account_id,
            $recipient->ledger_account_id,
            $validated['amount'],
            'XAF'
        );

        if (!$transfer) {
            return response()->json([
                'message' => 'Transfer failed'
            ], 500);
        }

        return response()->json([
            'message' => 'Transfer successful',
            'transfer' => $transfer,
            'recipient' => [
                'name' => $recipient->full_name,
                'phone' => $recipient->phone,
            ],
        ], 201);
    }
}
