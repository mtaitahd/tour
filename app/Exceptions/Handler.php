<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // When a user's session has expired, Laravel throws a TokenMismatchException
        // (rendered as the "419 Page Expired" page). Instead of showing that raw
        // error to the user, log them out (flush the stale session) and redirect
        // them cleanly to the login page so they never see a Laravel error.
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => route('login'),
                ], 419);
            }

            // Forget the authenticated user before flushing the session so they
            // are actually logged out, not just redirected.
            if (auth()->check()) {
                auth()->logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->guest(route('login'))
                ->with('status', 'Your session has expired. Please log in again to continue.');
        });
    }
}
