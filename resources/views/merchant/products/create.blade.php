@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 800px">
    <a href="{{ route('merchant.products.index') }}" class="text-decoration-none">
        ← Back to Inventory
    </a>

    <h2 class="fw-bold mt-3">Add Fertilizer Product</h2>
    <p class="text-muted">Enter the product details and available stock.</p>

    @include('merchant.products.form', [
        'product' => null,
        'action' => route('merchant.products.store'),
        'method' => 'POST',
        'buttonText' => 'Save Product',
    ])
</div>
@endsection
