<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionQrService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Jobs\DeliverLocalPaymentWebhook;
use App\Services\Payments\LocalPaymentSimulator;
use App\Services\Payments\PaymentSettlementService;

class CheckoutController extends Controller
{
    public function scan()
    {
        return view('farmer.checkout.scan');
    }

    public function review(
        Request $request,
        TransactionQrService $qrService
    ) {
        $validated = $request->validate([
            'qr_payload' => ['required', 'string', 'max:5000'],
        ]);

        $decoded = json_decode($validated['qr_payload'], true);

        if (
            ! is_array($decoded) ||
            ! isset($decoded['signature']) ||
            ! is_string($decoded['signature'])
        ) {
            throw ValidationException::withMessages([
                'qr_payload' => 'Invalid QR format. Please scan a valid merchant QR code.',
            ]);
        }

        $signature = $decoded['signature'];

        unset($decoded['signature']);

        // Verify the cryptographic signature and QR expiration.
        if (! $qrService->verifyPayload($decoded, $signature)) {
            throw ValidationException::withMessages([
                'qr_payload' => 'Invalid or expired QR code. Please request a new QR.',
            ]);
        }

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
            if (! array_key_exists($key, $decoded)) {
                throw ValidationException::withMessages([
                    'qr_payload' => 'Incomplete QR security information.',
                ]);
            }
        }

        // Only allow the farmer assigned to this transaction.
        $transaction = Transaction::with([
            'farmer',
            'merchant',
            'items',
        ])
            ->where('uuid', $decoded['transaction_uuid'])
            ->where('farmer_id', auth()->id())
            ->first();

        if (! $transaction) {
            throw ValidationException::withMessages([
                'qr_payload' => 'This QR is invalid or is not assigned to your farmer account.',
            ]);
        }

        $expectedReference =
            $transaction->farmer->beneficiary_id
            . '|'
            . $transaction->uuid
            . '|'
            . $transaction->created_at->timestamp;

        // Compare the signed QR data with the authoritative database values.
        $matchesDatabase =
            (int) $decoded['version'] === 1
            && $decoded['beneficiary_id'] === $transaction->farmer->beneficiary_id
            && $decoded['total_amount'] === number_format(
                (float) $transaction->total_amount,
                2,
                '.',
                ''
            )
            && (int) $decoded['issued_at'] === $transaction->created_at->timestamp
            && $transaction->qr_expires_at !== null
            && (int) $decoded['expires_at'] === $transaction->qr_expires_at->timestamp
            && $decoded['reference'] === $expectedReference
            && is_string($transaction->qr_signature)
            && hash_equals($transaction->qr_signature, $signature);

        if (! $matchesDatabase) {
            throw ValidationException::withMessages([
                'qr_payload' => 'QR information does not match the original transaction.',
            ]);
        }

        if ($transaction->status !== 'pending') {
            throw ValidationException::withMessages([
                'qr_payload' => 'This transaction is no longer pending.',
            ]);
        }

        if ($transaction->qr_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'qr_payload' => 'This QR code has expired. Please ask the merchant to create a new one.',
            ]);
        }

        return view('farmer.checkout.review', [
            'transaction' => $transaction,
        ]);
    }




    public function startPayment(
        string $uuid,
        LocalPaymentSimulator $simulator
    ) {
        $transaction = Transaction::query()
            ->where('uuid', $uuid)
            ->where('farmer_id', auth()->id())
            ->firstOrFail();

        if ($transaction->status !== 'pending') {
            return back()->withErrors([
                'payment' => 'This transaction is no longer pending.',
            ]);
        }

        if (
            ! $transaction->qr_expires_at ||
            $transaction->qr_expires_at->isPast()
        ) {
            return back()->withErrors([
                'payment' => 'The QR has expired. Please ask the merchant for a new order QR.',
            ]);
        }

        $transaction->payment_reference = $simulator->referenceFor($transaction);
        $transaction->save();

        // The in-app payment confirmation is delivered after the response.
        // The farmer status page polls as a backup and uses the same
        // idempotent settlement service.
        DeliverLocalPaymentWebhook::dispatch($transaction->id)->afterResponse();

        return redirect()->route('farmer.checkout.payment.return', [
            'uuid' => $transaction->uuid,
        ]);
    }

    public function paymentReturn(string $uuid)
    {
        $transaction = Transaction::with(['merchant', 'items'])
            ->where('uuid', $uuid)
            ->where('farmer_id', auth()->id())
            ->firstOrFail();

        // The internal payment event or the polling fallback settles payment.

        return view('farmer.checkout.payment-status', [
            'transaction' => $transaction,
        ]);
    }


    public function paymentStatus(
        string $uuid,
        LocalPaymentSimulator $simulator,
        PaymentSettlementService $settlement
    ) {
        $transaction = Transaction::query()
            ->where('uuid', $uuid)
            ->where('farmer_id', auth()->id())
            ->firstOrFail();

        if ($transaction->payment_reference === $simulator->referenceFor($transaction)) {
            $settlement->settle(
                'local-polling:' . $transaction->uuid,
                $simulator->referenceFor($transaction),
                $transaction->uuid,
                $simulator->paidEventFor($transaction),
                'polling'
            );
        }

        return response()->json([
            'uuid' => $transaction->uuid,
            'status' => $transaction->fresh()->status,
            'paid_at' => $transaction->fresh()->paid_at?->timezone('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function cancelOrder(
        string $uuid,
        \App\Services\Payments\PaymentSettlementService $settlement
    ) {
        $transaction = Transaction::query()
            ->where('uuid', $uuid)
            ->where('farmer_id', auth()->id())
            ->firstOrFail();

        $result = $settlement->cancelPending($transaction);

        if ($result === 'payment_active') {
            return back()->withErrors([
                'payment' => 'Nagsimula na ang payment. Hintayin muna ang resulta bago kanselahin ang order.',
            ]);
        }

        if (str_starts_with($result, 'ignored_')) {
            return back()->withErrors([
                'payment' => 'Hindi na pending ang order na ito.',
            ]);
        }

        return redirect()->route('farmer.transactions.index')
            ->with('status', 'Kinansela ang order.');
    }


}
