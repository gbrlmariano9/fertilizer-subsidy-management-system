@extends('layouts.app')

@section('content')
<div class="container py-4 merchant-transactions-page">
    <div class="page-intro mb-4">
        <span class="page-eyebrow">SALES ACTIVITY</span>
        <h1 class="page-heading">Transactions</h1>
        <p class="mb-0">Review beneficiary orders, payment status, and active QR codes.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="content-card merchant-transactions-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 js-datatable merchant-transactions-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Farmer</th>
                        <th>Beneficiary ID</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td><span class="transaction-ref-label">REFERENCE</span><span class="merchant-transaction-ref">{{ $transaction->uuid }}</span></td>
                            <td>
                                {{ $transaction->farmer->name ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $transaction->farmer->beneficiary_id ?? 'N/A' }}
                            </td>
                            <td>
                                @foreach ($transaction->items as $item)
                                    <div>
                                        {{ $item->product_name }}
                                        × {{ $item->quantity }}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                ₱{{ number_format((float) $transaction->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $qrExpired = $transaction->status === 'pending'
                                        && $transaction->qr_expires_at
                                        && $transaction->qr_expires_at->isPast();
                                @endphp
                                <span class="transaction-status status-{{ $qrExpired ? 'expired' : $transaction->status }}">
                                    <span></span>{{ $qrExpired ? 'Expired' : ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                </span>
                                @if ($qrExpired)
                                    <small class="d-block mt-1 text-muted">Expired {{ $transaction->qr_expires_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                @if ($transaction->status === 'pending' && $transaction->qr_expires_at && $transaction->qr_expires_at->isFuture())
                                    <a href="{{ route('merchant.pos.qr', $transaction->uuid) }}" class="btn btn-sm btn-outline-primary">View QR</a>
                                @elseif ($transaction->status === 'pending')
                                    <span class="text-muted">Expired</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                @if ($transaction->status === 'pending')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger mt-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelOrderModal"
                                        data-cancel-url="{{ route('merchant.transactions.cancel', $transaction->uuid) }}"
                                        data-order-reference="{{ $transaction->uuid }}"
                                    >Delete order</button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="merchant-transactions-empty"><i class="bi bi-receipt"></i><strong>No transactions yet</strong><span>Orders you prepare will appear here.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5" id="cancelOrderModalTitle">Delete this order?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">This removes the order from your active transaction list, and the farmer won’t be able to pay it.</p>
                <p class="small text-muted mb-2">The record is kept for audit purposes.</p>
                <p class="small text-muted mb-0">Order reference: <span id="cancelOrderReference" class="font-monospace"></span></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep order</button>
                <form method="POST" id="cancelOrderForm" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger">Yes, delete order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('cancelOrderModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        document.getElementById('cancelOrderForm').action = trigger.dataset.cancelUrl;
        document.getElementById('cancelOrderReference').textContent = trigger.dataset.orderReference;
    });
</script>
@endsection
