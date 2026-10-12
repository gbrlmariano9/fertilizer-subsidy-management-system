@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('farmer.transactions.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i>Back to transactions
    </a>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Review your order</h4>
        </div>

        <div class="card-body">
            <p><strong>Merchant:</strong> {{ $order->merchant->name }}</p>
            <p><strong>Reference:</strong> {{ $order->uuid }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>

            @if ($order->status === 'pending')
                @php($qrIsActive = ! $order->payment_reference && $order->qr_expires_at && $order->qr_expires_at->isFuture())
                <div class="order-qr-status {{ $order->payment_reference || $qrIsActive ? 'is-active' : 'is-expired' }}" role="status">
                    <i class="bi {{ $order->payment_reference ? 'bi-hourglass-split' : ($qrIsActive ? 'bi-clock' : 'bi-qr-code') }}"></i>
                    <div>
                        <strong>{{ $order->payment_reference ? 'Payment confirmation in progress' : ($qrIsActive ? 'QR is still active' : 'QR code expired') }}</strong>
                        @if ($order->payment_reference)
                            <span>Payment has started. Please wait for its status to update before taking another action.</span>
                        @elseif ($qrIsActive)
                            <span>Scan the merchant’s QR before {{ $order->qr_expires_at->timezone('Asia/Manila')->format('M d, Y h:i A') }} to continue to payment.</span>
                        @elseif ($order->qr_expires_at)
                            <span>Expired {{ $order->qr_expires_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}. Ask the merchant for a new order QR if you still want to pay.</span>
                        @else
                            <span>This order has no active QR code. Ask the merchant to prepare a new one.</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>₱{{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th>₱{{ number_format((float) $order->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($order->status === 'pending')
                @if (! $order->payment_reference)
                    <div class="order-review-actions">
                        @if ($qrIsActive)
                            <a href="{{ route('farmer.scan') }}" class="btn btn-success"><i class="bi bi-qr-code-scan me-2"></i>Scan QR to pay</a>
                        @endif
                        <form method="POST" action="{{ route('farmer.checkout.cancel', $order->uuid) }}" data-confirm="Cancel this pending order? You can ask the merchant for a new QR if you want to pay later." data-confirm-title="Cancel this order?" data-confirm-action="Cancel order" data-confirm-variant="danger">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle me-2"></i>Cancel order</button>
                        </form>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
