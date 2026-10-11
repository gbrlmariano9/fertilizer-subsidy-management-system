<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class TransactionQrService
{
    /**
     * Create a signed payload for a pending transaction.
     */
    public function generatePayload(Transaction $transaction): array
    {
        $transaction->loadMissing('farmer');

        if ($transaction->status !== 'pending') {
            throw new \RuntimeException(
                'QR codes can only be generated for pending transactions.'
            );
        }

        $now = now();

        // Reuse the expiry if the QR was already generated.
        if (! $transaction->qr_expires_at) {
            $transaction->qr_expires_at = now()->addMinutes(15);
        }

        $issuedAt = $transaction->created_at->timestamp;

        $payload = [
            'version' => 1,
            'transaction_uuid' => $transaction->uuid,
            'beneficiary_id' => $transaction->farmer->beneficiary_id,
            'total_amount' => number_format(
                (float) $transaction->total_amount,
                2,
                '.',
                ''
            ),
            'issued_at' => $issuedAt,
            'expires_at' => $transaction->qr_expires_at->timestamp,
            'reference' => $transaction->farmer->beneficiary_id
                . '|'
                . $transaction->uuid
                . '|'
                . $issuedAt,
        ];

        $canonicalPayload = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $signature = hash_hmac(
            'sha256',
            $canonicalPayload,
            $this->signingKey()
        );

        $transaction->qr_signature = $signature;
        $transaction->save();

        return [
            'payload' => $payload,
            'signature' => $signature,
        ];
    }

    /**
     * Verify the payload signature and expiry.
     */
    public function verifyPayload(array $payload, string $signature): bool
    {
        $required = [
            'version',
            'transaction_uuid',
            'beneficiary_id',
            'total_amount',
            'issued_at',
            'expires_at',
            'reference',
        ];

        foreach ($required as $key) {
            if (! array_key_exists($key, $payload)) {
                return false;
            }
        }

        $canonicalPayload = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $expectedSignature = hash_hmac(
            'sha256',
            $canonicalPayload,
            $this->signingKey()
        );

        if (! hash_equals($expectedSignature, $signature)) {
            return false;
        }

        if ((int) $payload['expires_at'] <= now()->timestamp) {
            return false;
        }

        return true;
    }

    private function signingKey(): string
    {
        return (string) config('app.key');
    }
}
