<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 0 first-login forced password change, enforced across the unified panel.
 */
class Phase0ForcedPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_with_temporary_password_is_forced_to_change_it(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()
            ->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)
            ->get(route('dashboard'))
            ->assertRedirect(route('forced-password-change'));

        // Also from inside the panel, not just from its landing page.
        $this->actingAs($editor)
            ->get(route('admin.destinations.index'))
            ->assertRedirect(route('forced-password-change'));

        $this->actingAs($editor)
            ->get(route('admin.profile.show'))
            ->assertRedirect(route('forced-password-change'));
    }

    public function test_editor_can_choose_a_new_password(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()
            ->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)
            ->post(route('forced-password-change'), [
                'current_password' => 'password',
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])
            ->assertRedirect(route('dashboard'));

        $editor->refresh();

        $this->assertFalse($editor->must_change_password);
        $this->assertTrue(password_verify('brand-new-secret', $editor->password));
    }

    public function test_wrong_temporary_password_is_rejected(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()
            ->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)
            ->post(route('forced-password-change'), [
                'current_password' => 'not-the-temp-password',
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue($editor->refresh()->must_change_password);
    }

    public function test_editor_without_requirement_is_not_forced(): void
    {
        $editor = User::factory()->packageEditor()->create(['permissions' => ['destinations']]);

        $this->actingAs($editor)->get(route('dashboard'))->assertOk();
        $this->actingAs($editor)->get(route('admin.destinations.index'))->assertOk();
    }
}
