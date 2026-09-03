<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCategory;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Str;

class TourCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TourCategory::withCount('tourPackages')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.tour-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.tour-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|unique:tour_categories,slug',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string|max:255',
            'no_robots'        => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['no_robots'] = $request->has('no_robots') ? 1 : 0;

        TourCategory::create($validated);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-categories.index')
                         ->with('success', 'Category created successfully');
    }

    public function edit(TourCategory $tourCategory)
    {
        return view('admin.tour-categories.edit', compact('tourCategory'));
    }

    public function update(Request $request, TourCategory $tourCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|unique:tour_categories,slug,' . $tourCategory->id,
            'description' => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string|max:255',
            'no_robots'        => 'boolean',

            // Freeform content sections — see TourCategorySection. Picker-only for the
            // image (no upload field at all, consistent with the rest of the app's
            // current direction for every image field).
            'sections'                       => 'nullable|array',
            'sections.*.title'               => 'nullable|string|max:255',
            'sections.*.content'             => 'nullable|string',
            'sections.*.existing_image_id'   => 'nullable|integer|exists:media,id',
            'sections.*.placement'           => 'nullable|in:above_grid,below_grid',
        ]);

        $validated['no_robots'] = $request->has('no_robots') ? 1 : 0;

        $tourCategory->update([
            'name'             => $validated['name'],
            'slug'             => $validated['slug'],
            'description'      => $validated['description'] ?? null,
            'order'            => $validated['order'] ?? 999,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords'    => $validated['meta_keywords'] ?? null,
            'no_robots'        => $validated['no_robots'],
        ]);

        $this->syncSections($tourCategory, $request->input('sections', []));

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-categories.index')
                         ->with('success', 'Category updated successfully');
    }

    /**
     * Replace this category's entire set of content sections with whatever the
     * repeater submitted — a full replace, not a diff, since the form always submits
     * the complete current state of the repeater (sections the admin removed in the
     * browser are simply absent from the submitted array). Mirrors
     * MediaLibraryService::setOrderedUsages()'s "delete then recreate" approach for
     * the same reason: the caller's submitted list is always authoritative.
     *
     * Each section's image usage is recorded/forgotten via MediaLibraryService, same
     * as every other direct-FK image field in the app (Page::hero_image_id, etc.), so
     * the Media Library's "used in N locations" / delete-protection stays accurate.
     */
    protected function syncSections(TourCategory $tourCategory, array $sectionsInput): void
    {
        $mediaLibrary = app(MediaLibraryService::class);

        // Forget usage for every section image this category had before this save,
        // regardless of whether it's kept — recordUsage() below re-adds it for
        // anything still present, and this avoids needing to diff old vs new by id.
        foreach ($tourCategory->sections as $existingSection) {
            if ($existingSection->image_id) {
                $mediaLibrary->forgetUsage($existingSection->image_id, $tourCategory, "section.{$existingSection->id}");
            }
        }

        $tourCategory->sections()->delete();

        foreach (array_values($sectionsInput) as $position => $sectionData) {
            $title = trim($sectionData['title'] ?? '');
            $content = trim($sectionData['content'] ?? '');
            $imageId = $sectionData['existing_image_id'] ?? null;

            // Skip a fully blank section (no title, no content, no image) rather
            // than save an empty row.
            if ($title === '' && $content === '' && empty($imageId)) {
                continue;
            }

            $section = $tourCategory->sections()->create([
                'title'      => $title,
                'content'    => $content,
                'image_id'   => $imageId ?: null,
                'placement'  => $sectionData['placement'] ?? 'below_grid',
                'order'      => $position,
            ]);

            if ($section->image_id) {
                $mediaLibrary->recordUsage($section->image_id, $tourCategory, "section.{$section->id}");
            }
        }
    }

    /**
     * Deletes the category itself, but never the tour packages assigned to it — they
     * simply lose this category assignment (the pivot row cascades on delete per the
     * migration; the tour package record itself is untouched). Same non-destructive
     * guarantee as MediaCategoryController/MediaTagController.
     */
    public function destroy(TourCategory $tourCategory)
    {
        $tourCategory->delete();

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-categories.index')
                         ->with('success', 'Category deleted');
    }
}
