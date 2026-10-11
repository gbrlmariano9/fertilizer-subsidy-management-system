<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Services\Payments\LocalPaymentSimulator;
use App\Services\Payments\PaymentSettlementService;
use Illuminate\Foundation\Bus\Dispatchable;

class DeliverLocalPaymentWebhook
{
    use Dispatchable;

    public function __construct(public int $transactionId)
    {
    }

    public function handle(
        LocalPaymentSimulator $simulator,
        PaymentSettlementService $settlement,
        PaymentWebhookController $webhook
    ): void {
        $transaction = Transaction::find($this->transactionId);

        if (! $transaction || $transaction->payment_reference !== $simulator->referenceFor($transaction)) {
            return;
        }

        $event = $simulator->paidEventFor($transaction);
        $event['event_id'] = 'local-webhook:' . $transaction->uuid;
        $event['payment_reference'] = $simulator->referenceFor($transaction);

        $webhook->processVerifiedEvent($event, $settlement);
    }
}
