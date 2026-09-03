<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 0 first-login forced password change.
 */
class Phase0ForcedPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_with_temporary_password_is_forced_to_change_it(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()->create();

        $this->actingAs($editor)
            ->get('/editor')
            ->assertRedirect(route('forced-password-change'));
    }

    public function test_editor_can_choose_a_new_password(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()->create();

        $this->actingAs($editor)
            ->post(route('forced-password-change'), [
                'current_password' => 'password',
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])
            ->assertRedirect('/editor');

        $editor->refresh();

        $this->assertFalse($editor->must_change_password);
        $this->assertTrue(password_verify('brand-new-secret', $editor->password));
    }

    public function test_wrong_temporary_password_is_rejected(): void
    {
        $editor = User::factory()->packageEditor()->mustChangePassword()->create();

        $this->actingAs($editor)
            ->post(route('forced-password-change'), [
                'current_password' => 'not-the-temp-password',
                'password' => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_editor_without_requirement_is_not_forced(): void
    {
        $editor = User::factory()->packageEditor()->create();

        $this->actingAs($editor)->get('/editor')->assertOk();
    }
}