<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Midtrans Snap integration (https://docs.midtrans.com/reference/snap-quick-start).
 * Sandbox and production share the same request shape and only differ by base URL and
 * server key — see config/payment.php / MIDTRANS_IS_PRODUCTION.
 */
class MidtransGateway implements PaymentGateway
{
    public function __construct(
        private readonly ?string $serverKey,
        private readonly bool $isProduction = false,
    ) {}

    public function charge(Order $order, Payment $payment): array
    {
        $response = Http::withBasicAuth($this->serverKey ?? '', '')
            ->acceptJson()
            ->post($this->baseUrl().'/transactions', [
                'transaction_details' => [
                    // Must be unique PER ATTEMPT, not per order — Midtrans rejects a
                    // reused order_id, and a retry after a failed/expired attempt needs
                    // its own. provider_payment_id (order_number + payment id) already
                    // guarantees this, and is exactly what the webhook echoes back in
                    // its own `order_id` field, so it doubles as our lookup key.
                    'order_id' => $payment->provider_payment_id,
                    'gross_amount' => $order->total,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'item_details' => $order->items->map(fn ($item) => [
                    'id' => (string) ($item->product_id ?? $item->id),
                    'name' => Str::limit($item->product_title, 50, ''),
                    'price' => $item->unit_price,
                    'quantity' => $item->quantity,
                ])->all(),
                'callbacks' => [
                    'finish' => route('orders.show', $order),
                ],
            ])
            ->throw();

        $data = $response->json() ?? [];

        return [
            'redirect_url' => $data['redirect_url'] ?? null,
            'raw_response' => $data,
        ];
    }

    public function verifyWebhookSignature(array $payload): bool
    {
        if (! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $expected = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].($this->serverKey ?? '')
        );

        return hash_equals($expected, $payload['signature_key']);
    }

    /** @param  ?string  $context  Midtrans's `fraud_status`, relevant only for the `capture` transaction_status. */
    public function mapStatus(string $providerStatus, ?string $context = null): PaymentStatus
    {
        return match ($providerStatus) {
            'capture' => $context === 'challenge' ? PaymentStatus::Pending : PaymentStatus::Paid,
            'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'deny', 'failure' => PaymentStatus::Failed,
            'cancel' => PaymentStatus::Cancelled,
            'expire' => PaymentStatus::Expired,
            default => PaymentStatus::Pending,
        };
    }

    private function baseUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }
}
