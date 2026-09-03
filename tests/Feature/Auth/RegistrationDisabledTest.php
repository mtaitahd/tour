<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public self-registration is disabled. Accounts are only ever created by a
 * Super Admin through admin/users. Both the GET and POST routes must 404.
 */
class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_submission_is_not_available(): void
    {
        // With the register routes removed, GET /register falls through to the
        // site's category catch-all (404) while POST is not permitted (405).
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [404, 405], true));
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}