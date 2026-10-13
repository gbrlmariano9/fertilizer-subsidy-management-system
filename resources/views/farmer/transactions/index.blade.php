@extends('layouts.app')

@section('title', 'My Transactions')

@section('content')
    <div class="page-intro transaction-page-intro">
        <span class="page-eyebrow">FARMER RECORDS</span>
        <h1 class="page-heading">My transactions</h1>
        <p class="mb-0">See your fertilizer purchases, payment status, and transaction details.</p>
    </div>

    <div class="row g-3 transaction-summary-row">
        <div class="col-12 col-sm-6 col-xl-4">
            <section class="content-card transaction-summary-card">
                <span class="transaction-summary-icon"><i class="bi bi-receipt"></i></span>
                <span class="transaction-summary-label">All transactions</span>
                <strong>{{ number_format($transactionCount) }}</strong>
                <small>Recorded on your account</small>
            </section>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <section class="content-card transaction-summary-card">
                <span class="transaction-summary-icon paid"><i class="bi bi-check2-circle"></i></span>
                <span class="transaction-summary-label">Total paid</span>
                <strong>₱{{ number_format((float) $paidTotal, 2) }}</strong>
                <small>Across completed payments</small>
            </section>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <section class="content-card transaction-summary-card">
                <span class="transaction-summary-icon pending"><i class="bi bi-clock-history"></i></span>
                <span class="transaction-summary-label">Pending payments</span>
                <strong>{{ number_format($pendingCount) }}</strong>
                <small>Awaiting payment</small>
            </section>
        </div>
    </div>

    <section class="content-card farmer-transaction-list">
        <div class="transaction-list-heading">
            <div>
                <h2>Transaction history</h2>
                <p>Your most recent activity appears first.</p>
            </div>
            <a href="{{ route('farmer.scan') }}" class="btn btn-outline-success">
                <i class="bi bi-qr-code-scan me-1"></i> Scan a merchant QR
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 farmer-transaction-table js-datatable">
                <thead>
                    <tr>
                        <th scope="col">Transaction</th>
                        <th scope="col">Merchant</th>
                        <th scope="col">Items</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date</th>
                        <th scope="col"><span class="visually-hidden">Receipt</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            @php
                                $qrExpired = $transaction->status === 'pending'
                                    && $transaction->qr_expires_at
                                    && $transaction->qr_expires_at->isPast();
                            @endphp
                            <td>
                                <span class="transaction-ref-label">Reference</span>
                                <span class="farmer-transaction-ref">{{ $transaction->uuid }}</span>
                            </td>
                            <td class="farmer-transaction-merchant">{{ $transaction->merchant->name ?? 'Merchant' }}</td>
                            <td>{{ $transaction->items->count() }} {{ \Illuminate\Support\Str::plural('item', $transaction->items->count()) }}</td>
                            <td class="farmer-transaction-amount">₱{{ number_format((float) $transaction->total_amount, 2) }}</td>
                            <td>
                                <span class="transaction-status status-{{ $qrExpired ? 'expired' : $transaction->status }}">
                                    <span></span>{{ $qrExpired ? 'Expired' : ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                </span>
                                @if ($qrExpired)
                                    <small class="d-block mt-1">Expired {{ $transaction->qr_expires_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</small>
                                @endif
                            </td>
                            <td class="farmer-transaction-date">{{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y') }}<small>{{ $transaction->created_at->timezone('Asia/Manila')->format('h:i A') }}</small></td>

                            <td>
                                @if ($transaction->status === 'paid')
                                    <a
                                        class="transaction-details-link transaction-receipt-link"
                                        href="{{ route('farmer.orders.show', $transaction->uuid) }}"
                                        aria-label="View transaction {{ $transaction->uuid }}"
                                    >
                                        <i class="bi bi-receipt-cutoff"></i>
                                        View receipt
                                    </a>
                                @elseif ($transaction->status === 'pending')
                                    <a
                                        class="transaction-details-link transaction-review-link"
                                        href="{{ route('farmer.orders.show', $transaction->uuid) }}"
                                        aria-label="Review order {{ $transaction->uuid }}"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Review order
                                    </a>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="farmer-transactions-empty">
                                    <span class="empty-receipt-icon"><i class="bi bi-receipt-cutoff"></i></span>
                                    <strong>No transactions yet</strong>
                                    <p>Your fertilizer purchases and payment updates will show up here.</p>
                                    <a href="{{ route('farmer.scan') }}" class="btn btn-success">
                                        <i class="bi bi-qr-code-scan me-1"></i> Scan a merchant QR
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>
@endsection
