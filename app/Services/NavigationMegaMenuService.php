<?php

namespace App\Services;

use App\Models\NavigationMegaMenuItem;
use App\Models\Page;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/**
 * Single source of truth for the header's three-column mega menus.
 *
 * Previously the header template hard-coded every category, image, link and CTA for
 * Kilimanjaro / Safari / Day Trips. Now the left column is built entirely from
 * navigation_mega_items rows that an administrator activates per tour/page, and the
 * middle/right columns (contextual description/CTA/image) are derived from the same
 * selected source — so the whole panel stays in sync, no reload required.
 *
 * Public contract ("only what the admin enabled appears"):
 *   - only rows with is_active = true are considered;
 *   - the source must still exist and be published (tours + pages);
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
                $q->whereHas('tour', fn ($tour) => $tour->where('status', 'published'))
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
            'heading'     => $item->menu_label,
            'description' => $item->descriptionText(),
            'cta'         => ['label' => $item->buttonLabel(), 'url' => $url],
            'image'       => $item->imageUrl(),
            'image_alt'   => $item->menu_label,
            'url'         => $url,
        ];
    }

    // ─── Admin persistence ────────────────────────────────────────────────────

    /**
     * Validation rules for the "Navigation Mega Menu" section of the tour/page
     * forms. All mega fields are optional; enabling the section then requires the
     * fields that define an item.
     */
    public function megaRules(): array
    {
        $parents = implode(',', array_keys($this->parentDefinitions()));

        return [
            'mega_menu.enabled'              => 'nullable|boolean',
            'mega_menu.parent_key'           => 'required_if:mega_menu.enabled,true|nullable|string|in:' . $parents,
            'mega_menu.label'                => 'required_if:mega_menu.enabled,true|nullable|string|max:120',
            'mega_menu.description'          => 'nullable|string|max:500',
            'mega_menu.button_label'         => 'nullable|string|max:120',
            'mega_menu.button_url_override'  => 'nullable|string|max:2048',
            'mega_menu.image_id'             => 'nullable|integer|exists:media,id',
            'mega_menu.badge_text'           => 'nullable|string|max:40',
            'mega_menu.display_order'        => 'nullable|integer|min:0|max:9999',
        ];
    }

    /**
     * Post-validation guard against exceeding the per-parent cap and against
     * duplicate (parent, source) pairs. Throws a ValidationException so the form
     * shows the message inline, mirroring pricing validation behaviour.
     */
    public function assertCanEnable(string $parentKey, Model $source, int $max): void
    {
        $sourceType = $this->sourceTypeFor($source);

        if (! $sourceType) {
            throw ValidationException::withMessages(['mega_menu' => 'This source type cannot be added to the mega menu.']);
        }

        $existing = NavigationMegaMenuItem::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $source->getKey())
            ->first();

        // The source's own existing row is fine — persistForSource() deletes it and
        // recreates it, so an edit of an already-configured item must not be treated
        // as a duplicate. A genuine duplicate is only possible when the source has no
        // row yet and something else (e.g. a raced create) already placed it here.
        $isOwnRow = $existing && $existing->parent_menu_key === $parentKey;

        if (! $isOwnRow && $existing) {
            throw ValidationException::withMessages([
                'mega_menu' => 'This source is already added to another menu. Choose the same menu or clear it first.',
            ]);
        }

        if ($existing && $existing->parent_menu_key === $parentKey) {
            return;
        }

        $current = NavigationMegaMenuItem::query()
            ->where('parent_menu_key', $parentKey)
            ->where('source_type', $sourceType)
            ->where('source_id', '!=', $source->getKey())
            ->count();

        if ($current >= $max) {
            throw ValidationException::withMessages([
                'mega_menu' => "Menu '{$this->parentLabel($parentKey)}' already holds the maximum of {$max} enabled items.",
            ]);
        }
    }

    /**
     * Save (or clear) the mega-menu entry for one source. Called inside the source
     * controller's DB transaction so the menu row and the source save commit or
     * roll back together.
     *
     * @param array<string, mixed>|null $mega validated mega_menu.* input, or null
     *                                     when the form didn't submit the section
     */
    public function persistForSource(Model $source, ?array $mega, ?int $actorId): void
    {
        $sourceType = $this->sourceTypeFor($source);

        if (! $sourceType) {
            return;
        }

        $media = app(MediaLibraryService::class);

        $previous = NavigationMegaMenuItem::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $source->getKey())
            ->first();

        if ($previous && $previous->image_id) {
            $media->forgetUsage((int) $previous->image_id, $source, self::MEGA_IMAGE_USAGE_CONTEXT);
        }

        if ($previous) {
            $previous->delete();
        }

        if (! is_array($mega) || empty($mega['enabled'])) {
            return;
        }

        $imageId = ! empty($mega['image_id']) ? (int) $mega['image_id'] : null;

        NavigationMegaMenuItem::create([
            'parent_menu_key'     => $mega['parent_key'],
            'source_type'         => $sourceType,
            'source_id'           => $source->getKey(),
            'menu_label'          => $mega['label'],
            'short_description'   => ! empty($mega['description']) ? $mega['description'] : null,
            'button_label'        => ! empty($mega['button_label']) ? $mega['button_label'] : null,
            'button_url_override' => ! empty($mega['button_url_override']) ? trim($mega['button_url_override']) : null,
            'image_id'            => $imageId,
            'badge_text'          => ! empty($mega['badge_text']) ? $mega['badge_text'] : null,
            'display_order'       => isset($mega['display_order']) ? (int) $mega['display_order'] : 0,
            'is_active'           => true,
            'created_by'          => $actorId,
            'updated_by'          => $actorId,
        ]);

        if ($imageId) {
            $media->recordUsage($imageId, $source, self::MEGA_IMAGE_USAGE_CONTEXT);
        }
    }

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

    /**
     * Current mega-menu values for a source (for pre-filling the admin form),
     * normalized to form field names so the blade can be shared verbatim between
     * tour and page create/edit views.
     *
     * @return array<string, mixed> Prefixed with 'mega_menu.' keys.
     */
    public function formValuesFor(?Model $source): array
    {
        $sourceType = $source ? $this->sourceTypeFor($source) : null;

        $row = $sourceType
            ? NavigationMegaMenuItem::query()
                ->where('source_type', $sourceType)
                ->where('source_id', $source->getKey())
                ->first()
            : null;

        $values = [
            'mega_menu.enabled'     => false,
            'mega_menu.parent_key'  => '',
            'mega_menu.label'       => '',
            'mega_menu.description' => '',
            'mega_menu.button_label' => '',
            'mega_menu.button_url_override' => '',
            'mega_menu.image_id'    => '',
            'mega_menu.badge_text'  => '',
            'mega_menu.display_order' => 0,
        ];

        if ($row) {
            $values = [
                'mega_menu.enabled'     => true,
                'mega_menu.parent_key'  => $row->parent_menu_key,
                'mega_menu.label'       => $row->menu_label,
                'mega_menu.description' => $row->short_description ?? '',
                'mega_menu.button_label' => $row->button_label ?? '',
                'mega_menu.button_url_override' => $row->button_url_override ?? '',
                'mega_menu.image_id'    => (string) ($row->image_id ?? ''),
                'mega_menu.badge_text'  => $row->badge_text ?? '',
                'mega_menu.display_order' => $row->display_order,
            ];
        }

        return $values;
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