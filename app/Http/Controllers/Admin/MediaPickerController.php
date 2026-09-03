<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaPickerSearchRequest;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;

/**
 * Backend for the reusable <x-media-picker> Bootstrap modal (built in Phase 8). This
 * controller only ever returns JSON — there's no full-page view here, since the picker
 * lives inside whatever admin page embeds it (Destination edit, TourPackage edit, etc.)
 * and fetches results via AJAX as the person searches/filters/paginates inside the modal.
 */
class MediaPickerController extends Controller
{
    public function __construct(protected MediaLibraryService $mediaLibrary)
    {
    }

    /**
     * Search/filter/paginate results for display inside the picker grid. Same
     * underlying query as the main Media Library page (MediaLibraryService::paginate()),
     * just shaped as compact JSON instead of a Blade view, and including each image's
     * usage count so the picker can show "used elsewhere" as a hint.
     */
    public function search(MediaPickerSearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $media = $this->mediaLibrary->paginate([
            'search' => $validated['search'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'tag_id' => $validated['tag_id'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
        ]);

        return response()->json([
            'images' => $media->getCollection()->map(fn ($image) => [
                'id' => $image->id,
                'name' => $image->name,
                'display_title' => $image->display_title,
                'thumb_url' => $image->getUrl('thumb-webp') ?: $image->getUrl('thumb') ?: $image->getUrl(),
                'preview_url' => $image->getUrl('medium-webp') ?: $image->getUrl(),
                'usage_count' => $image->usage_count,
                'alt_text' => $image->meta?->alt_text,
            ]),
            'pagination' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'total' => $media->total(),
            ],
            'categories' => $this->mediaLibrary->categoryTree(),
            'tags' => $this->mediaLibrary->allTagsWithCounts(),
        ]);
    }

    /**
     * Resolve a single image's display data by id — used when a picker needs to show
     * the currently-selected image (e.g. re-opening an edit form where hero_image_id
     * is already set) without running a full search.
     */
    public function show(int $mediaId): JsonResponse
    {
        $image = $this->mediaLibrary->query()->findOrFail($mediaId);

        return response()->json([
            'id' => $image->id,
            'name' => $image->name,
            'display_title' => $image->display_title,
            'thumb_url' => $image->getUrl('thumb-webp') ?: $image->getUrl(),
            'preview_url' => $image->getUrl('medium-webp') ?: $image->getUrl(),
            'usage_count' => $image->usage_count,
            'alt_text' => $image->meta?->alt_text,
        ]);
    }
}
