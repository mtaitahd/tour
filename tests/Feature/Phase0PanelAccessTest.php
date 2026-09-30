<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authorization matrix for the unified admin panel.
 *
 * The separate Package Editor panel is gone: both roles use the same /admin
 * surface, and access is decided by the sidebar-module permissions in
 * config/panel.php rather than by which panel a role is sent to.
 */
class Phase0PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_reach_admin_panel(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    /**
     * The retired editor URL still resolves, so old bookmarks and e-mails land
     * somewhere real instead of a 404.
     */
    public function test_retired_editor_panel_redirects_into_the_unified_panel(): void
    {
        $editor = User::factory()->packageEditor()->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)->get(route('editor.dashboard'))->assertRedirect(route('dashboard'));
    }

    public function test_package_editor_reaches_only_the_modules_granted_to_them(): void
    {
        $editor = User::factory()->packageEditor()->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)->get(route('dashboard'))->assertOk();
        $this->actingAs($editor)->get(route('admin.destinations.index'))->assertOk();

        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.settings.index'))->assertForbidden();
    }

    /**
     * 'dashboard' and 'profile' are implicitly granted to every panel user, so an
     * editor with an empty grant set still has somewhere to land — but no module
     * beyond those two.
     */
    public function test_package_editor_without_a_granted_module_reaches_nothing_else(): void
    {
        $editor = User::factory()->packageEditor()->create(['permissions' => []]);

        $this->actingAs($editor)->get(route('dashboard'))->assertOk();
        $this->actingAs($editor)->get(route('admin.profile.show'))->assertOk();

        $this->actingAs($editor)->get(route('admin.destinations.index'))->assertForbidden();
    }

    public function test_role_less_account_is_denied_the_panel(): void
    {
        $user = User::factory()->noRole()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_suspended_account_is_denied_and_logged_out(): void
    {
        $editor = User::factory()->packageEditor()->suspended()->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)->get(route('dashboard'))->assertForbidden();

        $this->assertGuest();
    }
}
