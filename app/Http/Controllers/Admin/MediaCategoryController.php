<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaCategoryRequest;
use App\Models\MediaCategory;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaCategoryController extends Controller
{
    public function __construct(protected MediaLibraryService $mediaLibrary)
    {
    }

    public function index(): View
    {
        return view('admin.media.categories.index', [
            'categories' => $this->mediaLibrary->categoryTree(),
        ]);
    }

    public function create(): View
    {
        return view('admin.media.categories.create', [
            'categories' => $this->mediaLibrary->categoryTree(),
        ]);
    }

    public function store(StoreMediaCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        MediaCategory::create($validated);

        return redirect()
            ->route('admin.media.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(MediaCategory $category): View
    {
        return view('admin.media.categories.edit', [
            'category' => $category,
            'categories' => $this->mediaLibrary->categoryTree(),
        ]);
    }

    public function update(StoreMediaCategoryRequest $request, MediaCategory $category): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $category->update($validated);

        return redirect()
            ->route('admin.media.categories.index')
            ->with('success', 'Category updated.');
    }

    /**
     * Deletes the category itself, but never the media items in it — they simply lose
     * this category assignment (the media_category_media pivot row cascades on delete
     * per the Phase 2 migration; the underlying GalleryImage/media row is untouched).
     * Child categories cascade-delete too, per the same migration's parent_id foreign
     * key — confirmed and called out explicitly here since that's a real, if intentional,
     * destructive side effect worth knowing about before clicking delete.
     */
    public function destroy(MediaCategory $category): RedirectResponse
    {
        $childCount = $category->children()->count();

        $category->delete();

        $message = 'Category deleted.';
        if ($childCount > 0) {
            $message .= " {$childCount} subcategory(ies) were deleted with it.";
        }

        return redirect()
            ->route('admin.media.categories.index')
            ->with('success', $message);
    }
}
