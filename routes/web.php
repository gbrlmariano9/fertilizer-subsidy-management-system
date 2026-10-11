<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Merchant\ProductController;
use App\Http\Controllers\Merchant\PosController;
use App\Http\Controllers\Farmer\FarmerQrController;
use App\Http\Controllers\Farmer\OrderController;
use App\Http\Controllers\Farmer\TransactionController as FarmerTransactionController;
use App\Http\Controllers\Farmer\CheckoutController;
use App\Http\Controllers\Merchant\WithdrawalController;

use App\Http\Controllers\Webhooks\PaymentWebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;


use App\Http\Controllers\Merchant\TransactionController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware(['auth', 'role:farmer'])
    ->get('/farmer/dashboard', function () {
        return view('farmer.dashboard');
    })
    ->name('farmer.dashboard');

Route::middleware(['auth', 'role:merchant'])
    ->get('/merchant/dashboard', function () {
        return view('merchant.dashboard');
    })
    ->name('merchant.dashboard');

Route::post('/farmer/scan-qr', [FarmerQrController::class, 'verify'])
    ->middleware(['auth', 'role:farmer'])
    ->name('farmer.scan-qr');

Route::get('/farmer/scan', [
    FarmerQrController::class,
    'scanPage',
])
    ->middleware(['auth', 'role:farmer'])
    ->name('farmer.scan');

Route::post('/webhooks/payments', [
    PaymentWebhookController::class,
    'handle',
])->withoutMiddleware([
    ValidateCsrfToken::class,
])->name('webhooks.payments');


Route::middleware(['auth', 'role:farmer'])
    ->prefix('farmer')
    ->name('farmer.')
    ->group(function () {

        Route::get('/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/{uuid}', [OrderController::class, 'show'])
            ->name('orders.show');

        Route::get('/transactions', [FarmerTransactionController::class, 'index'])
            ->name('transactions.index');

        Route::get('/checkout/scan', [
            CheckoutController::class,
            'scan',
        ])->name('checkout.scan');

        Route::post('/checkout/review', [
            CheckoutController::class,
            'review',
        ])->middleware('throttle:10,1')
            ->name('checkout.review');


        Route::post('/checkout/{uuid}/payment', [
            CheckoutController::class,
            'startPayment',
        ])->middleware('throttle:5,1')
            ->name('checkout.payment');

        Route::get('/checkout/{uuid}/return', [
            CheckoutController::class,
            'paymentReturn',
        ])->name('checkout.payment.return');


        Route::get('/checkout/{uuid}/status', [
            CheckoutController::class,
            'paymentStatus',
        ])
            ->middleware('throttle:30,1')
            ->name('checkout.payment.status');

        Route::post('/checkout/{uuid}/cancel', [
            CheckoutController::class,
            'cancelOrder',
        ])->middleware('throttle:10,1')
            ->name('checkout.cancel');

    });


Route::middleware(['auth', 'role:merchant'])
    ->prefix('merchant')
    ->name('merchant.')
    ->group(function () {

        Route::get('/products', [ProductController::class, 'index'])
            ->name('products.index');

        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');

        Route::post('/products', [ProductController::class, 'store'])
            ->name('products.store');

        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('products.update');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

        Route::patch('/products/{product}/activate', [ProductController::class, 'activate'])
            ->name('products.activate');



        Route::get('/pos', [PosController::class, 'index'])
            ->name('pos.index');

        Route::post('/pos/verify-beneficiary', [PosController::class, 'verifyBeneficiary'])
            ->name('pos.verify-beneficiary');

        Route::post('/pos', [PosController::class, 'store'])
            ->name('pos.store');

        Route::get('/pos/{uuid}/qr', [PosController::class, 'showQr'])
            ->name('pos.qr');

        Route::get('/transactions', [TransactionController::class,'index',])
            ->name('transactions.index');


        Route::get('/withdrawals', [WithdrawalController::class,'index',])
            ->name('withdrawals.index');

        Route::post('/withdrawals', [WithdrawalController::class, 'store',])
            ->middleware('throttle:5,1')
            ->name('withdrawals.store');
    });


