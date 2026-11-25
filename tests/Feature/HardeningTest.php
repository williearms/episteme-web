<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent_on_storefront_and_admin_responses(): void
    {
        foreach (['/', '/login', '/admin/login'] as $url) {
            $this->get($url)
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy');
        }
    }

    public function test_security_headers_are_also_sent_on_error_responses(): void
    {
        $this->get('/definitely-not-a-page')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_hsts_is_not_sent_outside_production(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_the_php_version_is_not_advertised(): void
    {
        $this->get('/')->assertHeaderMissing('X-Powered-By');
    }

    public function test_the_404_page_is_the_branded_one(): void
    {
        $this->get('/definitely-not-a-page')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Back to home');
    }

    public function test_a_forbidden_page_is_branded_too(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden()->assertSee('have access to this page');
    }

    public function test_the_500_page_is_self_contained(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Something went wrong', $html);
        $this->assertStringNotContainsString('@vite', $html);
        $this->assertStringNotContainsString('<link', $html);
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('register'), [])->assertSessionHasErrors();
        }

        $this->post(route('register'), [])->assertStatus(429);
    }

    public function test_exhausting_the_login_limit_does_not_block_password_reset_requests(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login'), ['email' => "nobody{$i}@example.com", 'password' => 'wrong']);
        }
        $this->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'wrong'])->assertStatus(429);

        // Before the named limiters, this shared the login bucket and was already throttled.
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])->assertStatus(302);
    }

    public function test_cart_activity_does_not_eat_into_the_checkout_limit(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 0]);

        for ($i = 0; $i < 15; $i++) {
            $this->actingAs($user)->post(route('cart.items.store'), ['product_id' => $product->id])->assertStatus(302);
        }

        $this->actingAs($user)->post(route('checkout.store'))->assertStatus(302);
    }

    public function test_checkout_is_limited_per_user_and_does_not_block_the_cart(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('checkout.store'));
        }

        $this->actingAs($user)->post(route('checkout.store'))->assertStatus(429);
        $this->actingAs($user)->get(route('cart.index'))->assertOk();
        $this->actingAs($other)->post(route('checkout.store'))->assertStatus(302); // other users unaffected
    }

    public function test_quiz_submissions_are_rate_limited(): void
    {
        $this->assertTrue(
            collect(app('router')->getRoutes()->getByName('learning.quizzes.attempts.store')->gatherMiddleware())->contains('throttle:quiz')
        );
    }
}
