<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_view_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.settings'))->assertOk();
    }

    public function test_current_password_is_required_to_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($user)->from(route('account.settings'))->put(route('account.settings.password'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_an_incorrect_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($user)->from(route('account.settings'))->put(route('account.settings.password'), [
            'current_password' => 'totally-wrong',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_a_valid_password_change_succeeds(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($user)->put(route('account.settings.password'), [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_a_guest_cannot_change_a_password(): void
    {
        $this->put(route('account.settings.password'), [
            'current_password' => 'whatever',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));
    }
}
