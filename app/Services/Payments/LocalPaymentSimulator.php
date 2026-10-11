<?php

namespace App\Services\Payments;

use App\Models\Transaction;

class LocalPaymentSimulator
{
    public function referenceFor(Transaction $transaction): string
    {
        return 'local-payment:' . $transaction->uuid;
    }

    public function paidEventFor(Transaction $transaction): array
    {
        return [
            'reference' => $transaction->uuid,
            'payment' => [
                'status' => 'paid',
                'currency' => 'PHP',
                'amount' => (int) round((float) $transaction->total_amount * 100),
            ],
        ];
    }
}
