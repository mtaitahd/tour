<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OurStoryController extends Controller
{
    /**
     * The About page slug that owns the story section
     * (story_title heading + story_gallery images/captions).
     */
    private const ABOUT_PAGE_SLUG = 'about-us';

    public function edit(): View
    {
        $page = $this->aboutPage();

        return view('admin.story-gallery.edit', compact('page'));
    }

    /**
     * The About page that owns the story section, creating a missing placeholder
     * first. Live deploys sometimes lack the seeded about-us row, and without it
     * firstOrFail() turned /admin/our-story into a baffling 404 — so we bootstraps
     * the page instead of failing.
     */
    private function aboutPage(): Page
    {
        return Page::firstOrCreate(
            ['slug' => self::ABOUT_PAGE_SLUG],
            [
                'title'   => 'About Us',
                'content' => '',
                'status'  => 'published',
                'order'   => 10,
            ]
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $page = $this->aboutPage();

        $validated = $request->validate([
            'story_title'                 => ['nullable', 'string', 'max:255'],
            'story_gallery.*.image_id'    => ['nullable', 'integer'],
            'story_gallery.*.caption'     => ['nullable', 'string', 'max:255'],
            // Home page "About Us / Our Story" section — edited on this same
            // form so admins never have to jump to the settings page.
            'homepage_about'                     => ['nullable', 'array'],
            'homepage_about.eyebrow'             => ['nullable', 'string', 'max:100'],
            'homepage_about.title'               => ['nullable', 'string', 'max:255'],
            'homepage_about.text'                => ['nullable', 'string'],
            'homepage_about.checklist'           => ['nullable', 'string'],
            'homepage_about.btn_text'            => ['nullable', 'string', 'max:100'],
            'homepage_about.btn_link'            => ['nullable', 'string', 'max:500'],
            'homepage_about_polaroids'           => ['nullable', 'array'],
            'homepage_about_polaroids.*.src'     => ['nullable', 'string', 'max:1000'],
            'homepage_about_polaroids.*.caption' => ['nullable', 'string', 'max:100'],
        ]);

        // Build the Story Gallery array; blank rows (no image selected) are
        // dropped, same as PageController::encodeStoryGallery().
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

        $page->update([
            'story_title'   => $validated['story_title'] ?? '',
            'story_gallery' => $gallery,
        ]);

        // Full replace of the ordered image set, mirroring PageController::update().
        $storyGalleryIds = collect($gallery)->pluck('image_id')->filter()->values()->all();
        app(MediaLibraryService::class)->setOrderedUsages($page, 'story_gallery', $storyGalleryIds);

        // ── Home page "About Us / Our Story" section ──────────────────────
        $home = $request->input('homepage_about', []);

        Setting::set('home_about_eyebrow', trim((string) ($home['eyebrow'] ?? '')));
        Setting::set('home_about_title', trim((string) ($home['title'] ?? '')));
        Setting::set('home_about_text', trim((string) ($home['text'] ?? '')));
        Setting::set('home_about_checklist', trim((string) ($home['checklist'] ?? '')));
        Setting::set('home_about_btn_text', trim((string) ($home['btn_text'] ?? '')));
        Setting::set('home_about_btn_link', trim((string) ($home['btn_link'] ?? '')));

        // Polaroid photo stack. First three are shown on the home page; deleted
        // slots (blank src) are dropped, so removing all three clears the stack.
        $polaroids = [];
        foreach ($request->input('homepage_about_polaroids', []) as $item) {
            $src = trim((string) ($item['src'] ?? ''));
            if ($src !== '') {
                $polaroids[] = [
                    'src'     => $src,
                    'caption' => trim((string) ($item['caption'] ?? '')),
                ];
            }
        }
        Setting::set('home_about_polaroids', json_encode($polaroids));

        return redirect()->route('admin.our-story.edit')
                         ->with('success', 'Our Story images updated successfully!');
    }
}