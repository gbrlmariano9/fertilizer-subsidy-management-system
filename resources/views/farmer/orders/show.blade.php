@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('farmer.orders.index') }}" class="btn btn-secondary btn-sm mb-3">
        Back to My Orders
    </a>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Review Fertilizer Order</h4>
        </div>

        <div class="card-body">
            <p><strong>Merchant:</strong> {{ $order->merchant->name }}</p>
            <p><strong>Reference:</strong> {{ $order->uuid }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>

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
                <div class="alert alert-info">
                    This order is pending payment. Payment confirmation will be available
                    after the payment verification flow is implemented.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
