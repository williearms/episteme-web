<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_a_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('account.dashboard'));
    }

    public function test_authentication_fails_with_an_invalid_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authentication_fails_for_a_nonexistent_email_with_the_same_generic_message(): void
    {
        $existing = User::factory()->create(['password' => bcrypt('password123')]);
        $genericMessage = 'These credentials do not match our records.';

        // Same generic message for a wrong password on a real account and for
        // an email that isn't registered at all — neither response reveals
        // whether the account exists (account enumeration protection).
        $this->from(route('login'))->post(route('login'), [
            'email' => $existing->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => $genericMessage]);

        $this->from(route('login'))->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => $genericMessage]);

        $this->assertGuest();
    }

    public function test_login_regenerates_the_session_id_to_prevent_fixation(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->withSession([])->get(route('login'));
        $idBeforeLogin = $this->app['session']->getId();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $idAfterLogin = $this->app['session']->getId();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($idBeforeLogin, $idAfterLogin);
    }

    public function test_a_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_a_guest_cannot_log_out(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }
}
