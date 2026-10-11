@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-header">
            <h4 class="mb-0">Transaction QR Code</h4>
        </div>

        <div class="card-body text-center">
            <p><strong>Reference:</strong></p>
            <p>{{ $transaction->uuid }}</p>

            <p><strong>Beneficiary ID:</strong></p>
            <p>{{ $transaction->farmer->beneficiary_id }}</p>

            <p><strong>Total Amount:</strong></p>
            <h3>₱{{ number_format((float) $transaction->total_amount, 2) }}</h3>

            <div class="my-4">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(280)->generate($qrContent) !!}
            </div>

            <p class="text-muted">
                This QR code expires 15 minutes after transaction creation.
            </p>

            <a href="{{ route('merchant.pos.index') }}"
               class="btn btn-secondary">
                Back to POS
            </a>
        </div>
    </div>
</div>
@endsection
