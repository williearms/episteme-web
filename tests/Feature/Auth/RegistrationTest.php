<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_a_user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Rian Wicaksono',
            'email' => 'rian@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'rian@example.com']);
        $this->assertAuthenticated();
        $response->assertRedirect(route('account.dashboard'));
    }

    public function test_the_password_is_hashed_not_stored_in_plain_text(): void
    {
        $this->post(route('register'), [
            'name' => 'Rian Wicaksono',
            'email' => 'rian@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'rian@example.com')->firstOrFail();

        $this->assertNotSame('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_registration_fails_with_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Someone Else',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_a_new_user_receives_an_email_verification_notification(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $this->post(route('register'), [
            'name' => 'Rian Wicaksono',
            'email' => 'rian@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'rian@example.com')->firstOrFail();

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }
}
