<?php

namespace App\Models\Concerns;

/**
 * Resolves a public/ asset path that is known to live at more than one location
 * depending on how a given server was deployed.
 *
 * Why this exists: the tracked tree and the production server do not agree on
 * the layout of public/. On a fresh clone the logo is at
 * public/assets/img/logo-1.webp, but the production server serves it from
 * public/front-end/html/assets/img/logo-1.webp, and vice versa for whole
 * subtrees (production has public/asset/ from the NiceAdmin template, which is
 * not tracked at all). A hard-coded path therefore works on exactly one of the
 * two, and silently 404s on the other.
 *
 * Rather than hard-code a guess, callers pass every location the asset is known
 * to occupy and this picks the first one that actually exists on the machine
 * rendering the request. Results are memoised per path because it is called
 * once per media collection registration on list pages.
 */
trait ResolvesPublicFallbackAsset
{
    /** @var array<string, string> */
    protected static array $resolvedPublicAssets = [];

    /**
     * Return the asset() URL for the first candidate that exists under public/,
     * falling back to the first candidate when none do (so a view still renders
     * a predictable URL rather than an empty src).
     */
    public static function firstExistingPublicAsset(string $first, string ...$rest): string
    {
        $candidates = [$first, ...$rest];

        foreach ($candidates as $candidate) {
            if (isset(static::$resolvedPublicAssets[$candidate])) {
                $resolved = static::$resolvedPublicAssets[$candidate];
                if ($resolved !== null) {
                    return $resolved;
                }
                continue;
            }

            $exists = $candidate !== '' && file_exists(public_path($candidate));
            static::$resolvedPublicAssets[$candidate] = $exists ? asset($candidate) : null;

            if ($exists) {
                return static::$resolvedPublicAssets[$candidate];
            }
        }

        return asset($first);
    }
}
