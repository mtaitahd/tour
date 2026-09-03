<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Page;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function show($slug)
    {
        // Fixed: this previously filtered on 'is_published', a column the admin
        // form never touches at all (it manages 'status' — draft/published). Since
        // 'is_published' defaults to true at the DB level and nothing ever set it to
        // false, every page was publicly visible regardless of its actual draft/
        // published status — including unfinished drafts. 'status' is the real field.
        $page = Page::where('slug', $slug)
                    ->where('status', 'published')
                    ->firstOrFail();

        // Special view for contact
        if ($slug === 'contact') {
            return view('pages.contact', compact('page'));
        }
        if ($slug === 'about-us') {
            $testimonials = \App\Models\Testimonial::published()
                                                   ->general()
                                                   ->orderBy('order')
                                                   ->take(6)
                                                   ->get();

            return view('pages.about', compact('page', 'testimonials'));
        }

        // Default template for any other content-only page (Terms & Conditions,
        // Refund Policy, Privacy Policy, Booking Terms, etc.) — these all share the
        // same structure: breadcrumb + rich-text content + optional CTA, so one
        // template covers all of them without needing a special case per page.
        //
        // This replaces the old fallback to 'admin.pages.show', which had a real bug:
        // it used @section('content'), but frontend.layouts.app only yields
        // @yield('page-content') (same as every other public page here). Since the
        // section name didn't match, Blade silently discarded the entire body,
        // leaving pages like a newly-added Terms & Conditions page rendering with
        // just the header/footer and a blank middle — no error, just nothing shown.
        return view('pages.show', compact('page'));
    }

    public function index()
    {
        $pages = Page::orderBy('order')->orderBy('title')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'title'             => 'required|string|max:255',
        // Fixed pre-existing bug: this previously referenced $page->id before $page was
        // ever defined in this method (it's only created further down) — store() is
        // creating a brand new page, so there's no existing row to exclude from the
        // uniqueness check at all.
        'slug'              => 'required|string|max:255|unique:pages,slug',
        'content'           => 'nullable|string',
        'status'            => 'required|in:draft,published',
        'order'             => 'nullable|integer|min:0',
        'meta_title'        => 'nullable|string|max:255',
        'meta_description'  => 'nullable|string|max:500',
        'meta_keywords'     => 'nullable|string|max:500',
        'no_robots'         => 'nullable|boolean',

        // FIX: file validation (not string!)
        'hero_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'story_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

        // Media Library picker path — additive alongside the file-upload fields above.
        'hero_image_id'     => 'nullable|integer|exists:media,id',
        'story_image_id'    => 'nullable|integer|exists:media,id',

        // Extra fields...
        'extra_heading'         => 'nullable|string|max:255',
        'extra_subheading'      => 'nullable|string',
        'extra_hero_image'      => 'nullable|string|max:255',
        'cta_text'              => 'nullable|string|max:255',
        'cta_link'              => 'nullable|url|max:255',
        'contact_heading'       => 'nullable|string|max:255',
        'contact_subheading'    => 'nullable|string',
        'contact_map_embed'     => 'nullable|string',
        'story_title'           => 'nullable|string|max:255',
        'why_choose_subtitle'   => 'nullable|string|max:255',
        // Submitted as nested repeater arrays (see admin/pages/edit.blade.php), not
        // pre-encoded JSON strings — encoded into stats_counters/custom_data manually
        // below, so these two keys are deliberately NOT part of $validated's mass
        // assignment.
        'stats_counters.*.value'   => 'nullable|string|max:20',
        'stats_counters.*.suffix'  => 'nullable|string|max:10',
        'stats_counters.*.label'   => 'nullable|string|max:100',
        'team_members.*.name'          => 'nullable|string|max:255',
        'team_members.*.role'          => 'nullable|string|max:255',
        'team_members.*.bio'           => 'nullable|string',
        'team_members.*.photo_image_id' => 'nullable|integer|exists:media,id',
        // ── Navigation Mega Menu ─────────────────────────────────────────────────
        ...$this->megaRules(),
    ]);

    // Auto-generate slug if empty
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['stats_counters'] = $this->encodeStatsCounters($request);
        $validated['custom_data'] = $this->encodeTeamMembers($request);
        $validated['no_robots'] = $request->boolean('no_robots');

        $this->assertMegaEnableable($request);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated) {
        // Create the page
        $page = Page::create($validated);

        // Upload hero image if provided
        if ($request->hasFile('hero_image')) {
            $page->addMediaFromRequest('hero_image')
                 ->toMediaCollection('hero');
        }

        // Story image, on create — the original store() never handled this at all
        // (only update() did), even though the create form's Story Image field exists
        // for about/about-us pages. Added here for parity with update().
        if ($request->hasFile('story_image')) {
            $page->addMediaFromRequest('story_image')
                 ->toMediaCollection('story');
        }

        // Media Library selections, on create — there's no "previous" usage to forget
        // here since the page didn't exist a moment ago.
        $mediaLibrary = app(MediaLibraryService::class);

        if (! empty($validated['hero_image_id'])) {
            $hero = GalleryImage::find($validated['hero_image_id']);
            if ($hero) {
                $mediaLibrary->recordUsage($hero->id, $page, 'hero_image_id');
            }
        }

        if (! empty($validated['story_image_id'])) {
            $story = GalleryImage::find($validated['story_image_id']);
            if ($story) {
                $mediaLibrary->recordUsage($story->id, $page, 'story_image_id');
            }
        }

        // Team member photos — tracked as a single ordered set under one context,
        // same mechanism as Destination/TourPackage galleries, rather than diffing
        // individual photos by hand.
        $teamPhotoIds = collect($page->custom_data ?? [])->pluck('photo_image_id')->filter()->values()->all();
        $mediaLibrary->setOrderedUsages($page, 'team_photo', $teamPhotoIds);

        $this->persistMega($request, $page);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.pages.index')
                         ->with('success', 'Page created successfully!');
        });
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
        'title'             => 'required|string|max:255',
        'slug'              => 'required|string|max:255|unique:pages,slug,' . ($page->id ?? ''),
        'content'           => 'nullable|string',
        'status'            => 'required|in:draft,published',
        'order'             => 'nullable|integer|min:0',
        'meta_title'        => 'nullable|string|max:255',
        'meta_description'  => 'nullable|string|max:500',
        'meta_keywords'     => 'nullable|string|max:500',
        'no_robots'         => 'nullable|boolean',

        // FIX: file validation (not string!)
        'hero_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'story_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

        // Media Library picker path — additive alongside the file-upload fields above.
        'hero_image_id'     => 'nullable|integer|exists:media,id',
        'story_image_id'    => 'nullable|integer|exists:media,id',

        // Extra fields...
        'extra_heading'         => 'nullable|string|max:255',
        'extra_subheading'      => 'nullable|string',
        'extra_hero_image'      => 'nullable|string|max:255',
        'cta_text'              => 'nullable|string|max:255',
        'cta_link'              => 'nullable|url|max:255',
        'contact_heading'       => 'nullable|string|max:255',
        'contact_subheading'    => 'nullable|string',
        'contact_map_embed'     => 'nullable|string',
        'story_title'           => 'nullable|string|max:255',
        'why_choose_subtitle'   => 'nullable|string|max:255',
        'stats_counters.*.value'   => 'nullable|string|max:20',
        'stats_counters.*.suffix'  => 'nullable|string|max:10',
        'stats_counters.*.label'   => 'nullable|string|max:100',
        'team_members.*.name'          => 'nullable|string|max:255',
        'team_members.*.role'          => 'nullable|string|max:255',
        'team_members.*.bio'           => 'nullable|string',
        'team_members.*.photo_image_id' => 'nullable|integer|exists:media,id',
        // ── Navigation Mega Menu ─────────────────────────────────────────────────
        ...$this->megaRules(),
    ]);

    $validated['stats_counters'] = $this->encodeStatsCounters($request);
    $validated['custom_data'] = $this->encodeTeamMembers($request);
    $validated['no_robots'] = $request->boolean('no_robots');

    $this->assertMegaEnableable($request);

    return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $page, $validated) {
    $mediaLibrary = app(MediaLibraryService::class);

        // Capture the pre-update FK values before update() runs — getOriginal() still
        // returns these correctly afterward too (Eloquent's update()/save() only calls
        // syncChanges(), not syncOriginal(), so $page->original is untouched until the
        // model is next freshly loaded from the database), but reading them explicitly
        // here up front keeps the forget/record logic below simple to follow.
        $previousHeroId = $page->hero_image_id;
        $previousStoryId = $page->story_image_id;

        $page->update($validated);

        // Media Library hero/story selection — additive alongside the existing
        // direct-upload paths below, mirroring DestinationController::update().
        if ($request->filled('hero_image_id') && (int) $request->input('hero_image_id') !== (int) $previousHeroId) {
            if ($previousHeroId) {
                $oldHero = GalleryImage::find($previousHeroId);
                if ($oldHero) {
                    $mediaLibrary->forgetUsage($oldHero->id, $page, 'hero_image_id');
                }
            }
            $newHero = GalleryImage::find($validated['hero_image_id']);
            if ($newHero) {
                $mediaLibrary->recordUsage($newHero->id, $page, 'hero_image_id');
            }
        }

        if ($request->filled('story_image_id') && (int) $request->input('story_image_id') !== (int) $previousStoryId) {
            if ($previousStoryId) {
                $oldStory = GalleryImage::find($previousStoryId);
                if ($oldStory) {
                    $mediaLibrary->forgetUsage($oldStory->id, $page, 'story_image_id');
                }
            }
            $newStory = GalleryImage::find($validated['story_image_id']);
            if ($newStory) {
                $mediaLibrary->recordUsage($newStory->id, $page, 'story_image_id');
            }
        }

        // Hero image – Spatie
        if ($request->hasFile('hero_image')) {
            $page->clearMediaCollection('hero');
            $page->addMediaFromRequest('hero_image')
                 ->toMediaCollection('hero');
        }

        // Story image – Spatie (same as hero)
        if ($request->hasFile('story_image')) {
            $page->clearMediaCollection('story');
            $page->addMediaFromRequest('story_image')
                 ->toMediaCollection('story');
        }

        // Team member photos — full replace of the ordered set, same as store().
        $teamPhotoIds = collect($page->custom_data ?? [])->pluck('photo_image_id')->filter()->values()->all();
        $mediaLibrary->setOrderedUsages($page, 'team_photo', $teamPhotoIds);

        $this->persistMega($request, $page);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.pages.index')
                         ->with('success', 'Page updated successfully!');
        });
    }

    
    public function destroy(Page $page)
    {
        // See TourPackageController::destroy() for the full rationale — without this,
        // deleting a page leaves orphaned media_usages rows pointing at a model_id
        // that no longer exists, which would keep its hero/story images incorrectly
        // marked "in use" forever.
        app(\App\Services\MediaLibraryService::class)->forgetAllUsagesFor($page);

        app(\App\Services\NavigationMegaMenuService::class)->clearForSource($page);

        $page->delete();
        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.pages.index')
                         ->with('success', 'Page deleted successfully!');
    }

    /**
     * Build the Stats Counters repeater (About page) as a plain array for
     * Page::stats_counters. Blank rows (no value and no label) are dropped.
     *
     * Returns an array, not a JSON string — Page::$casts already casts this
     * attribute as 'array', so Eloquent encodes it to JSON on save. Passing an
     * already-encoded string here would get double-encoded.
     */
    private function encodeStatsCounters(Request $request): array
    {
        $counters = [];

        foreach ($request->input('stats_counters', []) as $counterData) {
            $counter = [
                'value'  => $counterData['value'] ?? '',
                'suffix' => $counterData['suffix'] ?? '+',
                'label'  => $counterData['label'] ?? '',
            ];

            if ($counter['value'] !== '' || $counter['label'] !== '') {
                $counters[] = $counter;
            }
        }

        return $counters;
    }

    /**
     * Build the Team Members repeater (About page) as a plain array for
     * Page::custom_data. Blank rows (no name and no role) are dropped. Same
     * double-encoding caveat as encodeStatsCounters() above.
     */
    private function encodeTeamMembers(Request $request): array
    {
        $members = [];

        foreach ($request->input('team_members', []) as $memberData) {
            $member = [
                'name'            => $memberData['name'] ?? '',
                'role'            => $memberData['role'] ?? '',
                'bio'             => $memberData['bio'] ?? '',
                'photo_image_id'  => $memberData['photo_image_id'] ?? null,
            ];

            if ($member['name'] !== '' || $member['role'] !== '') {
                $members[] = $member;
            }
        }

        return $members;
    }

    /**
     * Validation rules for the "Navigation Mega Menu" section shared by the
     * store() and update() methods.
     */
    protected function megaRules(): array
    {
        return app(\App\Services\NavigationMegaMenuService::class)->megaRules();
    }

    /**
     * Called after validation (before the DB transaction) to enforce the per-parent
     * item cap and duplicate-source guard. Only runs when the item is being enabled.
     */
    protected function assertMegaEnableable(Request $request): void
    {
        $mega = $request->input('mega_menu');

        if (! is_array($mega) || empty($mega['enabled'])) {
            return;
        }

        $source = $request->route('page');

        if (! $source) {
            return;
        }

        app(\App\Services\NavigationMegaMenuService::class)->assertCanEnable(
            (string) $mega['parent_key'],
            $source,
            app(\App\Services\NavigationMegaMenuService::class)->maxItemsPerMenu(),
        );
    }

    /**
     * Persist (or clear) the mega-menu entry for a page, inside the store/update DB
     * transaction so the page save and its menu row commit or roll back together.
     */
    protected function persistMega(Request $request, Page $page): void
    {
        $mega = $request->input('mega_menu');
        $actorId = $request->user()?->id;

        app(\App\Services\NavigationMegaMenuService::class)
            ->persistForSource($page, is_array($mega) ? $mega : null, $actorId);
    }
}