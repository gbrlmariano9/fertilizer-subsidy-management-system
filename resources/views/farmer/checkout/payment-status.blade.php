@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mx-auto text-center" style="max-width: 600px;">
        <h2 id="payment-heading">Checking Payment Status</h2>

        <p id="payment-message" aria-live="polite">
            We are confirming your payment with the system.
            Please keep this page open.
        </p>

        <div id="payment-spinner" class="my-3">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Checking payment...</span>
            </div>
        </div>

        <p>
            <strong>Order:</strong>
            <span class="text-break">{{ $transaction->uuid }}</span>
        </p>

        <p>
            <strong>Status:</strong>
            <span id="payment-status">
                {{ ucfirst($transaction->status) }}
            </span>
        </p>

        <p>
            <strong>Total:</strong>
            ₱{{ number_format((float) $transaction->total_amount, 2) }}
        </p>

        <a href="{{ route('farmer.dashboard') }}"
           class="btn btn-outline-secondary">
            Back to Dashboard
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusUrl = @json(
        route('farmer.checkout.payment.status', [
            'uuid' => $transaction->uuid,
        ])
    );

    const heading = document.getElementById('payment-heading');
    const message = document.getElementById('payment-message');
    const statusText = document.getElementById('payment-status');
    const spinner = document.getElementById('payment-spinner');

    const initialStatus = @json($transaction->status);
    let attempts = 0;
    const maxAttempts = 24;
    let stopped = false;

    function showStatus(status) {
        statusText.textContent =
            status.charAt(0).toUpperCase() + status.slice(1);

        if (status === 'paid') {
            heading.textContent = 'Payment Confirmed';
            message.textContent =
                'Your payment has been verified and the transaction has been settled.';
            spinner.classList.add('d-none');
            stopped = true;
            return;
        }

        if (status !== 'pending') {
            heading.textContent = 'Transaction Status: ' +
                status.charAt(0).toUpperCase() + status.slice(1);

            message.textContent = status === 'failed'
                ? 'Verified that the payment failed. No subsidy, stock, or wallet changes were made.'
                : status === 'cancelled'
                    ? 'This order was cancelled. It will not be settled through the payment flow.'
                    : 'Please check your transaction details or contact support if you need help.';

            spinner.classList.add('d-none');
            stopped = true;
            return;
        }

        heading.textContent = 'Payment Verification Pending';
        message.textContent = 'We are waiting for the system to confirm your payment.';
    }

    async function checkPaymentStatus() {
        if (stopped || attempts >= maxAttempts) {
            if (!stopped) {
                message.textContent =
                    'Verification is taking longer than expected. Your transaction has not been confirmed on this page. You can refresh later to check again.';
                spinner.classList.add('d-none');
            }
            stopped = true;
            return;
        }

        attempts++;

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Status check failed.');
            }

            const data = await response.json();
            showStatus(data.status);
        } catch (error) {
            message.textContent =
                'We could not check the payment status right now. Retrying shortly.';
        }

        if (!stopped && attempts < maxAttempts) {
            window.setTimeout(checkPaymentStatus, 5000);
        } else if (!stopped) {
            window.setTimeout(checkPaymentStatus, 5000);
        }
    }

    showStatus(initialStatus);

    if (initialStatus === 'pending') {
        window.setTimeout(checkPaymentStatus, 1000);
    }
});
</script>
@endsection
