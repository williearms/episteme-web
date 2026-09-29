<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

/**
 * Creates a new Payment attempt for an order and hands it to the gateway.
 * Shared by checkout (first attempt) and the "retry payment" action (every
 * subsequent one) — a failed/expired attempt is never reused or duplicated
 * in place, a fresh row is always created so the full attempt history survives.
 */
class PaymentAttemptStarter
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /** @return array{payment: Payment, redirect_url: ?string} */
    public function start(Order $order): array
    {
        $payment = $order->payments()->create([
            'provider' => config('payment.provider'),
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        $payment->update(['provider_payment_id' => $order->order_number.'-P'.$payment->id]);

        try {
            $result = $this->gateway->charge($order, $payment);
        } catch (\Throwable $e) {
            $payment->update(['status' => PaymentStatus::Failed]);

            report($e);

            return ['payment' => $payment, 'redirect_url' => null];
        }

        $payment->update(['raw_response' => $result['raw_response']]);

        return ['payment' => $payment, 'redirect_url' => $result['redirect_url']];
    }
}
