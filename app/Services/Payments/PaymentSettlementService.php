<?php

namespace App\Services\Payments;

use App\Models\PaymentLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentSettlementService
{
    public function settle(
        string $eventId,
        string $paymentReference,
        string $reference,
        array $paymentEvent,
        string $source = 'webhook'
    ): string {
        if (PaymentLog::where('event_id', $eventId)->exists()) {
            return 'duplicate_event';
        }

        try {
            return DB::transaction(function () use (
                $eventId,
                $paymentReference,
                $reference,
                $paymentEvent,
                $source
            ) {
                $transaction = Transaction::where('payment_reference', $paymentReference)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction || $transaction->uuid !== $reference) {
                    throw new RuntimeException('Payment event does not match a transaction.');
                }

                if (PaymentLog::where('event_id', $eventId)->exists()) {
                    return 'duplicate_event';
                }

                if ($transaction->status !== 'pending') {
                    $result = 'ignored_' . $transaction->status;
                    $this->logEvent($transaction, $eventId, 'payment.paid', $paymentReference, $reference, $source, $result);

                    return $result;
                }

                $payment = data_get($paymentEvent, 'payment', []);
                $amount = (int) round((float) $transaction->total_amount * 100);
                $isVerifiedPaidPayment = data_get($payment, 'status') === 'paid'
                    && strtoupper((string) data_get($payment, 'currency', '')) === 'PHP'
                    && (int) data_get($payment, 'amount', 0) === $amount;

                if (! $isVerifiedPaidPayment) {
                    throw new RuntimeException('Payment event is not a verified PHP payment matching the order total.');
                }

                $farmer = User::whereKey($transaction->farmer_id)->lockForUpdate()->firstOrFail();
                $merchant = User::whereKey($transaction->merchant_id)->lockForUpdate()->firstOrFail();
                $items = $transaction->items()->get();
                $products = [];

                foreach ($items as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();

                    if ($product->stock < $item->quantity) {
                        throw new RuntimeException('Insufficient stock for product: ' . $product->name);
                    }

                    $products[] = [$product, $item];
                }

                $total = (float) $transaction->total_amount;

                if ((float) $farmer->subsidy_balance < $total) {
                    throw new RuntimeException('Insufficient farmer subsidy balance.');
                }

                foreach ($products as [$product, $item]) {
                    $product->decrement('stock', $item->quantity);
                }

                $farmer->decrement('subsidy_balance', $total);
                $merchant->increment('wallet_balance', $total);
                $transaction->status = 'paid';
                $transaction->paid_at = now();
                $transaction->save();

                $this->logEvent($transaction, $eventId, 'payment.paid', $paymentReference, $reference, $source, 'processed');

                return 'settled';
            }, 3);
        } catch (QueryException $e) {
            if (PaymentLog::where('event_id', $eventId)->exists()) {
                return 'duplicate_event';
            }

            throw $e;
        }
    }

    public function failVerified(
        string $eventId,
        string $paymentReference,
        string $reference,
        array $paymentEvent,
        string $source = 'webhook'
    ): string {
        try {
            return DB::transaction(function () use ($eventId, $paymentReference, $reference, $paymentEvent, $source) {
                $transaction = Transaction::where('payment_reference', $paymentReference)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction || $transaction->uuid !== $reference) {
                    throw new RuntimeException('Payment event does not match a transaction.');
                }

                if (PaymentLog::where('event_id', $eventId)->exists()) {
                    return 'duplicate_event';
                }

                $payment = data_get($paymentEvent, 'payment', []);
                $expectedAmount = (int) round((float) $transaction->total_amount * 100);
                $verifiedFailure = data_get($payment, 'status') === 'failed'
                    && strtoupper((string) data_get($payment, 'currency', '')) === 'PHP'
                    && (int) data_get($payment, 'amount', 0) === $expectedAmount;

                if (! $verifiedFailure) {
                    throw new RuntimeException('Payment event is not a verified failed PHP payment matching the order total.');
                }

                if ($transaction->status !== 'pending') {
                    $result = 'ignored_' . $transaction->status;
                    $this->logEvent($transaction, $eventId, 'payment.failed', $paymentReference, $reference, $source, $result);

                    return $result;
                }

                $transaction->status = 'failed';
                $transaction->save();
                $this->logEvent($transaction, $eventId, 'payment.failed', $paymentReference, $reference, $source, 'processed');

                return 'failed';
            }, 3);
        } catch (QueryException $e) {
            if (PaymentLog::where('event_id', $eventId)->exists()) {
                return 'duplicate_event';
            }

            throw $e;
        }
    }

    public function cancelPending(Transaction $transaction): string
    {
        return DB::transaction(function () use ($transaction) {
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return 'ignored_' . $locked->status;
            }

            if ($locked->payment_reference) {
                return 'payment_active';
            }

            $locked->status = 'cancelled';
            $locked->save();

            // A farmer cancellation stays visible in transaction history.
            // A merchant deletion hides the mistaken order from normal lists.
            if (auth()->user()?->role === 'merchant') {
                $locked->delete();
            }

            PaymentLog::create([
                'transaction_id' => $locked->id,
                'event_type' => auth()->user()?->role === 'merchant' ? 'merchant.order.cancelled' : 'farmer.order.cancelled',
                'payload' => ['actor_id' => auth()->id()],
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return 'cancelled';
        }, 3);
    }

    private function logEvent(
        Transaction $transaction,
        string $eventId,
        string $eventType,
        string $paymentReference,
        string $reference,
        string $source,
        string $result
    ): void {
        PaymentLog::create([
            'transaction_id' => $transaction->id,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => [
                'payment_reference' => $paymentReference,
                'reference' => $reference,
                'source' => $source,
                'result' => $result,
            ],
            'status' => $result,
            'processed_at' => now(),
        ]);
    }
}
