<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, PaymentSettlementService $settlement)
    {
        $secret = config('services.payment_webhook_secret');
        $rawBody = $request->getContent();
        $providedSignature = (string) $request->header('X-Payment-Signature', '');

        if (! is_string($secret) || $secret === '' || $providedSignature === '') {
            return response()->json(['message' => 'Webhook configuration is missing.'], 500);
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $secret);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $event = json_decode($rawBody, true);

        if (! is_array($event)) {
            return response()->json(['message' => 'Invalid webhook payload.'], 400);
        }

        try {
            return response()->json([
                'received' => true,
                'settlement' => $this->processVerifiedEvent($event, $settlement),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Payment webhook settlement failed.', [
                'event_id' => data_get($event, 'event_id'),
                'payment_reference' => data_get($event, 'payment_reference'),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'received' => false,
                'message' => 'Settlement failed. Polling can reconcile the payment.',
            ], 500);
        }
    }

    public function processVerifiedEvent(
        array $event,
        PaymentSettlementService $settlement
    ): string {
        $eventId = data_get($event, 'event_id');
        $paymentReference = data_get($event, 'payment_reference');
        $reference = data_get($event, 'reference');
        $status = data_get($event, 'payment.status');

        if (
            ! is_string($eventId) || $eventId === '' ||
            ! is_string($paymentReference) || $paymentReference === '' ||
            ! is_string($reference) || $reference === ''
        ) {
            throw new \RuntimeException('Payment event identifiers are missing.');
        }

        if ($status === 'paid') {
            return $settlement->settle(
                $eventId,
                $paymentReference,
                $reference,
                $event,
                'webhook'
            );
        }

        if ($status === 'failed') {
            return $settlement->failVerified(
                $eventId,
                $paymentReference,
                $reference,
                $event,
                'webhook'
            );
        }

        throw new \RuntimeException('Payment event status is not final.');
    }
}
