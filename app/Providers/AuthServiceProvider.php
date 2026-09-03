<?php

namespace App\Providers;

use App\Models\GalleryImage;
use App\Models\TourPackage;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Policies\TourPackagePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        GalleryImage::class => MediaPolicy::class,
        TourPackage::class => TourPackagePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        Gate::define('access-admin-panel', fn (User $user) => $user->isSuperAdmin() || ($user->isPackageEditor() && $user->hasAnyPermission()));
        Gate::define('access-editor-panel', fn (User $user) => $user->isPackageEditor() && ! $user->is_suspended);
        // Each admin module gate mirrors its sidebar permission key.
        Gate::define('manage-users', fn (User $user) => $user->canAccess('users'));
        Gate::define('manage-settings', fn (User $user) => $user->canAccess('settings'));
        Gate::define('manage-packages', fn (User $user) => $user->canAccess('tours'));
        Gate::define('review-packages', fn (User $user) => $user->canAccess('tours'));
    }
}