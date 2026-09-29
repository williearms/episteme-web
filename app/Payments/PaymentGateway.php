<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

/**
 * Provider abstraction so Episteme is not hard-wired to one payment gateway.
 * Only Midtrans is implemented (see MidtransGateway); swapping providers means
 * adding a new class here and one new case in AppServiceProvider's binding —
 * no changes to CheckoutController or PaymentWebhookController.
 */
interface PaymentGateway
{
    /**
     * Start a transaction with the provider for this specific payment attempt.
     * $payment->provider_payment_id is already set (by PaymentAttemptStarter) to a
     * reference unique per attempt — implementations must send that value as the
     * provider's own transaction/order identifier, since it's what the webhook
     * handler uses to look the attempt back up.
     *
     * @return array{redirect_url: ?string, raw_response: array}
     */
    public function charge(Order $order, Payment $payment): array;

    /** Verify a webhook payload actually came from the provider (signature/authenticity check). */
    public function verifyWebhookSignature(array $payload): bool;

    /** Map the provider's own status vocabulary onto Episteme's internal PaymentStatus. */
    public function mapStatus(string $providerStatus, ?string $context = null): PaymentStatus;
}
