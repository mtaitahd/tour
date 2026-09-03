<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards entry into the unified admin panel (/admin). Both super admins and
 * package editors use the same panel; a package editor may only enter once an
 * admin has granted them at least one sidebar-module permission.
 */
class EnsurePanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('login'));
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $user->isPackageEditor() || ! $user->hasAnyPermission()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}