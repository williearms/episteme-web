<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_asked_to_log_in(): void
    {
        $product = Product::factory()->create(['price' => 0]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Log In to')
            ->assertDontSee('name="product_id"', false);
    }

    public function test_a_signed_in_user_with_an_empty_cart_sees_the_add_button(): void
    {
        $product = Product::factory()->create(['price' => 0]);

        $this->actingAs(User::factory()->create())->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('name="product_id"', false)
            ->assertDontSee('In your cart');
    }

    public function test_after_adding_to_the_cart_the_button_becomes_a_link_to_the_cart(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id]);

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('In your cart')
            ->assertSee(route('cart.index'), false)
            ->assertDontSee('name="product_id"', false);
    }

    public function test_the_in_cart_state_is_per_user(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $inCart = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($inCart)->post(route('cart.items.store'), ['product_id' => $product->id]);

        $this->actingAs($other)->get(route('products.show', $product))
            ->assertDontSee('In your cart')
            ->assertSee('name="product_id"', false);
    }

    public function test_removing_it_from_the_cart_brings_the_add_button_back(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id]);
        $this->actingAs($user)->delete(route('cart.items.destroy', $product->id));

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertDontSee('In your cart')
            ->assertSee('name="product_id"', false);
    }

    public function test_an_owned_product_links_to_learning_even_if_it_is_still_in_the_cart(): void
    {
        $product = Product::factory()->create(['price' => 0]);
        $user = User::factory()->create();
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 0]);
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertSee('Go to My Learning')
            ->assertDontSee('In your cart');
    }
}
