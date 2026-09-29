<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_when_viewing_an_order(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.show', $order))->assertRedirect(route('login'));
    }

    public function test_a_user_can_view_their_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['order_number' => 'EP-20260928-000001']);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('EP-20260928-000001');
    }

    public function test_a_user_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($intruder)->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_my_orders_page_lists_only_the_authenticated_users_orders(): void
    {
        $user = User::factory()->create();
        $someoneElse = User::factory()->create();
        Order::factory()->for($user)->create(['order_number' => 'EP-20260928-000002']);
        Order::factory()->for($someoneElse)->create(['order_number' => 'EP-20260928-000003']);

        $response = $this->actingAs($user)->get(route('account.orders.index'));

        $response->assertOk();
        $response->assertSee('EP-20260928-000002');
        $response->assertDontSee('EP-20260928-000003');
    }

    public function test_no_orders_yet_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('haven');
    }

    public function test_retrying_payment_on_a_pending_order_creates_a_new_payment_attempt(): void
    {
        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'retry-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/retry-token',
            ], 201),
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => OrderStatus::Pending, 'total' => 100000]);
        $order->payments()->create([
            'provider' => 'midtrans',
            'provider_payment_id' => $order->order_number.'-P1',
            'status' => PaymentStatus::Failed,
            'amount' => 100000,
            'currency' => 'IDR',
        ]);

        $response = $this->actingAs($user)->post(route('orders.retry-payment', $order));

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v3/redirection/retry-token');
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Pending->value]);
    }

    public function test_retrying_payment_is_blocked_once_an_order_is_already_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => OrderStatus::Paid]);

        $this->actingAs($user)->post(route('orders.retry-payment', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_user_cannot_retry_payment_on_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = Order::factory()->for($owner)->create(['status' => OrderStatus::Pending]);

        $this->actingAs($intruder)->post(route('orders.retry-payment', $order))
            ->assertForbidden();
    }
}
