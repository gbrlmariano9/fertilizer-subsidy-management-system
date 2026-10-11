@extends('layouts.app')

@section('title', 'Review order')

@section('content')
<div class="checkout-review-page">
    <div class="checkout-review-wrap">
        <header class="checkout-review-heading">
            <span class="checkout-kicker"><i class="bi bi-shield-check"></i> ORDER CHECK</span>
            <h1>Review your order</h1>
            <p>Make sure the merchant and fertilizer details are correct before you choose what to do.</p>
        </header>

        <section class="checkout-order-card">
            <div class="checkout-order-top">
                <div>
                    <span class="checkout-label">Merchant</span>
                    <h2>{{ $transaction->merchant->name }}</h2>
                </div>
                <span class="transaction-status status-{{ $transaction->status }}"><span></span>{{ ucfirst($transaction->status) }}</span>
            </div>

            <div class="checkout-order-meta">
                <div><span class="checkout-label">Transaction reference</span><strong class="checkout-reference">{{ $transaction->uuid }}</strong></div>
                <div><span class="checkout-label">Beneficiary ID</span><strong>{{ $transaction->farmer->beneficiary_id }}</strong></div>
            </div>

            <div class="table-responsive checkout-items-wrap">
                <table class="table align-middle checkout-items-table mb-0">
                    <thead><tr><th>Fertilizer</th><th class="text-end">Unit price</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($transaction->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-end">₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="checkout-total-row"><span>Total order</span><strong>₱{{ number_format((float) $transaction->total_amount, 2) }}</strong></div>
            <div class="checkout-balance-row"><span><i class="bi bi-wallet2"></i> Available subsidy balance</span><strong>₱{{ number_format((float) $transaction->farmer->subsidy_balance, 2) }}</strong></div>
        </section>

        <div class="checkout-review-note"><i class="bi bi-info-circle"></i><span>Your subsidy and the merchant’s stock are unchanged until payment is confirmed.</span></div>

        @if ($errors->has('payment'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('payment') }}</div>
        @endif

        <div class="checkout-action-grid">
            <form method="POST" action="{{ route('farmer.checkout.payment', $transaction->uuid) }}">
                @csrf
                <button type="submit" class="btn checkout-pay-button" {{ $transaction->status !== 'pending' ? 'disabled' : '' }}>
                    <i class="bi bi-check-circle"></i> Pay now
                </button>
            </form>
            @if ($transaction->status === 'pending' && ! $transaction->payment_reference)
                <form method="POST" action="{{ route('farmer.checkout.cancel', $transaction->uuid) }}">
                    @csrf
                    <button type="submit" class="btn checkout-cancel-button"><i class="bi bi-x-circle"></i> Cancel order</button>
                </form>
            @else
                <a href="{{ route('farmer.checkout.scan') }}" class="btn checkout-cancel-button"><i class="bi bi-arrow-left-circle"></i> Back to scan</a>
            @endif
        </div>
    </div>
</div>
@endsection
