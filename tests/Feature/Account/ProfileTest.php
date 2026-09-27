<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Rian Wicaksono']);

        $this->actingAs($user)->get(route('account.profile'))->assertSee('Rian Wicaksono');
    }

    public function test_an_authenticated_user_can_update_their_name_and_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('account.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
    }

    public function test_email_uniqueness_is_enforced_on_profile_update(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $response = $this->actingAs($user)->from(route('account.profile'))->patch(route('account.profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertRedirect(route('account.profile'));
        $response->assertSessionHasErrors('email');
        $this->assertSame('mine@example.com', $user->fresh()->email);
    }

    public function test_changing_the_email_resets_verification_status(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com', 'email_verified_at' => now()]);
        $this->assertTrue($user->hasVerifiedEmail());

        $this->actingAs($user)->patch(route('account.profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_keeping_the_same_email_does_not_reset_verification_status(): void
    {
        $user = User::factory()->create(['email' => 'same@example.com', 'email_verified_at' => now()]);

        $this->actingAs($user)->patch(route('account.profile.update'), [
            'name' => 'New Name',
            'email' => 'same@example.com',
        ]);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_updating_a_profile_never_changes_another_users_data(): void
    {
        $userA = User::factory()->create(['name' => 'User A', 'email' => 'a@example.com']);
        $userB = User::factory()->create(['name' => 'User B', 'email' => 'b@example.com']);

        $this->actingAs($userA)->patch(route('account.profile.update'), [
            'name' => 'User A Updated',
            'email' => 'a-updated@example.com',
        ]);

        $userB->refresh();
        $this->assertSame('User B', $userB->name);
        $this->assertSame('b@example.com', $userB->email);
    }

    public function test_a_guest_cannot_update_a_profile(): void
    {
        $this->patch(route('account.profile.update'), [
            'name' => 'Nobody',
            'email' => 'nobody@example.com',
        ])->assertRedirect(route('login'));
    }
}
