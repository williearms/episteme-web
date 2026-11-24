<?php

namespace Tests\Feature\Learning;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function webhookPayload(string $reference, int $amount): array
    {
        $payload = [
            'order_id' => $reference,
            'status_code' => '200',
            'gross_amount' => $amount.'.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('payment.midtrans.server_key'));

        return $payload;
    }

    private function pendingOrderFor(User $user, Product $product): Order
    {
        $order = Order::factory()->for($user)->create([
            'order_number' => 'EP-20261124-000001',
            'status' => OrderStatus::Pending,
            'subtotal' => $product->price,
            'total' => $product->price,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_type' => $product->product_type->value,
            'quantity' => 1,
            'unit_price' => $product->price,
            'subtotal' => $product->price,
        ]);
        $order->payments()->create([
            'provider' => 'midtrans',
            'provider_payment_id' => $order->order_number.'-P1',
            'status' => PaymentStatus::Pending,
            'amount' => $product->price,
            'currency' => 'IDR',
        ]);

        return $order;
    }

    public function test_a_paid_webhook_enrolls_the_buyer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 200000]);
        $order = $this->pendingOrderFor($user, $product);

        $this->postJson(route('payments.webhook.midtrans'), $this->webhookPayload($order->order_number.'-P1', 200000))->assertOk();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_placing_a_paid_order_does_not_enroll_until_payment_is_confirmed(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response(['token' => 't', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/t'], 201),
        ]);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 200000]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 200000]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_a_failed_payment_does_not_enroll_the_buyer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 200000]);
        $order = $this->pendingOrderFor($user, $product);

        $payload = $this->webhookPayload($order->order_number.'-P1', 200000);
        $payload['transaction_status'] = 'deny';
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('payment.midtrans.server_key'));

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_a_duplicate_paid_webhook_never_creates_a_second_enrollment(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 200000]);
        $order = $this->pendingOrderFor($user, $product);
        $payload = $this->webhookPayload($order->order_number.'-P1', 200000);

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();
        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_granting_enrollments_twice_for_the_same_order_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        $order = $this->pendingOrderFor($user, $product);

        $order->grantEnrollments();
        $order->grantEnrollments();

        $this->assertSame(1, Enrollment::where('user_id', $user->id)->count());
    }

    public function test_a_free_checkout_enrolls_the_buyer_immediately(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 0]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 0]);

        $this->actingAs($user)->post(route('checkout.store'));

        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_an_owned_product_cannot_be_added_to_the_cart_again(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_ignores_a_cart_item_the_user_already_owns(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 0]);
        $user->cartOrCreate()->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 0]);
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        $this->actingAs($user)->post(route('checkout.store'))->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_the_product_page_offers_learning_instead_of_buying_when_owned(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Go to My Learning');
    }
}
