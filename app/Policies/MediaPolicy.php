<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

/**
 * Authorization for Media Library actions.
 *
 * NOTE on scope: this application currently has no role/permission system at all — the
 * `users` table has no role column, and every existing admin route is protected purely
 * by the `auth` middleware with no further per-action checks (confirmed across every
 * existing admin controller in Phase 1's analysis). So for now, every method here simply
 * requires an authenticated user, matching how the rest of the admin area already
 * behaves — this Policy exists so the *structure* for finer-grained authorization is in
 * place (and call sites in controllers don't need to change), ready to tighten the
 * moment a real role system exists (e.g. only "admin" or "editor" roles may delete).
 */
class MediaPolicy
{
    /**
     * Anyone authenticated may view the library, upload, and edit metadata/categories/tags.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GalleryImage $image): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GalleryImage $image): bool
    {
        return true;
    }

    /**
     * Deletion is the one place a future role system would most likely add a real
     * restriction (e.g. "only admins, not editors, may permanently delete media").
     * The check is isolated here on purpose so that's a one-line change later.
     */
    public function delete(User $user, GalleryImage $image): bool
    {
        return true;
    }
}
