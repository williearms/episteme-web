<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Payments\PaymentGateway;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/** The E2E suite adds a database wipe and a gateway override; neither may ever act outside it. */
class E2eSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reset_command_refuses_to_run_outside_the_e2e_environment(): void
    {
        $user = User::factory()->create();

        $this->artisan('e2e:reset')->assertFailed();

        $this->assertModelExists($user);
    }

    public function test_the_reset_command_refuses_a_database_that_is_not_e2e_even_in_the_e2e_environment(): void
    {
        $user = User::factory()->create();
        $this->app['env'] = 'e2e'; // environment says e2e, but we're connected to "testing"

        $this->artisan('e2e:reset')->assertFailed();

        $this->assertModelExists($user);
    }

    public function test_the_e2e_seeder_refuses_to_run_elsewhere(): void
    {
        $this->expectException(RuntimeException::class);

        (new E2eSeeder)->run();
    }

    public function test_the_gateway_url_override_is_used_outside_production(): void
    {
        config(['payment.midtrans.snap_url' => 'http://127.0.0.1:8099/snap/v1']);

        $this->assertSame('http://127.0.0.1:8099/snap/v1/transactions', $this->chargeUrl());
    }

    public function test_the_gateway_url_override_is_ignored_in_production(): void
    {
        config(['payment.midtrans.snap_url' => 'http://attacker.example/snap/v1']);
        $this->app['env'] = 'production';

        $this->assertSame('https://app.sandbox.midtrans.com/snap/v1/transactions', $this->chargeUrl());
    }

    private function chargeUrl(): string
    {
        Http::fake(['*' => Http::response(['redirect_url' => 'https://example.com/pay'], 201)]);

        $order = Order::factory()->create();
        $payment = $order->payments()->create(['provider' => 'midtrans', 'provider_payment_id' => 'X-P1', 'status' => 'pending', 'amount' => $order->total, 'currency' => 'IDR']);

        app(PaymentGateway::class)->charge($order, $payment);

        return Http::recorded()->first()[0]->url();
    }
}
