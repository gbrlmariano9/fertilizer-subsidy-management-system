@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 800px">
    <a href="{{ route('merchant.products.index') }}" class="text-decoration-none">
        ← Back to Inventory
    </a>

    <h2 class="fw-bold mt-3">Edit Fertilizer Product</h2>
    <p class="text-muted">Update product details, pricing, and stock.</p>

    @include('merchant.products.form', [
        'product' => $product,
        'action' => route('merchant.products.update', $product),
        'method' => 'PUT',
        'buttonText' => 'Update Product',
    ])
</div>
@endsection
