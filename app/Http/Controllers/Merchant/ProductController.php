<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = auth()->user()
            ->products()
            ->latest()
            ->paginate(10);

        return view('merchant.products.index', compact('products'));
    }

    public function create()
    {
        return view('merchant.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $request->user()->products()->create($validated);

        return redirect()
            ->route('merchant.products.index')
            ->with('success', 'Fertilizer product added successfully.');
    }

    public function edit(Product $product)
    {
        $this->authorizeMerchantProduct($product);

        return view('merchant.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeMerchantProduct($product);

        $validated = $request->validate([
            'sku' => [
                'required', 'string', 'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('merchant.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeMerchantProduct($product);

        $product->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('merchant.products.index')
            ->with('success', 'Product deactivated successfully.');
    }


    public function activate(Product $product)
    {
        $this->authorizeMerchantProduct($product);

        $product->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('merchant.products.index')
            ->with('success', 'Product activated successfully.');
    }


    private function authorizeMerchantProduct(Product $product): void
    {
        abort_unless(
            (int) $product->merchant_id === (int) auth()->id(),
            403,
            'You cannot manage another merchant’s products.'
        );
    }
}
