@extends('layouts.app')

@section('content')
<div class="container py-4 merchant-transactions-page">
    <div class="page-intro mb-4">
        <span class="page-eyebrow">SALES ACTIVITY</span>
        <h1 class="page-heading">Transactions</h1>
        <p class="mb-0">Review beneficiary orders, payment status, and active QR codes.</p>
    </div>

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
                        <th>QR Code</th>
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
@endsection
