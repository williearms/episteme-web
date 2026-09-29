<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $payload = $request->all();

        // Never trust the payload's own claimed status/amount before this passes.
        if (! $gateway->verifyWebhookSignature($payload)) {
            Log::warning('Rejected payment webhook: invalid signature.');

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        // provider_payment_id was generated per-attempt at charge time and is
        // exactly what we sent the provider as its own order/transaction id,
        // so it's an unambiguous lookup even across multiple retry attempts
        // for the same Order.
        $payment = Payment::where('provider_payment_id', $payload['order_id'] ?? null)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $reportedAmount = (int) round((float) ($payload['gross_amount'] ?? -1));

        if ($reportedAmount !== $payment->amount) {
            Log::warning('Rejected payment webhook: amount mismatch.', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount,
                'reported' => $reportedAmount,
            ]);

            return response()->json(['message' => 'Amount mismatch.'], 422);
        }

        $status = $gateway->mapStatus($payload['transaction_status'] ?? '', $payload['fraud_status'] ?? null);

        DB::transaction(function () use ($payment, $status, $payload) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            // Idempotency: a terminal status never gets overwritten, so a
            // redelivered (duplicate) webhook is a safe no-op.
            if ($payment->status->isTerminal()) {
                return;
            }

            $payment->update([
                'status' => $status,
                'raw_response' => $payload,
                'paid_at' => $status === PaymentStatus::Paid ? now() : $payment->paid_at,
            ]);

            $order = $payment->order;

            $order->update(['status' => match ($status) {
                PaymentStatus::Paid => OrderStatus::Paid,
                PaymentStatus::Expired => OrderStatus::Expired,
                PaymentStatus::Cancelled => OrderStatus::Cancelled,
                // A failed/still-pending attempt doesn't change the order —
                // the user can retry without the order itself being touched.
                PaymentStatus::Pending, PaymentStatus::Failed => $order->status,
            }]);
        });

        return response()->json(['message' => 'OK']);
    }
}
