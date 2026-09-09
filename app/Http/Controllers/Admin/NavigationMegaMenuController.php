<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMegaMenuItem;
use App\Services\MediaLibraryService;
use App\Services\NavigationMegaMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Standalone "Mega Nav" management — the dedicated admin module for the site's
 * three-column navigation mega menu.
 *
 * Mega menu items used to be configured from inside the Tour / Page forms; that
 * coupling has been removed. This controller manages the items directly. Each row
 * may be a standalone entry (source_type = 'custom', source_id = null) carrying the
 * left-column title, middle-column heading, short description, right-column image
 * and an explicit link URL, or an existing legacy row still linked to a tour/page —
 * both kinds are listed, editable and deletable here so the admin controls every
 * entry in the site's navigation mega menu.
 */
class NavigationMegaMenuController extends Controller
{
    /**
     * Display a listing of standalone mega-menu items.
     */
    public function index(): View
    {
        $items = NavigationMegaMenuItem::with(['image', 'creator'])
            ->orderBy('parent_menu_key')
            ->orderBy('display_order')
            ->orderBy('menu_label')
            ->paginate(20);

        $parents = app(NavigationMegaMenuService::class)->parentDefinitions();

        return view('admin.mega-nav.index', compact('items', 'parents'));
    }

    /**
     * Show the form for creating a new mega-menu item.
     */
    public function create(): View
    {
        $parents = app(NavigationMegaMenuService::class)->parentDefinitions();

        return view('admin.mega-nav.create', compact('parents'));
    }

    /**
     * Store a newly created mega-menu item.
     */
    public function store(Request $request): RedirectResponse
    {
        $parents = app(NavigationMegaMenuService::class)->parentDefinitions();

        $validated = $this->validated($request, $parents);

        $this->assertParentRoom($validated['parent_menu_key'], null);

        $actorId = $request->user()?->id;

        $item = NavigationMegaMenuItem::create([
            'parent_menu_key'     => $validated['parent_menu_key'],
            'source_type'         => NavigationMegaMenuItem::SOURCE_CUSTOM,
            'source_id'           => null,
            'menu_label'          => $validated['menu_label'],
            'heading'             => $validated['heading'],
            'short_description'   => $validated['short_description'],
            'button_label'        => $validated['button_label'] ?? null,
            'button_url_override' => $validated['button_url_override'],
            'image_id'            => (int) $validated['image_id'],
            'badge_text'          => $validated['badge_text'] ?? null,
            'display_order'       => (int) ($validated['display_order'] ?? 0),
            'is_active'           => $validated['is_active'],
            'created_by'          => $actorId,
            'updated_by'          => $actorId,
        ]);

        app(MediaLibraryService::class)->recordUsage(
            (int) $validated['image_id'],
            $item,
            NavigationMegaMenuService::MEGA_IMAGE_USAGE_CONTEXT,
        );

        return redirect()->route('admin.mega-nav.index')
            ->with('success', 'Mega Nav item added. It now appears in the site navigation.');
    }

    /**
     * Show the form for editing the specified mega-menu item.
     */
    public function edit(NavigationMegaMenuItem $megaNav): View
    {
        $megaNav->load('image');

        $parents = app(NavigationMegaMenuService::class)->parentDefinitions();

        return view('admin.mega-nav.edit', compact('megaNav', 'parents'));
    }

    /**
     * Update the specified mega-menu item.
     */
    public function update(Request $request, NavigationMegaMenuItem $megaNav): RedirectResponse
    {
        $parents = app(NavigationMegaMenuService::class)->parentDefinitions();

        $validated = $this->validated($request, $parents);

        $this->assertParentRoom($validated['parent_menu_key'], $megaNav);

        $previousImageId = (int) ($megaNav->image_id ?: 0);

        $megaNav->update([
            'parent_menu_key'     => $validated['parent_menu_key'],
            'menu_label'          => $validated['menu_label'],
            'heading'             => $validated['heading'],
            'short_description'   => $validated['short_description'],
            'button_label'        => $validated['button_label'] ?? null,
            'button_url_override' => $validated['button_url_override'],
            'image_id'            => (int) $validated['image_id'],
            'badge_text'          => $validated['badge_text'] ?? null,
            'display_order'       => (int) ($validated['display_order'] ?? 0),
            'is_active'           => $validated['is_active'],
            'updated_by'          => $request->user()?->id,
        ]);

        $this->syncImageUsage($megaNav, $previousImageId, (int) $validated['image_id']);

        return redirect()->route('admin.mega-nav.index')
            ->with('success', 'Mega Nav item updated.');
    }

    /**
     * Remove the specified mega-menu item.
     */
    public function destroy(NavigationMegaMenuItem $megaNav): RedirectResponse
    {
        app(MediaLibraryService::class)->forgetAllUsagesFor($megaNav);

        $megaNav->delete();

        return redirect()->route('admin.mega-nav.index')
            ->with('success', 'Mega Nav item removed.');
    }

    /**
     * Shared validation rules for store() and update(). Standalone items carry no
     * source, so the fields that define the menu entry are all required: parent menu,
     * left-column title, middle-column heading, short description, image and the
     * explicit link URL.
     */
    private function validated(Request $request, array $parents): array
    {
        $validated = $request->validate([
            'parent_menu_key'     => ['required', Rule::in(array_keys($parents))],
            'menu_label'          => ['required', 'string', 'max:120'],
            'heading'             => ['required', 'string', 'max:255'],
            'short_description'   => ['required', 'string', 'max:500'],
            'button_label'        => ['nullable', 'string', 'max:120'],
            'button_url_override' => ['required', 'url', 'max:2048'],
            'image_id'            => ['required', 'integer', 'exists:media,id'],
            'badge_text'          => ['nullable', 'string', 'max:40'],
            'display_order'       => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Guard the per-parent cap so the public renderer's take() never silently drops
     * items the admin added past the limit.
     */
    private function assertParentRoom(string $parentKey, ?NavigationMegaMenuItem $except): void
    {
        $max = app(NavigationMegaMenuService::class)->maxItemsPerMenu();

        $count = NavigationMegaMenuItem::query()
            ->where('source_type', NavigationMegaMenuItem::SOURCE_CUSTOM)
            ->where('parent_menu_key', $parentKey)
            ->when($except, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->count();

        if ($count >= $max) {
            throw ValidationException::withMessages([
                'parent_menu_key' => "Menu '{$this->parentLabel($parentKey)}' already holds the maximum of {$max} items.",
            ]);
        }
    }

    private function parentLabel(string $key): string
    {
        return app(NavigationMegaMenuService::class)->parentLabel($key);
    }

    /** Replace the recorded media usage when the admin changes the item's image. */
    private function syncImageUsage(NavigationMegaMenuItem $item, int $previousImageId, int $newImageId): void
    {
        $media = app(MediaLibraryService::class);

        if ($previousImageId === $newImageId) {
            return;
        }

        if ($previousImageId) {
            $media->forgetUsage($previousImageId, $item, NavigationMegaMenuService::MEGA_IMAGE_USAGE_CONTEXT);
        }

        $media->recordUsage($newImageId, $item, NavigationMegaMenuService::MEGA_IMAGE_USAGE_CONTEXT);
    }
}