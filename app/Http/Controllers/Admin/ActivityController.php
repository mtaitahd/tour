<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Str;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::orderBy('order')->orderBy('name')->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        // slug is derived automatically from name via Activity::booted(), so it's
        // deliberately not part of $validated / mass-assigned here.
        $activity = Activity::create($validated);

        $this->syncHero($request, $activity);

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        return view('admin.activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $validated = $this->validated($request);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        $activity->update($validated);

        $this->syncHero($request, $activity);

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        app(MediaLibraryService::class)->forgetAllUsagesFor($activity);
        $activity->delete();

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity deleted');
    }

    /**
     * Shared validation rules for store() and update().
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'icon'            => 'nullable|string|max:100',
            'order'           => 'nullable|integer|min:0',
            'is_featured'     => 'boolean',
            'is_active'       => 'boolean',
            'hero_image_id'   => 'nullable|integer|exists:media,id',
        ]);
    }

    /**
     * Hero image selection via the Media Library picker — same recordUsage/
     * forgetUsage pattern as Accommodation/Testimonial.
     */
    private function syncHero(Request $request, Activity $activity): void
    {
        $mediaLibrary = app(MediaLibraryService::class);

        if ($activity->wasRecentlyCreated) {
            if ($request->filled('hero_image_id')) {
                $newHero = GalleryImage::find($request->input('hero_image_id'));
                if ($newHero) {
                    $mediaLibrary->recordUsage($newHero->id, $activity, 'hero_image_id');
                }
            }
            return;
        }

        if (! $request->has('hero_image_id')) {
            return;
        }

        $newHeroId = $request->filled('hero_image_id') ? (int) $request->input('hero_image_id') : null;
        $previousHeroId = (int) $activity->getOriginal('hero_image_id') ?: null;

        if ($previousHeroId === $newHeroId) {
            return;
        }

        if ($previousHeroId) {
            $previousHero = GalleryImage::find($previousHeroId);
            if ($previousHero) {
                $mediaLibrary->forgetUsage($previousHero->id, $activity, 'hero_image_id');
            }
        }

        if ($newHeroId) {
            $newHero = GalleryImage::find($newHeroId);
            if ($newHero) {
                $mediaLibrary->recordUsage($newHero->id, $activity, 'hero_image_id');
            }
        }
    }
}
