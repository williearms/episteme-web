<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Payments\PaymentAttemptStarter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'payments' => fn ($query) => $query->latest()]);

        return view('orders.show', [
            'order' => $order,
            'latestPayment' => $order->payments->first(),
        ]);
    }

    public function retryPayment(Request $request, Order $order, PaymentAttemptStarter $paymentAttemptStarter): RedirectResponse
    {
        $this->authorize('view', $order);

        if ($order->status !== OrderStatus::Pending) {
            return redirect()->route('orders.show', $order)->with('error', 'This order can no longer be paid.');
        }

        $result = $paymentAttemptStarter->start($order);

        if (! $result['redirect_url']) {
            return redirect()->route('orders.show', $order)->with('error', 'We could not start the payment. Please try again.');
        }

        return redirect()->away($result['redirect_url']);
    }
}
