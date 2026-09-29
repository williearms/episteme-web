<?php

namespace Tests\Feature\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSuccessfulMidtransCharge(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'fake-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/fake-snap-token',
            ], 201),
        ]);
    }

    public function test_a_guest_is_redirected_to_login_from_checkout(): void
    {
        $this->get(route('checkout.show'))->assertRedirect(route('login'));
    }

    public function test_checkout_redirects_to_cart_when_the_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('checkout.show'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_placing_an_order_creates_an_order_with_a_snapshot_of_product_details(): void
    {
        $this->fakeSuccessfulMidtransCharge();

        $user = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Snapshot Course', 'price' => 300000]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal' => 300000,
            'total' => 300000,
            'status' => OrderStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_title' => 'Snapshot Course',
            'unit_price' => 300000,
            'subtotal' => 300000,
        ]);
    }

    public function test_checkout_uses_the_products_current_price_not_the_stale_cart_price(): void
    {
        $this->fakeSuccessfulMidtransCharge();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        // Cart item was priced at add-to-cart time...
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100000]);
        // ...but the product's price has since changed.
        $product->update(['price' => 175000]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'unit_price' => 175000, 'subtotal' => 175000]);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 175000]);
    }

    public function test_checkout_creates_a_pending_payment_and_redirects_to_the_gateway(): void
    {
        $this->fakeSuccessfulMidtransCharge();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $response = $this->actingAs($user)->post(route('checkout.store'));

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v3/redirection/fake-snap-token');
        $this->assertDatabaseHas('payments', [
            'provider' => 'midtrans',
            'status' => PaymentStatus::Pending->value,
            'amount' => 50000,
        ]);
    }

    public function test_a_free_product_checkout_skips_payment_and_is_marked_paid_immediately(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 0]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 0]);

        $this->actingAs($user)->post(route('checkout.store'));

        $order = $user->orders()->first();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'free',
            'status' => PaymentStatus::Paid->value,
            'amount' => 0,
        ]);
        Http::assertNothingSent();
    }

    public function test_checkout_clears_purchased_items_from_the_cart(): void
    {
        $this->fakeSuccessfulMidtransCharge();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_excludes_items_that_became_unavailable_since_being_added_to_cart(): void
    {
        $this->fakeSuccessfulMidtransCharge();

        $user = User::factory()->create();
        $available = Product::factory()->create(['price' => 100000]);
        $nowDraft = Product::factory()->create(['price' => 50000]);
        $user->cartOrCreate()->items()->create(['product_id' => $available->id, 'quantity' => 1, 'unit_price' => 100000]);
        $user->cartOrCreate()->items()->create(['product_id' => $nowDraft->id, 'quantity' => 1, 'unit_price' => 50000]);
        $nowDraft->update(['status' => ProductStatus::Draft]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 100000]);
        $this->assertDatabaseCount('order_items', 1);
        // The now-unavailable item is left in the cart rather than silently dropped.
        $this->assertDatabaseHas('cart_items', ['product_id' => $nowDraft->id]);
    }

    public function test_checkout_marks_the_payment_failed_when_the_gateway_request_fails(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['error_messages' => ['Unauthorized']], 401),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 80000]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $response = $this->actingAs($user)->post(route('checkout.store'));

        $order = $user->orders()->first();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Failed->value]);
    }
}
