<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_when_viewing_the_cart(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
    }

    public function test_a_guest_cannot_add_a_product_to_a_cart(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.items.store'), ['product_id' => $product->id])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_an_authenticated_user_can_add_a_published_product_to_their_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 250000]);

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 250000,
        ]);
    }

    public function test_adding_the_same_product_twice_increments_quantity_instead_of_duplicating_the_row(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id]);
        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_a_draft_product_cannot_be_added_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->draft()->create();

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_an_archived_product_cannot_be_added_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->archived()->create();

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_a_nonexistent_product_id_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => 999999])
            ->assertSessionHasErrors('product_id');
    }

    public function test_a_user_can_update_the_quantity_of_a_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->actingAs($user)->patch(route('cart.items.update', $product->id), ['quantity' => 3])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_a_user_can_remove_an_item_from_their_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->actingAs($user)->delete(route('cart.items.destroy', $product->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_a_user_cannot_modify_a_cart_item_that_belongs_to_another_users_cart(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create();
        $owner->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->actingAs($intruder)->patch(route('cart.items.update', $product->id), ['quantity' => 5])
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_the_cart_page_shows_the_subtotal(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Cart Subtotal Course', 'price' => 100000]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => $product->price]);

        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Cart Subtotal Course')
            ->assertSee('Rp200.000');
    }

    public function test_an_empty_cart_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty');
    }
}
