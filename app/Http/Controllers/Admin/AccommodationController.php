<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Str;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accommodations = Accommodation::with('destination')
                                        ->orderBy('order')
                                        ->orderBy('name')
                                        ->paginate(20);

        return view('admin.accommodations.index', compact('accommodations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $destinations = \App\Models\Destination::orderBy('name')->get();
        return view('admin.accommodations.create', compact('destinations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['amenities'] = $this->parseAmenities($request->input('amenities_raw'));
        $validated['is_featured'] = $request->has('is_featured');

        $accommodation = Accommodation::create($validated);

        $this->syncMedia($request, $accommodation);

        return redirect()->route('admin.accommodations.index')
                         ->with('success', 'Accommodation created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Accommodation $accommodation)
    {
        $destinations = \App\Models\Destination::orderBy('name')->get();
        return view('admin.accommodations.edit', compact('accommodation', 'destinations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Accommodation $accommodation)
    {
        $validated = $this->validated($request, $accommodation->id);

        $validated['amenities'] = $this->parseAmenities($request->input('amenities_raw'));
        $validated['is_featured'] = $request->has('is_featured');

        $accommodation->update($validated);

        $this->syncMedia($request, $accommodation);

        return redirect()->route('admin.accommodations.index')
                         ->with('success', 'Accommodation updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $accommodation)
    {
        app(MediaLibraryService::class)->forgetAllUsagesFor($accommodation);
        $accommodation->delete();

        return redirect()->route('admin.accommodations.index')
                         ->with('success', 'Accommodation deleted');
    }

    /**
     * Shared validation rules for store() and update().
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => $ignoreId
                                    ? 'required|string|max:255|unique:accommodations,slug,' . $ignoreId
                                    : 'nullable|unique:accommodations,slug',
            'tier'             => 'nullable|string|max:100',
            'destination_id'   => 'nullable|integer|exists:destinations,id',
            'location'         => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'price_from'       => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|max:3',
            'is_featured'      => 'boolean',
            'order'            => 'nullable|integer|min:0',
            'status'           => 'required|in:draft,published,archived',
            'hero_image_id'    => 'nullable|integer|exists:media,id',
            'gallery_image_ids'    => 'nullable|array',
            'gallery_image_ids.*'  => 'integer|exists:media,id',
        ]);
    }

    /**
     * Amenities are entered as one-per-line free text in the admin form (simplest
     * possible input for a list that's just display copy, no structure needed beyond
     * "a list of strings") and stored as a JSON array.
     */
    private function parseAmenities(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }

    /**
     * Hero + gallery selection via the Media Library picker — same recordUsage /
     * forgetUsage / setOrderedUsages pattern as DestinationController::update().
     */
    private function syncMedia(Request $request, Accommodation $accommodation): void
    {
        $mediaLibrary = app(MediaLibraryService::class);

        // On a freshly created record, getOriginal() already equals the current
        // attributes (nothing to diff against), so the usage must always be recorded
        // rather than compared — there's no "previous" hero to forget yet.
        if ($accommodation->wasRecentlyCreated) {
            if ($request->filled('hero_image_id')) {
                $newHero = GalleryImage::find($request->input('hero_image_id'));
                if ($newHero) {
                    $mediaLibrary->recordUsage($newHero->id, $accommodation, 'hero_image_id');
                }
            }
        } elseif ($request->has('hero_image_id')) {
            $newHeroId = $request->filled('hero_image_id') ? (int) $request->input('hero_image_id') : null;
            $previousHeroId = (int) $accommodation->getOriginal('hero_image_id') ?: null;

            if ($previousHeroId !== $newHeroId) {
                if ($previousHeroId) {
                    $previousHero = GalleryImage::find($previousHeroId);
                    if ($previousHero) {
                        $mediaLibrary->forgetUsage($previousHero->id, $accommodation, 'hero_image_id');
                    }
                }

                if ($newHeroId) {
                    $newHero = GalleryImage::find($newHeroId);
                    if ($newHero) {
                        $mediaLibrary->recordUsage($newHero->id, $accommodation, 'hero_image_id');
                    }
                }
            }
        }

        $mediaLibrary->setOrderedUsages(
            $accommodation,
            'gallery',
            array_values($request->input('gallery_image_ids', []))
        );
    }
}
