<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Super Admin management of Package Editor accounts.
 *
 * Phase 0 supports create, suspend, reactivate and reset/setup password only —
 * editors are never permanently deleted so ownership and audit history survive.
 * The target must always be a package_editor account; Super Admin accounts
 * cannot be modified here.
 */
class ManageUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            Gate::authorize('manage-users');

            return $next($request);
        });
    }

    public function index(): View
    {
        $users = User::with('createdBy')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $modules = array_keys(config('panel.modules'));

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'lowercase', 'max:255', Rule::unique('users', 'email')],
            'role'          => ['required', 'string', Rule::in([User::ROLE_PACKAGE_EDITOR, User::ROLE_SUPER_ADMIN])],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in($modules)],
        ]);

        $role = $validated['role'];

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $role;
        $user->must_change_password = true;
        $user->is_suspended = false;
        $user->created_by = $request->user()->id;
        // Super admin implicitly holds every module (NULL = all); package
        // editors store the granted sidebar-module keys as a JSON array.
        $user->permissions = ($role === User::ROLE_SUPER_ADMIN)
            ? null
            : array_values($request->input('permissions', []));
        $user->save();

        $label = $role === User::ROLE_SUPER_ADMIN ? 'Admin' : 'Package Editor';

        return redirect()->route('admin.users.index')
            ->with('status', "{$label} \"{$user->name}\" created. They must change the temporary password on first login.");
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($user);

        $validated = $request->validate([
            'permissions'  => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(config('panel.modules')))],
        ]);

        $user->permissions = array_values($request->input('permissions', []));
        $user->save();

        return back()->with('status', "Permissions updated for \"{$user->name}\".");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($user);

        $user->is_suspended = true;
        $user->save();

        return back()->with('status', "Package Editor \"{$user->name}\" suspended.");
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($user);

        $user->is_suspended = false;
        $user->save();

        return back()->with('status', "Package Editor \"{$user->name}\" reactivated.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->must_change_password = true;
        $user->save();

        return back()->with('status', "Password reset for \"{$user->name}\". They must change it on next login.");
    }

    private function authorizeManageable(User $user): void
    {
        Gate::authorize('manage-users');

        if ($user->isSuperAdmin()) {
            abort(403, 'Super Admin accounts cannot be managed here.');
        }
    }
}