<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaTagRequest;
use App\Models\MediaTag;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaTagController extends Controller
{
    public function __construct(protected MediaLibraryService $mediaLibrary)
    {
    }

    public function index(): View
    {
        return view('admin.media.tags.index', [
            'tags' => $this->mediaLibrary->allTagsWithCounts(),
        ]);
    }

    public function store(StoreMediaTagRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        MediaTag::create($validated);

        return redirect()
            ->route('admin.media.tags.index')
            ->with('success', 'Tag created.');
    }

    public function update(StoreMediaTagRequest $request, MediaTag $tag): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $tag->update($validated);

        return redirect()
            ->route('admin.media.tags.index')
            ->with('success', 'Tag updated.');
    }

    /**
     * Deletes the tag itself, but never the media items tagged with it — they simply
     * lose this tag (media_tag_media pivot row cascades per the Phase 2 migration; the
     * underlying media row is untouched).
     */
    public function destroy(MediaTag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()
            ->route('admin.media.tags.index')
            ->with('success', 'Tag deleted.');
    }
}
