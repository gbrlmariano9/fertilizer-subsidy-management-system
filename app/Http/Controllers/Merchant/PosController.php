<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Services\TransactionQrService;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('merchant_id', auth()->id())
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('merchant.pos.index', compact('products'));
    }

    public function verifyBeneficiary(Request $request)
    {
        $validated = $request->validate([
            'beneficiary_id' => ['required', 'string', 'max:100'],
        ]);

        $farmer = User::query()
            ->where('beneficiary_id', $validated['beneficiary_id'])
            ->where('role', 'farmer')
            ->where('is_verified', true)
            ->where('is_active', true)
            ->first();

        if (! $farmer) {
            return response()->json([
                'valid' => false,
                'message' => 'Beneficiary ID not found or not eligible.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Verified beneficiary.',
            'beneficiary' => [
                'beneficiary_id' => $farmer->beneficiary_id,
                'name' => $farmer->name,
                'subsidy_balance' => (float) $farmer->subsidy_balance,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'beneficiary_id' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $selectedItems = collect($validated['items'])
            ->filter(fn ($item) => (int) $item['quantity'] > 0);

        if ($selectedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Please select at least one product quantity.',
            ]);
        }

        $transaction = DB::transaction(function () use ($validated, $selectedItems) {
            $merchant = User::query()
                ->whereKey(auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $merchant->role === 'merchant'
                    && $merchant->is_verified
                    && $merchant->is_active,
                403,
                'Merchant account is not eligible.'
            );

            $farmer = User::query()
                ->where('beneficiary_id', $validated['beneficiary_id'])
                ->where('role', 'farmer')
                ->where('is_verified', true)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $farmer) {
                throw ValidationException::withMessages([
                    'beneficiary_id' => 'Beneficiary ID is invalid or not eligible.',
                ]);
            }

            $itemsToSave = [];
            $totalAmount = 0;

            foreach ($selectedItems as $item) {
                $product = Product::query()
                    ->where('merchant_id', $merchant->id)
                    ->where('is_active', true)
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product is unavailable or does not belong to your store.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}. Available: {$product->stock}.",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $subtotal = round($unitPrice * $quantity, 2);
                $totalAmount = round($totalAmount + $subtotal, 2);

                $itemsToSave[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            if ($totalAmount > (float) $farmer->subsidy_balance) {
                throw ValidationException::withMessages([
                    'beneficiary_id' => 'The farmer does not have enough subsidy balance for this order.',
                ]);
            }

            $transaction = Transaction::create([
                'uuid' => (string) Str::uuid(),
                'farmer_id' => $farmer->id,
                'merchant_id' => $merchant->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'qr_expires_at' => now()->addMinutes(15),
            ]);

            foreach ($itemsToSave as $item) {
                $transaction->items()->create($item);
            }

            return $transaction;
        }, 3);

        return redirect()
        ->route('merchant.pos.qr', ['uuid' => $transaction->uuid])
        ->with('success', 'Pending order created successfully.');
    }


    public function showQr($uuid, TransactionQrService $qrService)
    {
        $transaction = Transaction::with(['farmer', 'items'])
            ->where('uuid', $uuid)
            ->where('merchant_id', auth()->id())
            ->firstOrFail();

        if ($transaction->status !== 'pending') {
            abort(403, 'Only pending transactions can display a QR code.');
        }

        if (
            $transaction->qr_expires_at &&
            $transaction->qr_expires_at->isPast()
        ) {
            abort(410, 'This QR code has expired. Please create a new transaction.');
        }

        $qrData = $qrService->generatePayload($transaction);

        $qrContent = json_encode(array_merge(
            $qrData['payload'],
            ['signature' => $qrData['signature']]
        ));

        return view('merchant.pos.qr', [
            'transaction' => $transaction,
            'qrContent' => $qrContent,
        ]);
    }
}
