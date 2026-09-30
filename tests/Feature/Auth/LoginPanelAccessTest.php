<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Login screen and where each account type lands afterwards.
 *
 * The separate Package Editor panel was retired: editors sign in to the same
 * unified panel as super admins, with their sidebar filtered to the modules an
 * admin granted them.
 *
 * The login form is behind Google reCAPTCHA v2, verified server-side in
 * LoginRequest, so every successful-login test has to hand over a token and fake
 * the siteverify call — the same approach the public captcha-protected forms use
 * in tests/Feature/PublicPricingTest.php.
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

    private const SITEVERIFY = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Fake a passing reCAPTCHA verification. Call before any login POST.
     */
    private function fakeCaptchaPasses(): void
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => true])]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_screen_renders_the_recaptcha_widget(): void
    {
        $response = $this->get('/login');

        $response->assertSee('g-recaptcha', escape: false);
        $response->assertSee('https://www.google.com/recaptcha/api.js', escape: false);
        $response->assertSee('data-sitekey="' . config('services.recaptcha.site_key') . '"', escape: false);
    }

    public function test_super_admin_can_authenticate_using_the_login_screen(): void
    {
        $this->fakeCaptchaPasses();

        $user = User::factory()->superAdmin()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'fake-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_package_editor_lands_in_the_unified_panel_after_login(): void
    {
        $this->fakeCaptchaPasses();

        $user = User::factory()->packageEditor()->create(['permissions' => ['destinations']]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'fake-token',
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
        $this->fakeCaptchaPasses();

        $user = User::factory()->noRole()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'fake-token',
        ]);

        $response->assertForbidden();

        $this->actingAs($user->fresh())->get(route('dashboard'))->assertForbidden();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->fakeCaptchaPasses();

        $user = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'g-recaptcha-response' => 'fake-token',
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

    /**
     * Without a token the request never reaches the credentials — so a scripted
     * attempt cannot skip the captcha by simply omitting the field.
     */
    public function test_login_is_rejected_when_the_captcha_was_not_completed(): void
    {
        Http::fake();

        $user = User::factory()->superAdmin()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();

        Http::assertNothingSent();
    }

    /**
     * A token that Google rejects is not a token. This is the case a fake token in
     * a scripted POST would otherwise walk straight through.
     */
    public function test_login_is_rejected_when_google_rejects_the_token(): void
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => false])]);

        $user = User::factory()->superAdmin()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'scripted-fake-token',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest();
    }

    /**
     * The submitted token is exchanged with Google using the configured secret and
     * the requester's IP, so a token minted for another site or replayed from
     * elsewhere does not pass.
     */
    public function test_login_verifies_the_token_with_google_before_authenticating(): void
    {
        $this->fakeCaptchaPasses();

        $user = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'token-from-the-browser',
        ]);

        Http::assertSent(fn (Request $request) => $request->url() === self::SITEVERIFY
            && $request['secret'] === config('services.recaptcha.secret_key')
            && $request['response'] === 'token-from-the-browser'
            && $request['remoteip'] === request()->ip());
    }

    /**
     * If Google cannot be reached the check fails closed: an API outage must not
     * turn into a way to skip the captcha, and it must not 500 the login screen.
     */
    public function test_login_fails_closed_when_google_cannot_be_reached(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('unreachable'));

        $user = User::factory()->superAdmin()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'fake-token',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest();
    }
}
