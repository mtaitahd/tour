<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Str;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $destinations = Destination::orderBy('order')->orderBy('name')->paginate(20);
        return view('admin.destinations.index', compact('destinations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.destinations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|unique:destinations,slug',
            'country_code'     => 'required|in:TZ,KE,UG,RW',
            'type'             => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'faqs'             => 'nullable|array',
            'faqs.*.question'  => 'nullable|string|max:500',
            'faqs.*.answer'    => 'nullable|string',
            'reviews_embed'    => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'is_featured'      => 'boolean',
        ]);

        // Drop FAQ rows where both fields were left blank (e.g. an added-then-unused
        // repeater row), same convention as tour_packages.
        if (!empty($validated['faqs'])) {
            $validated['faqs'] = array_values(array_filter($validated['faqs'], function ($faq) {
                return !empty($faq['question']) || !empty($faq['answer']);
            }));
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Destination::create($validated);

        return redirect()->route('admin.destinations.index')
                         ->with('success', 'Destination created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destination $destination)
    {
        return view('admin.destinations.edit', compact('destination'));
    }

    public function update(Request $request, Destination $destination)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:destinations,slug,' . $destination->id,
            'country_code'     => 'required|in:TZ,KE,UG,RW',
            'type'             => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'faqs'             => 'nullable|array',
            'faqs.*.question'  => 'nullable|string|max:500',
            'faqs.*.answer'    => 'nullable|string',
            'reviews_embed'    => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'is_featured'      => 'boolean',
            'order'            => 'nullable|integer|min:0',
            'hero_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            // Media Library picker path — selecting an existing image sets this instead
            // of (or in addition to) uploading a new file via hero_image above. Both
            // paths are validated so a person can use either one on any given save.
            'hero_image_id'    => 'nullable|integer|exists:media,id',
            // Gallery is now picker-only (direct upload removed per decision) — an
            // ordered array of media ids from the multi-select picker.
            'gallery_image_ids'    => 'nullable|array',
            'gallery_image_ids.*'  => 'integer|exists:media,id',
        ]);

        // Drop FAQ rows where both fields were left blank (e.g. an added-then-unused
        // repeater row), same convention as tour_packages.
        if (!empty($validated['faqs'])) {
            $validated['faqs'] = array_values(array_filter($validated['faqs'], function ($faq) {
                return !empty($faq['question']) || !empty($faq['answer']);
            }));
        }

        $validated['is_featured'] = $request->has('is_featured');

        $destination->update($validated);

        // Media Library hero selection — additive alongside the existing direct-upload
        // path below. If both hero_image_id and a new hero_image file are submitted in
        // the same request (shouldn't normally happen from the UI, but the controller
        // shouldn't assume), the freshly uploaded file wins, since it's the more
        // specific, more recent action — handled by simply letting the upload block
        // below run after this one and overwrite hero_image_id via its own path.
        if ($request->filled('hero_image_id')) {
            $mediaLibrary = app(MediaLibraryService::class);

            // Forget the previous hero's usage record before recording the new one,
            // so an old, no-longer-used hero doesn't stay marked "in use" forever and
            // block deletion incorrectly.
            if ($destination->getOriginal('hero_image_id')) {
                $previousHero = GalleryImage::find($destination->getOriginal('hero_image_id'));
                if ($previousHero) {
                    $mediaLibrary->forgetUsage($previousHero->id, $destination, 'hero_image_id');
                }
            }

            $newHero = GalleryImage::find($validated['hero_image_id']);
            if ($newHero) {
                $mediaLibrary->recordUsage($newHero->id, $destination, 'hero_image_id');
            }
        }

        // Handle hero deletion
        if ($request->input('delete_hero') == '1') {
            $destination->clearMediaCollection('hero');
        }

        // Handle hero upload (only if new file)
        if ($request->hasFile('hero_image')) {
            $destination->clearMediaCollection('hero'); // optional: replace old
            $destination->addMediaFromRequest('hero_image')->toMediaCollection('hero');
        }

        // Gallery — picker-only (direct upload, manual reorder inputs, and the
        // detach-to-'unlinked'-collection workaround all removed; this single call
        // replaces all of that). setOrderedUsages() does a full replace each save,
        // which is correct here since the picker component always submits the
        // complete, current gallery state, not a delta — array_values() ensures a
        // clean sequential list even if the submitted array has gaps from removed
        // items in the browser.
        app(MediaLibraryService::class)->setOrderedUsages(
            $destination,
            'gallery',
            array_values($validated['gallery_image_ids'] ?? [])
        );

        return redirect()->route('admin.destinations.index')
                         ->with('success', 'Destination updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Destination $destination)
    {
        app(MediaLibraryService::class)->forgetAllUsagesFor($destination);
        $destination->delete();

        return redirect()->route('admin.destinations.index')
                         ->with('success', 'Destination deleted successfully!');
    }
}
