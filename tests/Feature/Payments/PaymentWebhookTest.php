<?php

namespace Tests\Feature\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function signedPayload(array $overrides = []): array
    {
        $payload = array_merge([
            'order_id' => 'EP-20260928-000001-P1',
            'status_code' => '200',
            'gross_amount' => '150000.00',
            'transaction_status' => 'settlement',
        ], $overrides);

        $serverKey = config('payment.midtrans.server_key');
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        return $payload;
    }

    private function createPendingPayment(array $orderOverrides = [], array $paymentOverrides = []): array
    {
        $order = Order::factory()->create(array_merge([
            'order_number' => 'EP-20260928-000001',
            'status' => OrderStatus::Pending,
            'total' => 150000,
        ], $orderOverrides));

        $payment = $order->payments()->create(array_merge([
            'provider' => 'midtrans',
            'provider_payment_id' => 'EP-20260928-000001-P1',
            'status' => PaymentStatus::Pending,
            'amount' => 150000,
            'currency' => 'IDR',
        ], $paymentOverrides));

        return [$order, $payment];
    }

    public function test_a_valid_webhook_marks_the_payment_and_order_as_paid(): void
    {
        [$order] = $this->createPendingPayment();

        $response = $this->postJson(route('payments.webhook.midtrans'), $this->signedPayload());

        $response->assertOk();
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payments()->latest()->first()->status);
    }

    public function test_an_invalid_signature_is_rejected_and_does_not_change_status(): void
    {
        [$order] = $this->createPendingPayment();

        $payload = $this->signedPayload();
        $payload['signature_key'] = 'tampered-signature';

        $response = $this->postJson(route('payments.webhook.midtrans'), $payload);

        $response->assertStatus(403);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_an_amount_mismatch_is_rejected(): void
    {
        [$order] = $this->createPendingPayment();

        $response = $this->postJson(route('payments.webhook.midtrans'), $this->signedPayload(['gross_amount' => '999000.00']));

        $response->assertStatus(422);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_an_unknown_order_id_returns_not_found(): void
    {
        $response = $this->postJson(route('payments.webhook.midtrans'), $this->signedPayload(['order_id' => 'EP-does-not-exist-P1']));

        $response->assertStatus(404);
    }

    public function test_a_duplicate_webhook_delivery_is_idempotent(): void
    {
        [$order] = $this->createPendingPayment();
        $payload = $this->signedPayload();

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();
        $firstPaidAt = $order->fresh()->payments()->latest()->first()->paid_at;

        $this->travel(5)->minutes();
        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $payment = $order->fresh()->payments()->latest()->first();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertTrue($firstPaidAt->equalTo($payment->paid_at));
    }

    public function test_an_expired_transaction_status_marks_the_order_expired(): void
    {
        [$order] = $this->createPendingPayment();

        $this->postJson(route('payments.webhook.midtrans'), $this->signedPayload(['transaction_status' => 'expire']))
            ->assertOk();

        $this->assertSame(OrderStatus::Expired, $order->fresh()->status);
    }

    public function test_a_failed_transaction_status_leaves_the_order_pending_so_it_can_be_retried(): void
    {
        [$order] = $this->createPendingPayment();

        $this->postJson(route('payments.webhook.midtrans'), $this->signedPayload(['transaction_status' => 'deny']))
            ->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payments()->latest()->first()->status);
    }
}
