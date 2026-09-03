<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects users who were created with a temporary password until they change
 * it. The forced-password-change screen and logout are exempt so the user can
 * complete the required change.
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->must_change_password && $this->isExempt($request)) {
            return $next($request);
        }

        if ($user !== null && $user->must_change_password) {
            return redirect()->route('forced-password-change');
        }

        return $next($request);
    }

    private function isExempt(Request $request): bool
    {
        return $request->routeIs('forced-password-change') || $request->routeIs('logout');
    }
}