<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionQrService;
use Illuminate\Http\Request;

class FarmerQrController extends Controller
{
    public function verify(Request $request, TransactionQrService $qrService)
    {
        $validated = $request->validate([
            'qr_content' => ['required', 'string', 'max:10000'],
        ]);

        $qrData = json_decode($validated['qr_content'], true);

        if (! is_array($qrData) || ! is_string($qrData['signature'] ?? null)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid QR code format.',
            ], 422);
        }

        $signature = $qrData['signature'];
        unset($qrData['signature']);

        $requiredKeys = [
            'version',
            'transaction_uuid',
            'beneficiary_id',
            'total_amount',
            'issued_at',
            'expires_at',
            'reference',
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $qrData)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'QR code is missing required information.',
                ], 422);
            }
        }

        // Verify the cryptographic signature and QR expiration.
        if (! $qrService->verifyPayload($qrData, $signature)) {
            return response()->json([
                'valid' => false,
                'message' => 'QR code is invalid or expired.',
            ], 422);
        }

        $farmer = $request->user();

        // Find the transaction identified by the signed QR payload.
        $transaction = Transaction::with(['merchant', 'farmer', 'items'])
            ->where('uuid', $qrData['transaction_uuid'])
            ->first();

        if (! $transaction) {
            return response()->json([
                'valid' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        $expectedAmount = number_format(
            (float) $transaction->total_amount,
            2,
            '.',
            ''
        );

        $expectedIssuedAt = $transaction->created_at->timestamp;

        $expectedReference = $transaction->farmer_id
            ? $transaction->farmer->beneficiary_id
                . '|'
                . $transaction->uuid
                . '|'
                . $expectedIssuedAt
            : '';

        $matchesDatabase =
            (int) $qrData['version'] === 1
            && (int) $transaction->farmer_id === (int) $farmer->id
            && (string) $qrData['beneficiary_id'] === (string) $farmer->beneficiary_id
            && (string) $qrData['total_amount'] === $expectedAmount
            && (int) $qrData['issued_at'] === $expectedIssuedAt
            && $transaction->qr_expires_at !== null
            && (int) $qrData['expires_at'] === $transaction->qr_expires_at->timestamp
            && (string) $qrData['reference'] === $expectedReference
            && $transaction->qr_signature !== null
            && hash_equals((string) $transaction->qr_signature, $signature);

        if (! $matchesDatabase || $transaction->qr_expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'QR code does not match this transaction or has expired.',
            ], 422);
        }

        if ($transaction->status !== 'pending') {
            return response()->json([
                'valid' => false,
                'message' => 'This transaction is no longer pending.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'QR verified. Please review your order.',
            'transaction' => [
                'uuid' => $transaction->uuid,
                'merchant' => $transaction->merchant->name,
                'total_amount' => $expectedAmount,
                'status' => $transaction->status,
                'expires_at' => $transaction->qr_expires_at->timezone('Asia/Manila')->toIso8601String(),
                'items' => $transaction->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ])->values(),
            ],
        ]);
    }

    public function scanPage()
    {
        return view('farmer.scan');
    }
}
