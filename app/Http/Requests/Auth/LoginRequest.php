<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The captcha token is verified here rather than in the controller so that
     * every admin login passes through it before Auth::attempt() is ever called.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required', 'string', $this->recaptchaRule()],
        ];
    }

    /**
     * Server-side Google reCAPTCHA v2 verification.
     *
     * The checkbox on the login form proves nothing on its own: 'g-recaptcha-response'
     * arrives in the POST body and is attacker-controlled, so the only real check is
     * exchanging it with Google. Same siteverify endpoint and config keys the public
     * contact and inquiry forms already use.
     */
    private function recaptchaRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            try {
                $response = Http::timeout(10)
                    ->asForm()
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret'   => config('services.recaptcha.secret_key'),
                        'response' => $value,
                        'remoteip' => $this->ip(),
                    ]);
            } catch (ConnectionException) {
                // Fail closed. An unreachable Google API must not become a way around
                // the check, and the 10s timeout above keeps the wait short. The
                // deliberate cost is that nobody can log in while the API is down —
                // the alternative is an open admin login.
                $fail('reCAPTCHA could not be verified. Please try again in a moment.');

                return;
            }

            if (! $response->successful() || ! $response->json('success')) {
                $fail('Please complete the reCAPTCHA verification and try again.');
            }
        };
    }


    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
