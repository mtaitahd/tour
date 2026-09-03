<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 0 authorization matrix: which panel each account type may reach.
 */
class Phase0PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_reach_admin_panel(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin')->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_super_admin_is_denied_the_editor_panel(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get('/editor')->assertForbidden();
    }

    public function test_package_editor_can_reach_editor_panel_only(): void
    {
        $editor = User::factory()->packageEditor()->create();

        $this->actingAs($editor)->get('/editor')->assertOk();
    }

    public function test_package_editor_is_denied_the_admin_panel(): void
    {
        $editor = User::factory()->packageEditor()->create();

        $this->actingAs($editor)->get('/dashboard')->assertForbidden();
        $this->actingAs($editor)->get('/admin')->assertForbidden();
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_role_less_account_is_denied_both_panels(): void
    {
        $user = User::factory()->noRole()->create();

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/editor')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_suspended_account_is_denied_and_logged_out(): void
    {
        $editor = User::factory()->packageEditor()->suspended()->create();

        $this->actingAs($editor)->get('/editor')->assertForbidden();

        $this->assertGuest();
    }
}