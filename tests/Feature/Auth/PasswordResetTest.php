<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_a_reset_link_request_can_be_initiated_for_a_registered_email(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_a_reset_link_request_for_an_unregistered_email_shows_the_same_generic_status(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        // No "we don't recognize that email" message — avoids account enumeration.
        $response->assertSessionHas('status', 'If an account exists for that email address, we have emailed a password reset link.');
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_a_user_can_reset_their_password_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, function (ResetPassword $notification) use ($user) {
            $response = $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

            $response->assertRedirect(route('login'));
            $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

            return true;
        });
    }

    public function test_a_password_reset_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->from(route('password.reset', ['token' => 'invalid-token']))->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
