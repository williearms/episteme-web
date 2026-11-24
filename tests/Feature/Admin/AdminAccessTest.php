<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/admin',
        '/admin/products',
        '/admin/products/create',
        '/admin/categories',
        '/admin/instructors',
        '/admin/orders',
        '/admin/enrollments',
        '/admin/course-modules',
        '/admin/course-modules/create',
        '/admin/users',
    ];

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }

    public function test_a_regular_user_cannot_open_any_admin_page(): void
    {
        $user = User::factory()->create();

        foreach (self::PAGES as $page) {
            $this->actingAs($user)->get($page)->assertForbidden();
        }
    }

    public function test_an_admin_can_open_every_admin_page(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (self::PAGES as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_users_are_not_admins_by_default(): void
    {
        $this->assertFalse(User::factory()->create()->fresh()->is_admin);
    }

    public function test_registering_cannot_grant_admin_through_a_crafted_request(): void
    {
        $this->post(route('register'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'a-Strong-pass-123',
            'password_confirmation' => 'a-Strong-pass-123',
            'is_admin' => 1,
        ]);

        $this->assertFalse(User::where('email', 'sneaky@example.com')->firstOrFail()->is_admin);
    }

    public function test_updating_a_profile_cannot_grant_admin_through_a_crafted_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('account.profile.update'), [
            'name' => 'Still Regular',
            'email' => $user->email,
            'is_admin' => 1,
        ]);

        $this->assertFalse($user->fresh()->is_admin);
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_the_make_admin_command_promotes_an_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);

        $this->artisan('episteme:make-admin', ['email' => 'staff@example.com'])->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_the_make_admin_command_fails_for_an_unknown_email(): void
    {
        $this->artisan('episteme:make-admin', ['email' => 'nobody@example.com'])->assertFailed();
    }

    public function test_an_admin_can_view_any_order_in_the_panel_but_not_on_the_storefront(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->for(User::factory())->create(['order_number' => 'EP-20261125-000001']);

        // Filament builds record URLs from the model's route key, which for orders is the order number.
        $this->actingAs($admin)->get('/admin/orders/'.$order->getRouteKey())->assertOk()->assertSee('EP-20261125-000001');

        // The storefront rule from Phase 5 is unchanged: only the owner sees an order there.
        $this->actingAs($admin)->get(route('orders.show', $order))->assertForbidden();
    }
}
