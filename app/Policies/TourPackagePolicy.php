<?php

namespace App\Policies;

use App\Models\TourPackage;
use App\Models\User;

/**
 * Authorization foundation for tour packages.
 *
 * Phase 0 stabilizes the access rules; the Package Editor workflow phase wires
 * these into the editor and admin controllers. Any panel user may view and
 * create. Updates by editors are limited to packages they created while the
 * package is a Draft or marked Changes Requested. Publishing, reviews and
 * deletion are Super Admin only.
 */
class TourPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasValidRole();
    }

    public function view(User $user, TourPackage $tourPackage): bool
    {
        return $user->hasValidRole();
    }

    public function create(User $user): bool
    {
        return $user->hasValidRole();
    }

    public function update(User $user, TourPackage $tourPackage): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isPackageEditor()
            && (int) $tourPackage->created_by === (int) $user->id
            && in_array($tourPackage->status, ['draft', 'changes_requested'], true);
    }

    public function delete(User $user, TourPackage $tourPackage): bool
    {
        return $user->isSuperAdmin();
    }

    public function publish(User $user, TourPackage $tourPackage): bool
    {
        return $user->isSuperAdmin();
    }

    public function unpublish(User $user, TourPackage $tourPackage): bool
    {
        return $user->isSuperAdmin();
    }

    public function archive(User $user, TourPackage $tourPackage): bool
    {
        return $user->isSuperAdmin();
    }

    public function requestChanges(User $user, TourPackage $tourPackage): bool
    {
        return $user->isSuperAdmin();
    }
}