@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Fertilizer Inventory</h2>
            <p class="text-muted mb-0">Manage your fertilizer products, prices, and stock.</p>
        </div>

        <a href="{{ route('merchant.products.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
    </div>

    {{-- @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif --}}

    @if ($errors->any())
        <div class="alert alert-danger">
            Please check the form and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 js-datatable">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock (bags)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <small class="text-muted">
                                    {{ $product->description ?: 'No description' }}
                                </small>
                            </td>
                            <td>₱{{ number_format((float) $product->price, 2) }}</td>
                            <td>{{ number_format($product->stock) }}</td>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('merchant.products.edit', $product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>


                                @if ($product->is_active)
                                    <form action="{{ route('merchant.products.destroy', $product) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Deactivate this product?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Inactive
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('merchant.products.activate', $product) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Activate this product again?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            Activate
                                        </button>
                                    </form>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No products found. Add your first fertilizer product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
