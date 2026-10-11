@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">My Fertilizer Orders</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @forelse ($orders as $order)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <strong>{{ $order->merchant->name }}</strong>
                            <div class="text-muted small">
                                Reference: {{ $order->uuid }}
                            </div>
                            <div class="text-muted small">
                                {{ $order->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>

                        <div class="text-end">
                            <strong>
                                ₱{{ number_format((float) $order->total_amount, 2) }}
                            </strong>
                            <div>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('farmer.orders.show', $order->uuid) }}"
                       class="btn btn-primary btn-sm mt-3">
                        Review Order
                    </a>
                </div>
            @empty
                <p class="text-muted mb-0">You don't have any orders yet.</p>
            @endforelse

            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
