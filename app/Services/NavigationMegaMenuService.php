<?php

namespace App\Services;

use App\Models\NavigationMegaMenuItem;
use App\Models\Page;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Single source of truth for the header's three-column mega menus.
 *
 * Previously the header template hard-coded every category, image, link and CTA for
 * Kilimanjaro / Safari / Day Trips. Now the left column is built entirely from
 * navigation_mega_items rows. An item is either a standalone entry created in the
 * dedicated "Mega Nav" admin module (source_type = 'custom' — carries its own title,
 * heading, description, image and link URL) or a legacy row linked to a tour/page;
 * the middle/right columns (contextual description/CTA/image) are derived from the
 * item, so the whole panel stays in sync, no reload required.
 *
 * Public contract ("only what the admin enabled appears"):
 *   - only rows with is_active = true are considered;
 *   - custom items render on their own; source-linked rows additionally require the
 *     source to still exist and be published (tours + pages);
 *   - a parent with no qualifying items simply isn't rendered as a mega menu
 *     (its trigger link still works normally);
 *   - all source+image data is eager loaded (no N+1 across columns).
 */
class NavigationMegaMenuService
{
    public const MEGA_IMAGE_USAGE_CONTEXT = 'mega_menu_image';

    /** All configured top-level parents (config/navigation.php). */
    public function parentDefinitions(): array
    {
        return (array) config('navigation.parent_menus', []);
    }

    public function isDefinedParent(string $key): bool
    {
        return array_key_exists($key, $this->parentDefinitions());
    }

    public function parentLabel(string $key): string
    {
        return (string) ($this->parentDefinitions()[$key]['label'] ?? $key);
    }

    public function triggerUrl(string $key): ?string
    {
        $def = $this->parentDefinitions()[$key] ?? null;

        if (! $def) {
            return null;
        }

        return route($def['route'], $def['params'] ?? []);
    }

    public function maxItemsPerMenu(): int
    {
        return max(1, (int) config('navigation.max_items_per_menu', 6));
    }

    /**
     * The active/current state of a top-level parent on the current request,
     * preserving the exact semantics the old hard-coded header used.
     */
    public function isParentActive(string $key): bool
    {
        $routeName = optional(Route::current())->getName();
        $params = optional(Route::current())->parameters() ?? [];
        $catSlug = $params['categorySlug'] ?? ($params['slug'] ?? null);

        $kiliActive = $routeName === 'tours.category' && $catSlug === 'kilimanjaro-climbing';

        if ($key === 'kilimanjaro') {
            return $kiliActive;
        }

        if ($key === 'safari') {
            return ! $kiliActive && in_array($routeName, $this->parentDefinitions()['safari']['active_routes'], true);
        }

        $routes = $this->parentDefinitions()[$key]['active_routes'] ?? [];

        return in_array($routeName, $routes, true);
    }

    /**
     * All publishable items for one parent, eager-loaded and in display order.
     * Broken/deleted sources, drafts and archived tours never appear here.
     *
     * @return Collection<int, NavigationMegaMenuItem>
     */
    public function itemsForParent(string $key): Collection
    {
        if (! $this->isDefinedParent($key)) {
            return collect();
        }

        $items = NavigationMegaMenuItem::query()
            ->where('parent_menu_key', $key)
            ->where('is_active', true)
            ->where(function (Builder $q) {
                $q->where('source_type', NavigationMegaMenuItem::SOURCE_CUSTOM)
                  ->orWhereHas('tour', fn ($tour) => $tour->where('status', 'published'))
                  ->orWhereHas('page', fn ($page) => $page->where('status', 'published'));
            })
            ->with(['image', 'tour.heroImage', 'page.heroImage'])
            ->get()
            ->filter(fn (NavigationMegaMenuItem $item) => $item->url() !== null)
            ->sortBy([['display_order', 'asc'], ['menu_label', 'asc']])
            ->values();

        return $items->take($this->maxItemsPerMenu());
    }

    /**
     * Full data structure the header template renders. Only parents with at least
     * one publishable item are included — a parent without items renders as its
     * plain trigger link instead of an empty mega panel.
     *
     * @return array<string, array{label: string, trigger_url: string, active: bool, categories: array}>
     */
    public function frontendData(): array
    {
        $data = [];

        foreach (array_keys($this->parentDefinitions()) as $key) {
            $items = $this->itemsForParent($key);

            if ($items->isEmpty()) {
                continue;
            }

            $data[$key] = [
                'label'       => $this->parentLabel($key),
                'trigger_url' => (string) $this->triggerUrl($key),
                'active'      => $this->isParentActive($key),
                'categories'  => $items->map(fn (NavigationMegaMenuItem $item) => $this->toViewItem($item))->all(),
            ];
        }

        return $data;
    }

    /**
     * Normalize one row into the exact shape the header's three-column markup and
     * its tab/panel/image JS already expects (title/badge/heading/description/cta/
     * image), so the frontend re-uses the battle-tested interactions unchanged.
     */
    protected function toViewItem(NavigationMegaMenuItem $item): array
    {
        $url = $item->url();

        return [
            'title'       => $item->menu_label,
            'badge'       => $item->badge_text ?: null,
            'heading'     => $item->heading ?: $item->menu_label,
            'description' => $item->descriptionText(),
            'cta'         => ['label' => $item->buttonLabel(), 'url' => $url],
            'image'       => $item->imageUrl(),
            'image_alt'   => $item->menu_label,
            'url'         => $url,
        ];
    }

    // ─── Source-linked row cleanup (legacy tour/page items) ────────────────────

    /** Remove the menu entry for a source — called from destroy(). */
    public function clearForSource(Model $source): void
    {
        $sourceType = $this->sourceTypeFor($source);

        if (! $sourceType) {
            return;
        }

        NavigationMegaMenuItem::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $source->getKey())
            ->delete();
    }

    protected function sourceTypeFor(?Model $source): ?string
    {
        if (! $source) {
            return null;
        }

        return match (true) {
            $source instanceof TourPackage => NavigationMegaMenuItem::SOURCE_TOUR,
            $source instanceof Page        => NavigationMegaMenuItem::SOURCE_PAGE,
            default                        => null,
        };
    }

    /**
     * Remove media rescission cleanup for a deleted image: media_usages rows
     * referencing a navigation row's image are owned by the source model, so once
     * the source is gone they'd already be handled by forgetAllUsagesFor(). This
     * helper exists for completeness if a future caller needs to drop the usage
     * without touching the source (kept public for the test suite).
     */
    public function forgetImageUsage(Model $source, int $imageId): void
    {
        app(MediaLibraryService::class)->forgetUsage($imageId, $source, self::MEGA_IMAGE_USAGE_CONTEXT);
    }
}