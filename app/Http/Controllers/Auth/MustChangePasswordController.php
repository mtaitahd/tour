<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * First-login forced password change. Used whenever an account was created
 * with a temporary password (must_change_password = true). The current
 * (temporary) password must be confirmed before a new one is accepted.
 */
class MustChangePasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.forced-password-change');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password does not match your existing password.'],
            ]);
        }

        $user->password = $request->input('password');
        $user->must_change_password = false;
        $user->save();

        return redirect()->intended($user->panelHome());
    }
}