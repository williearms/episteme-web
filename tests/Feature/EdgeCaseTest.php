<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCartOf(Product $product, int $quantity = 1): User
    {
        $user = User::factory()->create();
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->price]);

        return $user;
    }

    public function test_an_absurd_quantity_is_a_validation_error_not_a_server_error(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        $user = $this->userWithCartOf($product);

        foreach ([99999999999, 5000000, CartItem::MAX_QUANTITY + 1, 0, -3] as $quantity) {
            $this->actingAs($user)->patch(route('cart.items.update', $product->id), ['quantity' => $quantity])
                ->assertSessionHasErrors('quantity');
        }

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_adding_with_an_absurd_quantity_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 99999999999])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_repeated_adds_stop_at_the_per_line_maximum(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        foreach (range(1, 4) as $_) {
            $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => CartItem::MAX_QUANTITY]);
        }

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => CartItem::MAX_QUANTITY]);
    }

    public function test_an_order_over_the_maximum_total_is_refused_instead_of_overflowing(): void
    {
        $product = Product::factory()->create(['price' => 100_000_000]);
        $user = $this->userWithCartOf($product, CartItem::MAX_QUANTITY);
        // 10 x Rp100.000.000 = Rp1.000.000.000 is allowed; add enough lines to pass the cap.
        foreach (Product::factory()->count(2)->create(['price' => 100_000_000]) as $extra) {
            $user->cart->items()->create(['product_id' => $extra->id, 'quantity' => CartItem::MAX_QUANTITY, 'unit_price' => $extra->price]);
        }

        $this->actingAs($user)->post(route('checkout.store'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 3); // the cart is left untouched
    }

    public function test_the_checkout_page_also_refuses_an_oversized_order(): void
    {
        $user = User::factory()->create();
        foreach (Product::factory()->count(3)->create(['price' => 100_000_000]) as $product) {
            $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => CartItem::MAX_QUANTITY, 'unit_price' => $product->price]);
        }

        $this->actingAs($user)->get(route('checkout.show'))->assertRedirect(route('cart.index'));
    }

    public function test_a_just_under_the_cap_order_still_works(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $user = $this->userWithCartOf($product, CartItem::MAX_QUANTITY);

        $this->actingAs($user)->post(route('checkout.store'))->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_submitting_checkout_twice_creates_only_one_order(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $user = $this->userWithCartOf($product);

        $this->actingAs($user)->post(route('checkout.store'))->assertRedirect();
        // The "double click": the cart was already consumed by the first request.
        $this->actingAs($user)->post(route('checkout.store'))->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_the_application_timezone_is_indonesian_time_by_default(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', now()->timezoneName);
    }

    public function test_order_numbers_use_the_local_date(): void
    {
        $this->assertStringStartsWith('EP-'.now('Asia/Jakarta')->format('Ymd').'-', Order::generateOrderNumber());
    }
}
