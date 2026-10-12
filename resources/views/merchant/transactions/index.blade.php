@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Transaction History</h2>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
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
                            <td>{{ $transaction->uuid }}</td>
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
                                <span class="badge bg-secondary">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                @if ($transaction->status === 'pending' && $transaction->qr_expires_at && $transaction->qr_expires_at->isFuture())
                                    <a href="{{ route('merchant.pos.qr', $transaction->uuid) }}" class="btn btn-sm btn-outline-primary">View QR</a>
                                @elseif ($transaction->status === 'pending')
                                    <span class="text-muted">QR expired</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
