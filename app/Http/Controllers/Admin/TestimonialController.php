<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Testimonial;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $testimonials = Testimonial::with(['tourPackage', 'destination'])
                                    ->orderBy('order')
                                    ->orderByDesc('created_at')
                                    ->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tourPackages = \App\Models\TourPackage::orderBy('title')->get();
        $destinations = \App\Models\Destination::orderBy('name')->get();

        return view('admin.testimonials.create', compact('tourPackages', 'destinations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $testimonial = Testimonial::create($validated);

        $this->syncAvatar($request, $testimonial);

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testimonial created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        $tourPackages = \App\Models\TourPackage::orderBy('title')->get();
        $destinations = \App\Models\Destination::orderBy('name')->get();

        return view('admin.testimonials.edit', compact('testimonial', 'tourPackages', 'destinations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $this->validated($request);

        $testimonial->update($validated);

        $this->syncAvatar($request, $testimonial);

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testimonial updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        app(MediaLibraryService::class)->forgetAllUsagesFor($testimonial);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
                         ->with('success', 'Testimonial deleted');
    }

    /**
     * Shared validation rules for store() and update().
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'location'         => 'nullable|string|max:255',
            'rating'           => 'required|integer|min:1|max:5',
            'content'          => 'required|string',
            'tour_package_id'  => 'nullable|integer|exists:tour_packages,id',
            'destination_id'   => 'nullable|integer|exists:destinations,id',
            'is_featured'      => 'boolean',
            'order'            => 'nullable|integer|min:0',
            'status'           => 'required|in:draft,published,archived',
            'avatar_image_id'  => 'nullable|integer|exists:media,id',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        return $validated;
    }

    /**
     * Avatar selection via the Media Library picker — same recordUsage/forgetUsage
     * pattern as AccommodationController's hero image handling.
     */
    private function syncAvatar(Request $request, Testimonial $testimonial): void
    {
        $mediaLibrary = app(MediaLibraryService::class);

        if ($testimonial->wasRecentlyCreated) {
            if ($request->filled('avatar_image_id')) {
                $newAvatar = GalleryImage::find($request->input('avatar_image_id'));
                if ($newAvatar) {
                    $mediaLibrary->recordUsage($newAvatar->id, $testimonial, 'avatar_image_id');
                }
            }
            return;
        }

        if (! $request->has('avatar_image_id')) {
            return;
        }

        $newAvatarId = $request->filled('avatar_image_id') ? (int) $request->input('avatar_image_id') : null;
        $previousAvatarId = (int) $testimonial->getOriginal('avatar_image_id') ?: null;

        if ($previousAvatarId === $newAvatarId) {
            return;
        }

        if ($previousAvatarId) {
            $previousAvatar = GalleryImage::find($previousAvatarId);
            if ($previousAvatar) {
                $mediaLibrary->forgetUsage($previousAvatar->id, $testimonial, 'avatar_image_id');
            }
        }

        if ($newAvatarId) {
            $newAvatar = GalleryImage::find($newAvatarId);
            if ($newAvatar) {
                $mediaLibrary->recordUsage($newAvatar->id, $testimonial, 'avatar_image_id');
            }
        }
    }
}
