<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->user()->cartOrCreate();
        $cart->load('items.product');

        return view('cart.index', [
            'cart' => $cart,
            'ownedProductIds' => $request->user()->enrollments()->pluck('product_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        // Server determines the product and its price — the client's own
        // opinion of either is never trusted, even indirectly.
        $product = Product::query()->find($validated['product_id']);

        if (! $product || ! $product->isPurchasable()) {
            return back()->with('error', 'This product is not currently available for purchase.');
        }

        if ($request->user()->ownsProduct($product->id)) {
            return back()->with('error', 'You already own this product.');
        }

        $cart = $request->user()->cartOrCreate();
        $quantity = $validated['quantity'] ?? 1;

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            // Adding an already-cart'd product again increments quantity rather
            // than creating a duplicate row (cart_items has a unique constraint
            // on [cart_id, product_id] to guarantee this at the DB level too).
            $item->update([
                'quantity' => $item->quantity + $quantity,
                'unit_price' => $product->price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);
        }

        return back()->with('status', 'Added to cart.');
    }

    /**
     * Scoped through the current user's own cart rather than an exposed
     * cart_item id — mirrors the Phase 4 pattern of never accepting a route
     * param that identifies someone else's resource, so there's no IDOR surface.
     */
    public function update(Request $request, int $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->user()->cartOrCreate();
        $item = $cart->items()->where('product_id', $product)->firstOrFail();
        $item->update(['quantity' => $validated['quantity']]);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, int $product): RedirectResponse
    {
        $cart = $request->user()->cartOrCreate();
        $cart->items()->where('product_id', $product)->delete();

        return back()->with('status', 'Removed from cart.');
    }
}
