<form action="{{ $action }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card-body p-4">
        <div class="mb-3">
            <label for="sku" class="form-label fw-semibold">Product SKU</label>
            <input type="text" id="sku" name="sku"
                   class="form-control @error('sku') is-invalid @enderror"
                   value="{{ old('sku', $product?->sku) }}"
                   maxlength="100" required>
            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Product Name</label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product?->name) }}"
                   maxlength="255" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      maxlength="2000">{{ old('description', $product?->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label fw-semibold">Price per Bag (₱)</label>
                <input type="number" id="price" name="price"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $product?->price) }}"
                       min="0.01" max="9999999999.99" step="0.01" required>
                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="stock" class="form-label fw-semibold">Stock (bags)</label>
                <input type="number" id="stock" name="stock"
                       class="form-control @error('stock') is-invalid @enderror"
                       value="{{ old('stock', $product?->stock ?? 0) }}"
                       min="0" step="1" required>
                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white p-3 d-flex justify-content-end gap-2">
        <a href="{{ route('merchant.products.index') }}" class="btn btn-outline-secondary">
            Cancel
        </a>
        <button type="submit" class="btn btn-success">
            {{ $buttonText }}
        </button>
    </div>
</form>
