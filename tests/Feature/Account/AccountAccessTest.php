<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_from_the_dashboard(): void
    {
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_guest_is_redirected_to_login_from_the_profile_page(): void
    {
        $this->get(route('account.profile'))->assertRedirect(route('login'));
    }

    public function test_a_guest_is_redirected_to_login_from_the_settings_page(): void
    {
        $this->get(route('account.settings'))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_user_can_access_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.dashboard'))->assertOk();
    }

    public function test_an_authenticated_user_can_access_the_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.profile'))->assertOk();
    }

    public function test_an_authenticated_user_can_access_the_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.settings'))->assertOk();
    }

    public function test_an_authenticated_user_can_still_browse_the_public_catalog(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('products.index'))->assertOk();
    }

    public function test_a_guest_can_still_browse_the_public_catalog(): void
    {
        $this->get(route('products.index'))->assertOk();
    }
}
