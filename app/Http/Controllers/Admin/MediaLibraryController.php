<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\TourPackage;
use App\Services\ImageProcessorService;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The main admin Media Library controller: browse (grid/search/filter), view detail,
 * edit metadata/categories/tags, and delete.
 *
 * This replaces the original Admin\MediaController. That controller had two real bugs
 * (see Phase 1 analysis): a missing detach() method despite a route pointing at it, and
 * that same route being double-prefixed ('/admin/media/{media}/detach' registered
 * *inside* a group already prefixed with 'admin', resolving to
 * '/admin/admin/media/{media}/detach'). Both are fixed here by routes/web.php pointing
 * at this controller instead, with corrected paths — see the Phase 5 report section for
 * the full route list.
 */
class MediaLibraryController extends Controller
{
    /**
     * Short aliases accepted in detach() requests, mapped to real model classes. Keeps
     * the request body from ever naming an arbitrary PHP class to query against. Public
     * (not protected) because MediaUploadController also needs this same allow-list when
     * resolving an upload's target model — see Phase 5 report for why this lives here
     * rather than as a separate config/class: it's small, only used by these two Media
     * controllers, and keeping it next to detach() (its original reason for existing)
     * avoids a third tiny file for something this self-contained.
     */
    public const ALLOWED_USAGE_MODELS = [
        'destination' => Destination::class,
        'tour_package' => TourPackage::class,
        'page' => Page::class,
        'blog_post' => BlogPost::class,
    ];

    public function __construct(
        protected MediaLibraryService $mediaLibrary,
        protected ImageProcessorService $imageProcessor,
    ) {
    }

    /**
     * Reverse lookup: given a real model class name, return its short alias from
     * ALLOWED_USAGE_MODELS (e.g. Destination::class -> 'destination'). Used by the
     * detail view to build the hidden model_type field for the "unlink" form per usage
     * location, without inlining an array_search() call in Blade.
     */
    public static function aliasForModel(string $modelClass): ?string
    {
        return array_search($modelClass, self::ALLOWED_USAGE_MODELS, true) ?: null;
    }

    /**
     * Grid view with search/filter/sort — the brief's "Media Library Interface" /
     * "Search & Filters" requirements. Keeps the existing $media paginator variable
     * name so the current admin/media/index.blade.php view (Phase 1) keeps working
     * unmodified until Phase 7 replaces it with the full library UI.
     */
    public function index(Request $request): View
    {
        $media = $this->mediaLibrary->paginate([
            'search' => $request->query('search'),
            'category_id' => $request->query('category_id'),
            'tag_id' => $request->query('tag_id'),
            'uploaded_from' => $request->query('uploaded_from'),
            'uploaded_to' => $request->query('uploaded_to'),
            'sort' => $request->query('sort', 'newest'),
        ]);

        return view('admin.media.index', [
            'media' => $media,
            'categories' => $this->mediaLibrary->categoryTree(),
            'tags' => $this->mediaLibrary->allTagsWithCounts(),
            'stats' => $this->mediaLibrary->stats(),
            'filters' => $request->only(['search', 'category_id', 'tag_id', 'uploaded_from', 'uploaded_to', 'sort']),
        ]);
    }

    /**
     * Detail view — the brief's "Detail View" requirement: preview, title, alt text,
     * caption, description, file size, width, height, MIME type, upload date, usage
     * count and *locations* (not just a count).
     */
    public function show(GalleryImage $media): View
    {
        $this->authorize('view', $media);

        return view('admin.media.show', [
            'image' => $media->load(['meta', 'categories', 'tags', 'uploader']),
            'usageLocations' => $this->mediaLibrary->usageLocations($media->id),
            'categories' => $this->mediaLibrary->categoryTree(),
            'tags' => $this->mediaLibrary->allTagsWithCounts(),
        ]);
    }

    /**
     * Edit form — same data as show(), reused by the edit view.
     */
    public function edit(GalleryImage $media): View
    {
        $this->authorize('update', $media);

        return view('admin.media.edit', [
            'image' => $media->load(['meta', 'categories', 'tags', 'uploader']),
            'categories' => $this->mediaLibrary->categoryTree(),
            'tags' => $this->mediaLibrary->allTagsWithCounts(),
        ]);
    }

    /**
     * Save SEO metadata (title/alt text/caption/description) plus category and tag
     * assignments. Authorization is handled inside UpdateMediaRequest itself.
     */
    public function update(UpdateMediaRequest $request, GalleryImage $media): RedirectResponse
    {
        $validated = $request->validated();

        $media->meta()->updateOrCreate(
            ['media_id' => $media->id],
            [
                'title' => $validated['title'] ?? null,
                'alt_text' => $validated['alt_text'] ?? null,
                'caption' => $validated['caption'] ?? null,
                'description' => $validated['description'] ?? null,
            ]
        );

        $media->categories()->sync($validated['category_ids'] ?? []);
        $media->tags()->sync($validated['tag_ids'] ?? []);

        return redirect()
            ->route('admin.media.show', $media)
            ->with('success', 'Image details updated.');
    }

    /**
     * Permanently delete a media item — but only if it's not currently used anywhere,
     * per the brief's "Prevent deletion while in use" requirement. Uses
     * MediaLibraryService::deleteIfUnused() rather than calling delete() directly, so
     * the usage check happens before any file is touched.
     */
    public function destroy(GalleryImage $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        if (! $this->mediaLibrary->deleteIfUnused($media, $this->imageProcessor)) {
            $locations = collect($this->mediaLibrary->usageLocations($media->id))
                ->pluck('label')
                ->implode(', ');

            return redirect()
                ->route('admin.media.show', $media)
                ->with('error', "This image is still in use and can't be deleted. Used in: {$locations}");
        }

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Image deleted.');
    }

    /**
     * AJAX variant of destroy(), used by the grid view's inline delete button (matching
     * the original Admin\MediaController::destroy() JSON response shape, so the existing
     * front-end delete handler in admin/media/index.blade.php keeps working unmodified).
     */
    public function destroyAjax(GalleryImage $media)
    {
        $this->authorize('delete', $media);

        if (! $this->mediaLibrary->deleteIfUnused($media, $this->imageProcessor)) {
            return response()->json([
                'success' => false,
                'message' => 'This image is still in use and cannot be deleted.',
                'usages' => $this->mediaLibrary->usageLocations($media->id),
            ], 422);
        }

        return response()->json(['success' => true, 'message' => 'Image removed']);
    }

    /**
     * Remove a recorded usage without deleting the underlying image — i.e. "this image
     * is no longer used *here*, but keep it in the library." This is the method that
     * was missing from the original Admin\MediaController despite a route already
     * pointing at it (see Phase 1 analysis). The model/context to detach from are
     * passed in the request body, since a single media item can have several usages
     * and the caller needs to specify which one.
     */
    public function detach(Request $request, GalleryImage $media): RedirectResponse
    {
        $this->authorize('update', $media);

        $request->validate([
            'model_type' => ['required', 'string', Rule::in(array_keys(self::ALLOWED_USAGE_MODELS))],
            'model_id' => ['required', 'integer'],
            'context' => ['required', 'string'],
        ]);

        // model_type from the request is a short alias (e.g. 'destination'), never a raw
        // class string — letting a request body name an arbitrary PHP class to
        // ::find() against would be a security problem, so it's resolved through this
        // fixed allow-list instead.
        $modelClass = self::ALLOWED_USAGE_MODELS[$request->input('model_type')];
        $model = $modelClass::find($request->input('model_id'));

        if ($model) {
            $this->mediaLibrary->forgetUsage($media->id, $model, $request->input('context'));
        }

        return back()->with('success', 'Image unlinked from that location.');
    }
}
