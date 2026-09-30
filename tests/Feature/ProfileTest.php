<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The profile area served by the admin panel: GET/POST /profile, plus the
 * separate password-change endpoint.
 *
 * This is the panel's own profile screen (Admin\ProfileController), not Breeze's
 * scaffolding: there is no PATCH verb here, and accounts are not self-deleting —
 * a super admin deactivates or deletes staff from User Management instead, so
 * nothing in this file asserts an endpoint the app does not expose.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile.show'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_profile_update_rejects_a_malformed_or_taken_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post('/profile', [
                'name' => 'Test User',
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->actingAs($user)
            ->from('/profile')
            ->post('/profile', [
                'name' => 'Test User',
                'email' => $other->email,
            ])
            ->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->assertNotSame($other->email, $user->refresh()->email);
    }

    public function test_password_can_be_updated_and_the_session_is_ended(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile/password', [
                'current_password'  => 'password',
                'password'          => 'brand-New1!',
                'password_confirmation' => 'brand-New1!',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue(Hash::check('brand-New1!', $user->password));

        // The panel signs the user out after a password change so the new
        // password is exercised from a clean session.
        $this->assertGuest();
    }

    public function test_password_update_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post('/profile/password', [
                'current_password'      => 'wrong-password',
                'password'              => 'brand-New1!',
                'password_confirmation' => 'brand-New1!',
            ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_password_update_enforces_the_strength_policy(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post('/profile/password', [
                'current_password'      => 'password',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
