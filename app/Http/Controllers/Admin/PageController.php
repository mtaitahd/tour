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
        // Site Information pages (About Us, Why Choose Us, Contact Us, Terms and
        // Conditions) are managed on their own screen — see SitePageController —
        // so they are kept out of this list to avoid two competing edit paths for
        // the same record.
        $pages = Page::whereNotIn('slug', Page::siteInfoSlugs())
                     ->orderBy('order')
                     ->orderBy('title')
                     ->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $draftId = request()->session()->getOldInput('draft_id') ?: request()->query('draft_id');
        $draft = $draftId ? Page::where('status', 'draft')->find($draftId) : null;

        if ($draft && request()->query->has('draft_id')) {
            $draftInput = $draft->draft_payload ?? [];
            unset($draftInput['_page_autosave_new']);
            $draftInput['draft_id'] = $draft->getKey();
            $draftInput['status'] = 'draft';
            request()->session()->flashInput(array_merge($draftInput, request()->session()->getOldInput()));
        }

        return view('admin.pages.create', compact('draft'));
    }

    /** Save an incomplete page form without publishing or overwriting page content. */
    public function autosaveDraft(Request $request)
    {
        $request->validate([
            'draft_id' => 'nullable|integer|exists:pages,id',
            'title'    => 'nullable|string|max:255',
            'slug'     => 'nullable|string|max:255',
        ]);

        $page = null;
        if ($request->filled('draft_id')) {
            $page = Page::whereNotIn('slug', Page::siteInfoSlugs())->findOrFail($request->integer('draft_id'));
        }

        $payload = $this->jsonSafe($request->except(['_token', '_method', 'draft_id']));
        if (json_encode($payload) === false) {
            return response()->json(['message' => 'This page content could not be saved. Remove unusual pasted characters and try again.'], 422);
        }

        $title = trim((string) $request->input('title', ''));
        $slug = Str::slug((string) $request->input('slug', '')) ?: Str::slug($title);
        if ($slug === '') {
            $slug = 'page-draft-' . Str::lower(Str::random(10));
        }

        $isNewDraft = ! $page || (bool) data_get($page->draft_payload, '_page_autosave_new', false);
        if ($isNewDraft) {
            $baseSlug = $slug;
            $suffix = 1;
            while (Page::where('slug', $slug)->when($page, fn ($query) => $query->whereKeyNot($page->getKey()))->exists()) {
                $slug = $baseSlug . '-' . $suffix++;
            }
            $payload['_page_autosave_new'] = true;
        }

        $page ??= new Page();
        if (! $page->exists) {
            $page->status = 'draft';
            $page->order = 999;
        }
        if ($isNewDraft) {
            $page->title = $title !== '' ? $title : 'Untitled page draft';
            $page->slug = $slug;
        }
        $page->draft_payload = $payload;
        $page->save();

        return response()->json([
            'draft_id' => $page->getKey(),
            'slug' => $page->slug,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    private function jsonSafe(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->jsonSafe($item), $value);
        }

        return ! is_string($value) || mb_check_encoding($value, 'UTF-8')
            ? $value
            : mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    public function store(Request $request, ?string $redirectTo = null)
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
        'story_gallery.*.image_id'     => 'nullable|integer|exists:media,id',
        'story_gallery.*.caption'      => 'nullable|string|max:255',
    ]);

    // Auto-generate slug if empty
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['stats_counters'] = $this->encodeStatsCounters($request);
        $validated['custom_data'] = $this->encodeTeamMembers($request);
        $validated['story_gallery'] = $this->encodeStoryGallery($request);
        $validated['no_robots'] = $request->boolean('no_robots');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $redirectTo) {
        // Create the page
        $page = Page::create($validated);

        // Picker-only: images come from the Media Library (hero_image_id /
        // story_image_id below), never from a device upload, so the old
        // addMediaFromRequest branches for 'hero_image' and 'story_image' are gone.
        // Pages created before the picker keep their legacy 'hero'/'story' media
        // untouched.

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

        // Our Story gallery images — same ordered-set mechanism (context: story_gallery).
        $storyGalleryIds = collect($page->story_gallery ?? [])->pluck('image_id')->filter()->values()->all();
        $mediaLibrary->setOrderedUsages($page, 'story_gallery', $storyGalleryIds);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route($redirectTo ?? 'admin.pages.index')
                         ->with('success', 'Page created successfully!');
        });
    }

    public function edit(Page $page)
    {
        // A site-information page has its own editor. Send anyone who reaches it
        // through the generic pages route to that screen, so there is only ever
        // one place to edit it from.
        if ($page->isSiteInfo()) {
            return redirect()->route('admin.site-pages.index');
        }

        if ($page->draft_payload) {
            $draftInput = $page->draft_payload;
            unset($draftInput['_page_autosave_new']);
            $draftInput['draft_id'] = $page->getKey();
            request()->session()->flashInput(array_merge($draftInput, request()->session()->getOldInput()));
        }

        return view('admin.pages.edit', compact('page'));
    }

    /**
     * @param string|null $redirectTo Route name to return to after a successful
     *                                save. Null = the generic pages list, which
     *                                is what the resource route passes. The Site
     *                                Information screen passes its own name so the
     *                                user lands back on the screen they started
     *                                from rather than an empty list.
     */
    public function update(Request $request, Page $page, ?string $redirectTo = null)
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
        'story_gallery.*.image_id'     => 'nullable|integer|exists:media,id',
        'story_gallery.*.caption'      => 'nullable|string|max:255',
    ]);

    $validated['stats_counters'] = $this->encodeStatsCounters($request);
    $validated['custom_data'] = $this->encodeTeamMembers($request);
    $validated['story_gallery'] = $this->encodeStoryGallery($request);
    $validated['no_robots'] = $request->boolean('no_robots');
    $validated['draft_payload'] = null;

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

        // Hero / story images – picker-only: see the matching note in store().

        // Team member photos — full replace of the ordered set, same as store().
        $teamPhotoIds = collect($page->custom_data ?? [])->pluck('photo_image_id')->filter()->values()->all();
        $mediaLibrary->setOrderedUsages($page, 'team_photo', $teamPhotoIds);

        // Our Story gallery images — full replace of the ordered set, same as store().
        $storyGalleryIds = collect($page->story_gallery ?? [])->pluck('image_id')->filter()->values()->all();
        $mediaLibrary->setOrderedUsages($page, 'story_gallery', $storyGalleryIds);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route($redirectTo ?? 'admin.pages.index')
                         ->with('success', 'Page updated successfully!');
        });
    }

    
    public function destroy(Page $page)
    {
        // Site Information pages are load-bearing: they are linked from the
        // header/footer and are the reason the Site Information screen exists.
        // Deleting one would 404 every link to it and leave a permanent hole in
        // that screen, so they can only be unpublished, never deleted.
        if ($page->isSiteInfo()) {
            return redirect()->route('admin.site-pages.index')
                             ->with('error', 'Site Information pages cannot be deleted. Set the page to Draft to take it off the site.');
        }

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
     * Build the Story Gallery repeater (About page) as a plain array for
     * Page::story_gallery. Blank rows (no image selected) are dropped. Same
     * double-encoding caveat as encodeStatsCounters() above.
     */
    private function encodeStoryGallery(Request $request): array
    {
        $gallery = [];

        foreach ($request->input('story_gallery', []) as $itemData) {
            if (empty($itemData['image_id'])) {
                continue;
            }

            $gallery[] = [
                'image_id' => (int) $itemData['image_id'],
                'caption'  => $itemData['caption'] ?? '',
            ];
        }

        return $gallery;
    }
}
