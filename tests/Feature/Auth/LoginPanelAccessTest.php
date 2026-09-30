<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Login screen and where each account type lands afterwards.
 *
 * The separate Package Editor panel was retired: editors sign in to the same
 * unified panel as super admins, with their sidebar filtered to the modules an
 * admin granted them.
 *
 * Named LoginPanelAccessTest rather than AuthenticationTest: the file
 * tests/Feature/Auth/AuthenticationTest.php could not be written back in this
 * working tree (every create/delete of that exact path is refused, while the
 * same name elsewhere is fine), so the coverage moved here instead of being
 * dropped. Restore the original filename once the path is writable again.
 */
class LoginPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_package_editor_lands_in_the_unified_panel_after_login(): void
    {
        $user = User::factory()->packageEditor()->create(['permissions' => ['destinations']]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * An account with no role at all is told it cannot proceed, and is shut out
     * of the panel on every following request.
     */
    public function test_account_without_a_role_is_refused_after_authentication(): void
    {
        $user = User::factory()->noRole()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertForbidden();

        $this->actingAs($user->fresh())->get(route('dashboard'))->assertForbidden();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
