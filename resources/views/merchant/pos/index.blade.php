@extends('layouts.app')

@section('content')
<div class="container py-4 merchant-pos-page">
    <div class="page-intro merchant-pos-intro">
        <span class="page-eyebrow">NEW ORDER</span>
        <h1 class="page-heading">Point of sale</h1>
        <p class="mb-0">Verify the beneficiary, choose fertilizer quantities, then create the order QR.</p>
    </div>

    {{-- @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif --}}

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('merchant.pos.store') }}">
        @csrf

        <div class="card border-0 shadow-sm mb-4 pos-beneficiary-card">
            <div class="card-body">
                <label for="beneficiary_id" class="form-label fw-semibold">
                    Farmer Beneficiary ID
                </label>

                <div class="input-group">
                    <input type="text"
                           name="beneficiary_id"
                           id="beneficiary_id"
                           class="form-control"
                           value="{{ old('beneficiary_id') }}"
                           placeholder="e.g. FARMER-001"
                           required>
                    <button type="button" id="verifyButton" class="btn btn-outline-success">
                        Verify
                    </button>
                </div>

                <div id="beneficiaryResult" class="mt-2" aria-live="polite"></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm pos-products-card">
            <div class="card-header bg-white py-3">
                <strong>Available Fertilizer Products</strong>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price per Bag</th>
                            <th>Available Stock</th>
                            <th style="width: 130px;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </td>
                                <td>₱{{ number_format((float) $product->price, 2) }}</td>
                                <td>{{ $product->stock }} bags</td>
                                <td>
                                    <input type="hidden"
                                           name="items[{{ $index }}][product_id]"
                                           value="{{ $product->id }}">

                                    <input type="number"
                                           name="items[{{ $index }}][quantity]"
                                           class="form-control quantity-input"
                                           min="0"
                                           max="{{ $product->stock }}"
                                           step="1"
                                           value="{{ old("items.$index.quantity", 0) }}"
                                           data-price="{{ $product->price }}"
                                           data-stock="{{ $product->stock }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No active products currently have stock.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold">Estimated Total</span>
                    <span id="estimatedTotal" class="fs-4 fw-bold">₱0.00</span>
                </div>

                <button type="submit"
                        class="btn btn-success w-100"
                        @disabled($products->isEmpty())>
                    Create Pending Order
                </button>

                <small class="text-muted d-block mt-2">
                    This creates a pending order only. Payment and balance deductions
                    are not performed at this stage.
                </small>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const verifyButton = document.getElementById('verifyButton');
    const beneficiaryInput = document.getElementById('beneficiary_id');
    const beneficiaryResult = document.getElementById('beneficiaryResult');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const totalElement = document.getElementById('estimatedTotal');

    function updateTotal() {
        let total = 0;

        quantityInputs.forEach(function (input) {
            const quantity = Math.max(0, parseInt(input.value || '0', 10));
            const price = Number(input.dataset.price || 0);
            total += quantity * price;
        });

        totalElement.textContent = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(total);
    }

    quantityInputs.forEach(function (input) {
        input.addEventListener('input', updateTotal);
    });

    verifyButton.addEventListener('click', async function () {
        const beneficiaryId = beneficiaryInput.value.trim();

        beneficiaryResult.textContent = '';

        if (!beneficiaryId) {
            beneficiaryResult.textContent = 'Enter a Beneficiary ID first.';
            beneficiaryResult.className = 'mt-2 text-danger';
            return;
        }

        verifyButton.disabled = true;
        verifyButton.textContent = 'Checking...';

        try {
            const response = await fetch(
                @json(route('merchant.pos.verify-beneficiary')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({ beneficiary_id: beneficiaryId })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to verify beneficiary.');
            }

            if (data.valid) {
                beneficiaryResult.textContent =
                    'Verified: ' + data.beneficiary.name +
                    ' | Subsidy balance: ' +
                    new Intl.NumberFormat('en-PH', {
                        style: 'currency',
                        currency: 'PHP'
                    }).format(data.beneficiary.subsidy_balance);

                beneficiaryResult.className = 'mt-2 text-success';
            } else {
                beneficiaryResult.textContent = data.message;
                beneficiaryResult.className = 'mt-2 text-danger';
            }
        } catch (error) {
            beneficiaryResult.textContent =
                error.message || 'Verification failed. Please try again.';
            beneficiaryResult.className = 'mt-2 text-danger';
        } finally {
            verifyButton.disabled = false;
            verifyButton.textContent = 'Verify';
        }
    });

    updateTotal();
});
</script>
@endsection
