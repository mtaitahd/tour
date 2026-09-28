<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Editable headings and intro copy for the site's public listing pages.
 *
 * Both the "Pages Listing" (/pages) and the "Tours Listing" (/tours) headings used
 * to be hard-coded inside their Blade templates, so changing one meant a code
 * deploy. They now resolve through this catalogue instead: the template asks for
 * a key, and the admin edits the value on Admin → Listing Titles.
 *
 * Deliberately a thin wrapper over the existing `settings` table rather than a new
 * table or a new model — the settings table already stores every other editable
 * string on this site (site name, listing intros, footer copy), and it already
 * carries `label` / `description` / `group` columns for exactly this purpose.
 *
 * A slot only becomes overridable once an admin saves it. Until then — and
 * permanently, if the row is deleted again — the template falls back to the
 * built-in default declared here, so a fresh install and an emptied table both
 * render the shipped copy rather than a blank heading.
 */
class ListingTitles
{
    /**
     * The `settings.group` these rows are filed under, so the admin screen can
     * list them and so a future settings export groups them sensibly.
     */
    public const GROUP = 'listing_titles';

    /**
     * The editable slots, keyed by their settings key.
     *
     * Every entry here MUST be consumed by a public template — a slot nothing
     * reads would be exactly the kind of decorative admin control this project
     * avoids. Adding a new listing screen to the site means adding its slots
     * here AND wiring them into that screen's Blade template in the same change.
     *
     * `default` is the shipped fallback. It is `null` for the intro slots
     * because their fallback prose is long, lives in the template next to the
     * context that produced it (some intros are overridden per destination or
     * category), and is exposed on the admin screen as a hint instead of being
     * duplicated here and allowed to drift.
     *
     * @return array<string, array{label:string, description:string, default:?string, multiline:bool, screen:string}>
     */
    public static function catalogue(): array
    {
        return [
            'pages_listing_title' => [
                'screen'      => 'Pages Listing (/pages)',
                'label'       => 'Pages Listing Title',
                'description' => 'Heading, breadcrumb and browser tab title of the public Travel Information page.',
                'default'     => 'Travel Information',
                'multiline'   => false,
            ],
            'pages_listing_intro' => [
                'screen'      => 'Pages Listing (/pages)',
                'label'       => 'Pages Listing Introduction',
                'description' => 'Intro paragraph shown under the Pages Listing heading. Leave empty to use the shipped travel copy.',
                'default'     => null,
                'multiline'   => true,
            ],
            'tours_listing_title' => [
                'screen'      => 'Tours Listing (/tours)',
                'label'       => 'Tours Listing Title',
                'description' => 'Heading and browser tab title of the public tours listing. Only used for the unfiltered listing; a destination or category page composes its own heading from that subject.',
                'default'     => 'Our Best All Tours & Safaris Packages',
                'multiline'   => false,
            ],
            'tours_listing_intro' => [
                'screen'      => 'Tours Listing (/tours)',
                'label'       => 'Tours Listing Introduction',
                'description' => 'Intro paragraph shown under the Tours Listing heading. A destination or category description still wins over this.',
                'default'     => null,
                'multiline'   => true,
            ],
        ];
    }

    /**
     * Whether $key is a slot this screen is allowed to touch. Used to reject
     * unknown keys before they reach the settings table — the destroy route
     * takes a key from the URL, and nothing else should be deletable from here.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, static::catalogue());
    }

    /**
     * The saved override for a slot, or null when the template default applies.
     *
     * An empty/whitespace-only stored value counts as "not set" so a cleared
     * text field falls back to the default instead of rendering an empty H1.
     */
    public static function override(string $key): ?string
    {
        if (! static::has($key)) {
            return null;
        }

        $value = trim((string) Setting::get($key, ''));

        return $value === '' ? null : $value;
    }

    /**
     * The heading to render for a slot: the admin's override when there is one,
     * otherwise the shipped default, otherwise the supplied last-resort string.
     */
    public static function title(string $key, string $fallback = ''): string
    {
        $default = static::catalogue()[$key]['default'] ?? null;

        return static::override($key) ?? ($default !== null ? $default : $fallback);
    }

    /**
     * Intro prose, flattened to a single line of plain text.
     *
     * The tours intro is stored as HTML (it comes from the same textarea as the
     * rest of the site copy), but it is injected into a <p> as text, so tags are
     * stripped rather than rendered. Returns null when the admin has not written
     * one, which tells the caller to use its own context-aware default.
     */
    public static function intro(string $key, int $limit = 540): ?string
    {
        $raw = static::override($key);

        if ($raw === null) {
            return null;
        }

        $flat = trim(preg_replace('/\s+/', ' ', strip_tags($raw)));

        return $flat === '' ? null : Str::limit($flat, $limit);
    }

    /**
     * Catalogue rows decorated for the admin screen: label, description, screen,
     * the value currently in force, and whether that value is an admin override
     * or the shipped default. "Add" and "Delete" on that screen both operate on
     * that override flag.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function rows(): array
    {
        $rows = [];

        foreach (static::catalogue() as $key => $meta) {
            $override = static::override($key);

            $rows[] = [
                'key'         => $key,
                'screen'      => $meta['screen'],
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'multiline'   => $meta['multiline'],
                'is_override' => $override !== null,
                'value'       => $override ?? ($meta['default'] ?? ''),
                'default'     => $meta['default'],
            ];
        }

        return $rows;
    }
}
