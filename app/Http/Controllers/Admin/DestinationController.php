<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

        // The live database has repeatedly lagged behind the migrations that add
        // these columns. Without this guard the insert throws a QueryException and
        // Laravel returns a bare 500 into the modal iframe: the spinner stops, the
        // modal stays open, and the admin is told nothing. Fail with a real message.
        if ($missing = $this->missingColumns(['faqs', 'reviews_embed'])) {
            return $this->missingColumnsResponse($missing);
        }

        // Drop FAQ rows where both fields were left blank (e.g. an added-then-unused
        // repeater row), same convention as tour_packages.
        if (!empty($validated['faqs'])) {
            $validated['faqs'] = array_values(array_filter($validated['faqs'], function ($faq) {
                return !empty($faq['question']) || !empty($faq['answer']);
            }));
        }

        if (empty($validated['slug'])) {
            // Validation above only guards a slug the admin typed; a blank slug is
            // generated here, *after* validation, so a name that collides with an
            // existing destination used to reach the unique index and 500. Generate
            // a guaranteed-unique slug instead (serengeti, serengeti-1, ...).
            $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']));
        }

        try {
            Destination::create($validated);
        } catch (QueryException $e) {
            Log::error('Failed to create destination', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', $this->describeSaveFailure($e));
        }

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

        // Same stale-schema protection as store(): the update writes these columns too,
        // and a QueryException here would also surface as a silent 500 in the modal.
        if ($missing = $this->missingColumns(['faqs', 'reviews_embed', 'hero_image_id'])) {
            return $this->missingColumnsResponse($missing);
        }

        try {
            $destination->update($validated);
        } catch (QueryException $e) {
            Log::error('Failed to update destination', [
                'destination_id' => $destination->id,
                'error'          => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $this->describeSaveFailure($e));
        }

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

    /**
     * Which of the given destinations columns don't exist in the database yet.
     *
     * @param  string[]  $columns
     * @return string[]
     */
    private function missingColumns(array $columns): array
    {
        return array_values(array_filter(
            $columns,
            fn (string $column) => !Schema::hasColumn('destinations', $column)
        ));
    }

    private function missingColumnsResponse(array $missing)
    {
        return back()->withInput()->with(
            'error',
            'Destination could not be saved: the database is missing the '
            . implode(', ', $missing)
            . ' column(s). Run "php artisan migrate --force" on the server, then try again.'
        );
    }

    /**
     * A slug built from the name that is guaranteed not to collide with an
     * existing destination (serengeti, serengeti-1, serengeti-2, ...).
     */
    private function uniqueSlug(string $base): string
    {
        $base = $base !== '' ? $base : 'destination';
        $slug = $base;
        $suffix = 1;

        while (Destination::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    /**
     * Turn a save QueryException into something the admin can act on. A raw "500"
     * in the modal iframe told them nothing, so recognise the common constraint
     * violations and otherwise surface the driver message (the full trace is also
     * written to storage/logs/laravel.log).
     */
    private function describeSaveFailure(QueryException $e): string
    {
        $message = $e->getMessage();

        if ($e->getCode() === '23000' && str_contains($message, 'Duplicate entry')) {
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $message, $m)) {
                return "The destination could not be saved: \"{$m[1]}\" already exists, so it "
                     . 'must be unique. Change the name, or set a different slug.';
            }

            return 'The destination could not be saved: a value that must be unique already exists.';
        }

        return 'The destination could not be saved because of a database error: '
             . Str::limit($message, 300);
    }
}
