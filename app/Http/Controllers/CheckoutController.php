<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Payments\PaymentAttemptStarter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Cart lines that can still be bought: published, and not already owned by the user. */
    private function purchasableItems(Request $request)
    {
        $user = $request->user();
        $cart = $user->cartOrCreate();
        $cart->load('items.product');

        $ownedProductIds = $user->enrollments()->pluck('product_id');

        return $cart->items->filter(
            fn ($item) => $item->isPurchasable() && ! $ownedProductIds->contains($item->product_id)
        );
    }

    /** Sum at the products' current prices — never the cart's cached unit_price. */
    private function total($items): int
    {
        return (int) $items->sum(fn ($item) => $item->product->price * $item->quantity);
    }

    public function show(Request $request): View|RedirectResponse
    {
        $items = $this->purchasableItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        if ($this->total($items) > Order::MAX_TOTAL) {
            return redirect()->route('cart.index')->with('error', 'This order is too large to process in one go. Please split it into smaller orders.');
        }

        return view('checkout.show', [
            'items' => $items,
            'subtotal' => $this->total($items),
        ]);
    }

    public function store(Request $request, PaymentAttemptStarter $paymentAttemptStarter): RedirectResponse
    {
        $user = $request->user();
        $cart = $user->cartOrCreate();

        // Re-validate at checkout time, not add-to-cart time: a product may
        // have gone unpublished (or been bought another way) since it was added.
        $items = $this->purchasableItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        if ($this->total($items) > Order::MAX_TOTAL) {
            return redirect()->route('cart.index')->with('error', 'This order is too large to process in one go. Please split it into smaller orders.');
        }

        $order = DB::transaction(function () use ($user, $cart, $items) {
            // Lock this cart's rows first. A double-clicked "Place order" (or two tabs) sends two
            // requests that both read the same cart; the second now waits here, finds the rows
            // already consumed, and bails instead of creating a duplicate order.
            $stillThere = $cart->items()->whereIn('id', $items->pluck('id'))->lockForUpdate()->count();

            if ($stillThere !== $items->count()) {
                return null;
            }

            $subtotal = 0;
            $rows = [];

            foreach ($items as $item) {
                // Always the product's CURRENT price, never the cart's cached
                // unit_price — price may have changed since it was added.
                $product = $item->product;
                $lineSubtotal = $product->price * $item->quantity;
                $subtotal += $lineSubtotal;

                $rows[] = [
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'product_type' => $product->product_type->value,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'discount' => 0, // no coupon/discount engine in Phase 5
                'total' => $subtotal,
                'currency' => 'IDR',
            ]);

            foreach ($rows as $row) {
                $order->items()->create($row);
            }

            if ($order->total === 0) {
                // Free products need no gateway round-trip — settle immediately.
                $order->update(['status' => OrderStatus::Paid]);
                $order->payments()->create([
                    'provider' => 'free',
                    'status' => PaymentStatus::Paid,
                    'amount' => 0,
                    'currency' => 'IDR',
                    'paid_at' => now(),
                ]);
                $order->grantEnrollments();
            }

            $cart->items()->whereIn('id', $items->pluck('id'))->delete();

            return $order;
        });

        if ($order === null) {
            return redirect()->route('cart.index')->with('error', 'Your cart changed while we were placing the order. Please review it and try again.');
        }

        if ($order->total === 0) {
            return redirect()->route('orders.show', $order)->with('status', 'Order placed — no payment required.');
        }

        $result = $paymentAttemptStarter->start($order);

        if (! $result['redirect_url']) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Your order was placed, but we could not start the payment. Please try again.');
        }

        return redirect()->away($result['redirect_url']);
    }
}
