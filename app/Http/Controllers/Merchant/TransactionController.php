<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\Payments\PaymentSettlementService;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with([
            'farmer',
            'items',
        ])
            ->where('merchant_id', auth()->id())
            ->latest()
            ->get();

        return view('merchant.transactions.index', compact('transactions'));
    }

    public function cancel(string $uuid, PaymentSettlementService $settlement)
    {
        $transaction = Transaction::query()
            ->where('uuid', $uuid)
            ->where('merchant_id', auth()->id())
            ->firstOrFail();

        $result = $settlement->cancelPending($transaction);

        if ($result === 'payment_active') {
            return back()->withErrors([
                'order' => 'Payment has started. Wait for the result before cancelling this order.',
            ]);
        }

        if (str_starts_with($result, 'ignored_')) {
            return back()->withErrors([
                'order' => 'This order is no longer pending and cannot be cancelled.',
            ]);
        }

        return redirect()->route('merchant.transactions.index')
            ->with('success', 'Order cancelled.');
    }
}
