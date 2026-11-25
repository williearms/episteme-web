<?php

namespace Tests\Feature\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * If MIDTRANS_SERVER_KEY is missing, the signature is sha512(order + status + amount + ""),
 * which anyone can compute — so a missing key must mean "reject everything", never "accept".
 */
class WebhookWithoutKeyTest extends TestCase
{
    use RefreshDatabase;

    private function pendingOrder(): Order
    {
        $order = Order::factory()->create(['order_number' => 'EP-20261125-000009', 'status' => OrderStatus::Pending, 'total' => 150000]);
        $order->payments()->create([
            'provider' => 'midtrans',
            'provider_payment_id' => 'EP-20261125-000009-P1',
            'status' => PaymentStatus::Pending,
            'amount' => 150000,
            'currency' => 'IDR',
        ]);

        return $order;
    }

    private function forgedPayload(string $keyUsed): array
    {
        $payload = ['order_id' => 'EP-20261125-000009-P1', 'status_code' => '200', 'gross_amount' => '150000.00', 'transaction_status' => 'settlement'];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$keyUsed);

        return $payload;
    }

    public function test_a_webhook_forged_with_an_empty_key_is_rejected_when_no_key_is_configured(): void
    {
        config(['payment.midtrans.server_key' => '']);
        $order = $this->pendingOrder();

        $this->postJson(route('payments.webhook.midtrans'), $this->forgedPayload(''))->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_the_same_holds_when_the_key_is_null(): void
    {
        config(['payment.midtrans.server_key' => null]);
        $order = $this->pendingOrder();

        $this->postJson(route('payments.webhook.midtrans'), $this->forgedPayload(''))->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_malformed_payload_types_are_rejected_cleanly_not_with_a_server_error(): void
    {
        config(['payment.midtrans.server_key' => 'real-key']);
        $this->pendingOrder();

        foreach ([
            ['order_id' => ['x'], 'status_code' => '200', 'gross_amount' => '1', 'signature_key' => 'abc'],
            ['order_id' => 'x', 'status_code' => '200', 'gross_amount' => '1', 'signature_key' => ['abc']],
            ['order_id' => 'x', 'status_code' => ['200'], 'gross_amount' => ['1'], 'signature_key' => 'abc'],
            [],
        ] as $payload) {
            $this->postJson(route('payments.webhook.midtrans'), $payload)->assertForbidden();
        }
    }

    public function test_a_properly_signed_webhook_still_works_when_a_key_is_configured(): void
    {
        config(['payment.midtrans.server_key' => 'real-key']);
        $order = $this->pendingOrder();

        $this->postJson(route('payments.webhook.midtrans'), $this->forgedPayload('real-key'))->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }
}
