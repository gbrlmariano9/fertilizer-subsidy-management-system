<?php

namespace Tests\Feature;

use App\Models\PaymentLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Jobs\DeliverLocalPaymentWebhook;
use App\Services\Payments\PaymentSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_then_polling_settles_once_and_audits_both_paths(): void
    {
        $this->assertDualPathSettlement('webhook');
    }

    public function test_polling_then_webhook_settles_once_and_audits_both_paths(): void
    {
        $this->assertDualPathSettlement('polling');
    }

    public function test_verified_failed_payment_is_logged_without_settlement(): void
    {
        [$transaction, $product, $farmer, $merchant, $session] = $this->paymentFixture('failed');

        $result = app(PaymentSettlementService::class)->failVerified(
            'evt_failed_1',
            $transaction->payment_reference,
            $transaction->uuid,
            $session
        );

        $this->assertSame('failed', $result);
        $this->assertSame('failed', $transaction->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertEquals(500, (float) $farmer->fresh()->subsidy_balance);
        $this->assertEquals(0, (float) $merchant->fresh()->wallet_balance);
        $this->assertDatabaseHas('payment_logs', [
            'event_id' => 'evt_failed_1',
            'event_type' => 'payment.failed',
            'status' => 'processed',
        ]);
    }

    public function test_farmer_polling_endpoint_reconciles_a_verified_paid_session(): void
    {
        [$transaction, , $farmer, , $session] = $this->paymentFixture('paid');
        $response = $this->actingAs($farmer)->getJson(
            route('farmer.checkout.payment.status', $transaction->uuid)
        );

        $response->assertOk()->assertJsonPath('status', 'paid');
        $this->assertDatabaseHas('payment_logs', [
            'transaction_id' => $transaction->id,
            'event_type' => 'payment.paid',
            'status' => 'processed',
        ]);
        $this->assertSame('polling', PaymentLog::first()->payload['source']);
    }

    public function test_verified_paid_webhook_reaches_the_same_settlement_path(): void
    {
        [$transaction, , , , $session] = $this->paymentFixture('paid');
        config(['services.payment_webhook_secret' => 'webhook_test_secret']);

        $rawBody = json_encode([
            'event_id' => 'evt_webhook_route_1',
            'payment_reference' => $transaction->payment_reference,
            'reference' => $transaction->uuid,
            ...$session,
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $rawBody, 'webhook_test_secret');

        $response = $this->call(
            'POST',
            '/webhooks/payments',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAYMENT_SIGNATURE' => $signature,
            ],
            $rawBody
        );

        $response->assertOk()->assertJsonPath('settlement', 'settled');
        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertDatabaseHas('payment_logs', [
            'transaction_id' => $transaction->id,
            'event_id' => 'evt_webhook_route_1',
            'event_type' => 'payment.paid',
            'status' => 'processed',
        ]);
    }

    public function test_farmer_scan_page_offers_pay_and_cancel_after_qr_review(): void
    {
        [, , $farmer] = $this->paymentFixture('paid');

        $this->actingAs($farmer)
            ->get(route('farmer.scan'))
            ->assertOk()
            ->assertSee('Pay Now')
            ->assertSee('Cancel Order');
    }

    public function test_farmer_can_cancel_a_pending_order_before_checkout_starts(): void
    {
        [$transaction, , $farmer] = $this->paymentFixture('paid');
        $transaction->payment_reference = null;
        $transaction->save();

        $this->actingAs($farmer)
            ->post(route('farmer.checkout.cancel', $transaction->uuid))
            ->assertRedirect(route('farmer.transactions.index'));

        $this->assertSame('cancelled', $transaction->fresh()->status);
        $this->assertDatabaseHas('payment_logs', [
            'transaction_id' => $transaction->id,
            'event_type' => 'farmer.order.cancelled',
            'status' => 'processed',
        ]);
    }

    public function test_proceeding_to_payment_runs_webhook_and_polling_once(): void
    {
        [$transaction, $product, $farmer, $merchant] = $this->paymentFixture('paid');
        $transaction->payment_reference = null;
        $transaction->qr_expires_at = now()->addMinutes(5);
        $transaction->save();

        $this->actingAs($farmer)
            ->post(route('farmer.checkout.payment', $transaction->uuid))
            ->assertRedirect(route('farmer.checkout.payment.return', [
                'uuid' => $transaction->uuid,
            ]));

        // The polling check remains a fallback if the async callback has not run.
        $this->actingAs($farmer)->getJson(
            route('farmer.checkout.payment.status', $transaction->uuid)
        )->assertOk()->assertJsonPath('status', 'paid');

        // Exercise the callback again to cover either callback/polling order.
        (new DeliverLocalPaymentWebhook($transaction->id))->handle(
            app(\App\Services\Payments\LocalPaymentSimulator::class),
            app(PaymentSettlementService::class),
            app(\App\Http\Controllers\Webhooks\PaymentWebhookController::class)
        );

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertEquals(250, (float) $farmer->fresh()->subsidy_balance);
        $this->assertEquals(250, (float) $merchant->fresh()->wallet_balance);
        $this->assertSame(2, PaymentLog::where('transaction_id', $transaction->id)->count());
    }

    private function assertDualPathSettlement(string $firstPath): void
    {
        [$transaction, $product, $farmer, $merchant, $session] = $this->paymentFixture('paid');
        $settlement = app(PaymentSettlementService::class);

        $paths = $firstPath === 'webhook'
            ? [['evt_webhook_1', 'webhook'], ['local-polling:' . $transaction->uuid, 'polling']]
            : [['local-polling:' . $transaction->uuid, 'polling'], ['evt_webhook_1', 'webhook']];

        foreach ($paths as [$eventId, $source]) {
            $result = $settlement->settle(
                $eventId,
                $transaction->payment_reference,
                $transaction->uuid,
                $session,
                $source
            );

            $this->assertContains($result, ['settled', 'ignored_paid']);
        }

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertEquals(250, (float) $farmer->fresh()->subsidy_balance);
        $this->assertEquals(250, (float) $merchant->fresh()->wallet_balance);
        $this->assertSame(2, PaymentLog::where('transaction_id', $transaction->id)->count());
        $this->assertSame(1, PaymentLog::where('transaction_id', $transaction->id)->where('status', 'processed')->count());
        $this->assertSame(1, PaymentLog::where('transaction_id', $transaction->id)->where('status', 'ignored_paid')->count());

        $duplicateEventId = $paths[0][0];
        $this->assertSame('duplicate_event', $settlement->settle(
            $duplicateEventId,
            $transaction->payment_reference,
            $transaction->uuid,
            $session,
            $paths[0][1]
        ));
        $this->assertSame(2, PaymentLog::where('transaction_id', $transaction->id)->count());
    }

    private function paymentFixture(string $paymentStatus): array
    {
        $farmer = User::factory()->create([
            'role' => 'farmer',
            'beneficiary_id' => 'BEN-001',
            'subsidy_balance' => 500,
            'is_verified' => true,
            'is_active' => true,
        ]);
        $merchant = User::factory()->create([
            'role' => 'merchant',
            'wallet_balance' => 0,
            'is_verified' => true,
            'is_active' => true,
        ]);
        $product = Product::create([
            'merchant_id' => $merchant->id,
            'sku' => 'FERT-001',
            'name' => 'Fertilizer',
            'price' => 125,
            'stock' => 10,
            'is_active' => true,
        ]);
        $uuid = (string) Str::uuid();
        $transaction = Transaction::create([
            'uuid' => $uuid,
            'farmer_id' => $farmer->id,
            'merchant_id' => $merchant->id,
            'total_amount' => 250,
            'status' => 'pending',
            'payment_reference' => 'local-payment:' . $uuid,
        ]);
        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => 'Fertilizer',
            'quantity' => 2,
            'unit_price' => 125,
            'subtotal' => 250,
        ]);

        $session = [
            'payment' => [
                'status' => $paymentStatus,
                'currency' => 'PHP',
                'amount' => 25000,
            ],
        ];

        return [$transaction, $product, $farmer, $merchant, $session];
    }
}
