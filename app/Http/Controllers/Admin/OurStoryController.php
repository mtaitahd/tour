<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
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
        $page = Page::where('slug', self::ABOUT_PAGE_SLUG)->firstOrFail();

        return view('admin.story-gallery.edit', compact('page'));
    }

    public function update(Request $request): RedirectResponse
    {
        $page = Page::where('slug', self::ABOUT_PAGE_SLUG)->firstOrFail();

        $validated = $request->validate([
            'story_title'           => ['nullable', 'string', 'max:255'],
            'story_gallery.*.image_id' => ['nullable', 'integer'],
            'story_gallery.*.caption'  => ['nullable', 'string', 'max:255'],
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

        return redirect()->route('admin.our-story.edit')
                         ->with('success', 'Our Story images updated successfully!');
    }
}