<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_PACKAGE_EDITOR = 'package_editor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'avatar_image_id',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_suspended' => 'boolean',
        'must_change_password' => 'boolean',
        'permissions' => 'array',
    ];

    // Profile picture selected from the shared Media Library (picker-driven path).
    // Additive alongside the legacy 'avatar' storage-disk column — see avatarUrl().
    public function avatarImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'avatar_image_id');
    }

    /**
     * Avatar URL, preferring the Media Library selection (avatar_image_id) when set,
     * falling back to the legacy direct-upload 'avatar' storage path otherwise — same
     * fallback convention as Destination::heroUrl() and TourPackage's hero image.
     */
    public function avatarUrl(string $conversion = ''): ?string
    {
        if ($this->avatarImage) {
            return $this->avatarImage->getUrl($conversion) ?: $this->avatarImage->getUrl();
        }

        return $this->avatar ? \Storage::url($this->avatar) : null;
    }

    /**
     * Whether this user has a profile picture at all, via either path.
     */
    public function hasAvatar(): bool
    {
        return $this->avatarImage !== null || (bool) $this->avatar;
    }

    // ─── Role / access helpers ─────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isPackageEditor(): bool
    {
        return $this->role === self::ROLE_PACKAGE_EDITOR;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * A user without a valid role must never reach either panel.
     */
    public function hasValidRole(): bool
    {
        return $this->isSuperAdmin() || $this->isPackageEditor();
    }

    public function isActive(): bool
    {
        return ! $this->is_suspended;
    }

    // ─── Sidebar-module permissions ───────────────────────────────────────

    /**
     * The sidebar-module keys granted to this user. Super admin implies the full
     * set; package editors use the value stored in the 'permissions' column.
     */
    public function permissionKeys(): array
    {
        if ($this->isSuperAdmin()) {
            return array_keys(config('panel.modules', []));
        }

        return array_values((array) $this->permissions);
    }

    /**
     * Whether this user can access the given sidebar module.
     *
     * 'dashboard' and 'profile' are always granted so every panel user can land
     * on the dashboard (and manage their own account); super admin implicitly
     * holds every module. All other modules require an explicit grant.
     */
    public function canAccess(string $module): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (in_array($module, ['dashboard', 'profile'], true)) {
            return true;
        }

        return in_array($module, (array) $this->permissions, true);
    }

    /**
     * Whether this user holds at least one sidebar permission (or is super
     * admin). 'dashboard' and 'profile' are always granted to every valid-role
     * panel user, so only role-less accounts are blocked.
     */
    public function hasAnyPermission(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->isPackageEditor();
    }

    /**
     * The first sidebar module this user can access, used as their landing page
     * when they are not a super admin.
     */
    public function firstGrantedModule(): ?string
    {
        foreach (array_keys(config('panel.modules', [])) as $module) {
            if ($this->canAccess($module)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Role-based landing page after authentication. Role-less users are denied.
     */
    public function panelHome(): string
    {
        if ($this->isSuperAdmin()) {
            return '/dashboard';
        }

        if ($this->isPackageEditor()) {
            $module = $this->firstGrantedModule();
            $route  = config("panel.modules.{$module}.route");

            if ($module !== null && $route !== null && \Route::has($route)) {
                return route($route);
            }

            return '/dashboard';
        }

        abort(403, 'Your account has not been assigned a role.');
    }

    /**
     * The user that created this account (Super Admin acting as admin).
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }
}
