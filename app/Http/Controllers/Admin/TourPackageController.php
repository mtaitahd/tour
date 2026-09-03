<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Pricing\PackageDurationType;
use App\Pricing\PricingValidationException;
use App\Services\PricingPersistenceService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TourPackageController extends Controller
{
    public function index()
    {
        $tourPackages = TourPackage::orderBy('title')->get();
        return view('admin.tour-packages.index', compact('tourPackages'));
    }

    public function create()
    {
        return view('admin.tour-packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                                         => 'required|string|max:255',
            'slug'                                          => 'nullable|string|unique:tour_packages,slug',
            'duration_days'                                 => 'nullable|integer|min:1',
            'video_url'                                     => 'nullable|string|max:500',
            'embed_map'                                     => 'nullable|string',
            'physical_rating'                               => 'nullable|in:relaxing,easy,moderate,complex,super_complex',
            'tour_level'                                    => 'nullable|in:budget_camping,budget_lodge,mid_range,luxury',
            'overview'                                      => 'nullable|string',
            'highlights_text'                               => 'nullable|string',
            'status'                                        => 'required|in:draft,published,archived',
            'is_featured'                                   => 'boolean',
            'is_group_departure'                            => 'boolean',
            'starting_point'                                => 'nullable|string|max:255',
            'ending_point'                                  => 'nullable|string|max:255',
            'meta_title'                                    => 'nullable|string|max:255',
            'meta_description'                              => 'nullable|string',
            'meta_keywords'                                 => 'nullable|string|max:255',
            'hero_image'                                    => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'hero_image_id'                                 => 'nullable|integer|exists:media,id',
            'gallery_image_ids'                             => 'nullable|array',
            'gallery_image_ids.*'                           => 'integer|exists:media,id',
            'safari_car_image_ids'                          => 'nullable|array',
            'safari_car_image_ids.*'                        => 'integer|exists:media,id',
            'itinerary_days'                                => 'nullable|array',
            'itinerary_days.*.title'                        => 'nullable|string|max:255',
            'itinerary_days.*.description'                  => 'nullable|string',
            'itinerary_days.*.existing_image_ids'            => 'nullable|array',
            'itinerary_days.*.existing_image_ids.*'          => 'integer|exists:media,id',
            'itinerary_days.*.meals'                        => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_silver'    => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_gold'      => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_platinum'  => 'nullable|string|max:255',
            'itinerary_days.*.existing_accommodation_image_silver'   => 'nullable|integer|exists:media,id',
            'itinerary_days.*.existing_accommodation_image_gold'     => 'nullable|integer|exists:media,id',
            'itinerary_days.*.existing_accommodation_image_platinum' => 'nullable|integer|exists:media,id',
            'itinerary_days.*.accommodations'               => 'nullable|array',
            'itinerary_days.*.accommodations.*.type'        => 'nullable|string|in:SILVER,GOLD,PLATINUM',
            'itinerary_days.*.accommodations.*.name'        => 'nullable|string|max:255',
            'itinerary_days.*.location_name'                => ['nullable', 'string', 'max:255'],
            'itinerary_days.*.lat'                          => ['nullable', 'numeric', 'between:-90,90'],
            'itinerary_days.*.lng'                          => ['nullable', 'numeric', 'between:-180,180'],
            'inclusions_items'                              => 'nullable|array',
            'exclusions_items'                              => 'nullable|array',
            'extra_sections'                                => 'nullable|array',
            'extra_sections.*.title'                        => 'nullable|string|max:255',
            'extra_sections.*.content'                      => 'nullable|string',
            'extra_sections.*.existing_image_id'        => 'nullable|integer|exists:media,id',
            'extra_sections.*.image_side'                   => 'nullable|in:left,right',
            'faqs'                                          => 'nullable|array',
            'faqs.*.question'                               => 'nullable|string',
            'faqs.*.answer'                                 => 'nullable|string',
'detail.title'                                  => 'nullable|string|max:255',
            'detail.description'                            => 'nullable|string',
            // ── Phase 2 Pricing ────────────────────────────────────────────────
            'pricing_source'                                 => 'required|in:none,manual,calculator',
            'package_duration_type'                          => 'sometimes|nullable|in:single_day,multi_day',
            'tour_type'                                      => 'required_if:pricing_source,calculator|nullable|in:KILIMANJARO,SAFARI',
            'package_category'                               => 'nullable|in:LUXURY,MID_RANGE,BUDGET',
            'calculator_payload'                             => 'required_if:pricing_source,calculator|json',
            'manual_prices'                                  => 'required_if:pricing_source,manual|array|max:6',
            'manual_prices.*.level_key'                      => 'required|string',
            'manual_prices.*.season_code'                    => 'required|in:HIGH,LOW_WET',
            'manual_prices.*.price_2p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_prices.*.price_4p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_prices.*.price_6p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_currency'                                => 'required_if:pricing_source,manual|nullable|regex:/^[A-Za-z]{3}$/',
            // ── Navigation Mega Menu ─────────────────────────────────────────────
            ...$this->megaRules(),
        ]);

        $this->assertMegaEnableable($request);

        return DB::transaction(function () use ($request) {

        // ── Slug ──────────────────────────────────────────────────────────────
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
        $original = $slug;
        $count = 1;
        while (TourPackage::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        // ── Season Pricing ────────────────────────────────────────────────────
        $seasonPricing = [];
        for ($i = 1; $i <= 3; $i++) {
            $seasonName = $request->input("season.$i");
            if ($seasonName) {
                $seasonPricing[] = [
                    'season'   => $seasonName,
                    'price_2p' => $request->input("season_{$i}_2p"),
                    'price_4p' => $request->input("season_{$i}_4p"),
                    'price_6p' => $request->input("season_{$i}_6p"),
                ];
            }
        }

        // ── Highlights ────────────────────────────────────────────────────────
        $highlights = $request->filled('highlights_text')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->highlights_text))))
            : null;

        // ── Inclusions & Exclusions ───────────────────────────────────────────
        $inclusions = $request->filled('inclusions_items')
            ? array_values(array_filter(array_map('trim', $request->inclusions_items)))
            : null;

        $exclusions = $request->filled('exclusions_items')
            ? array_values(array_filter(array_map('trim', $request->exclusions_items)))
            : null;

        // ── FAQs ──────────────────────────────────────────────────────────────
        $faqs = [];
        if ($request->filled('faqs')) {
            foreach ($request->faqs as $faq) {
                if (!empty($faq['question']) || !empty($faq['answer'])) {
                    $faqs[] = [
                        'question' => trim($faq['question'] ?? ''),
                        'answer'   => trim($faq['answer'] ?? ''),
                    ];
                }
            }
        }

        // ── Trip Details ──────────────────────────────────────────────────────
        // FIX: default to an empty JSON object so the NOT NULL column is never null
        $tripDetails = json_encode(['title' => '', 'description' => '']);
        $detailInput = $request->input('detail');
        if (!empty($detailInput['title']) || !empty($detailInput['description'])) {
            $tripDetails = json_encode([
                'title'       => trim($detailInput['title'] ?? ''),
                'description' => trim($detailInput['description'] ?? ''),
            ]);
        }

        // ── Itinerary ─────────────────────────────────────────────────────────
        $itinerary = [];
        if ($request->filled('itinerary_days')) {
            foreach ($request->itinerary_days as $dayData) {
                if (!empty($dayData['title']) || !empty($dayData['description'])) {
                    $dayEntry = [
                        'title'          => $dayData['title'] ?? '',
                        'description'    => $dayData['description'] ?? '',
                        'accommodations' => $this->normalizeAccommodationTiers($dayData),
                        'meals'          => $dayData['meals'] ?? '',
                        // Read from the picker's selection (existing_image_ids), same
                        // as update() — previously hardcoded to an empty array here
                        // and only populated later by the now-removed file-upload
                        // block, which would have silently dropped any day images
                        // selected on the create form once upload was removed.
                        'image_ids'      => array_values($dayData['existing_image_ids'] ?? []),
                    ];
                    $lat = $this->cleanCoordinate($dayData['lat'] ?? null, -90, 90);
                    $lng = $this->cleanCoordinate($dayData['lng'] ?? null, -180, 180);
                    if ($lat !== null && $lng !== null) {
                        $dayEntry['lat'] = $lat;
                        $dayEntry['lng'] = $lng;
                        $dayEntry['location_name'] = $dayData['location_name'] ?? '';
                    }
                    $itinerary[] = $dayEntry;
                }
            }
        }

        // ── Create Model ──────────────────────────────────────────────────────
        $tourPackage = TourPackage::create([
            'slug'               => $slug,
            'title'              => $request->title,
            'duration_days'      => $request->duration_days,
            'video_url'          => $request->video_url,
            'embed_map'          => $request->embed_map,
            'base_price'         => null,
            'season_pricing'     => !empty($seasonPricing) ? $seasonPricing : null,
            'currency'           => $request->input('currency', 'USD'),
            'physical_rating'    => $request->input('physical_rating', 'moderate'),
            'tour_level'         => $request->input('tour_level', 'mid_range'),
            'is_group_departure' => $request->has('is_group_departure') ? 1 : 0,
            'starting_point'     => $request->starting_point,
            'ending_point'       => $request->ending_point,
            'overview'           => $request->overview,
            'highlights'         => $highlights,
            'inclusions'         => $inclusions,
            'exclusions'         => $exclusions,
            'itinerary'          => !empty($itinerary) ? $itinerary : null,
            'faqs'               => !empty($faqs) ? $faqs : null,
            'trip_details'       => $tripDetails,
            'meta_title'         => $request->meta_title,
            'meta_description'   => $request->meta_description,
            'meta_keywords'      => $request->meta_keywords,
'status'             => $request->status,
            'is_featured'        => $request->has('is_featured') ? 1 : 0,
            'no_robots'          => $request->has('no_robots') ? 1 : 0,
            'hero_image_id'      => $request->input('hero_image_id'),
            'package_duration_type' => $request->input('package_duration_type') ?: null,
            'tour_type'            => $request->input('tour_type') ? strtoupper($request->input('tour_type')) : null,
            'package_category'     => $request->input('package_category') ?: null,
            'pricing_source'       => $request->input('pricing_source', 'none'),
        ]);

        // ── Hero Image ────────────────────────────────────────────────────────
        if ($request->hasFile('hero_image')) {
            $tourPackage->clearMediaCollection('hero');
            $tourPackage->addMediaFromRequest('hero_image')
                        ->toMediaCollection('hero', 'public');
        }

        // Media Library selection, on create — no previous usage to forget here.
        if ($request->filled('hero_image_id')) {
            $heroImage = \App\Models\GalleryImage::find($request->input('hero_image_id'));
            if ($heroImage) {
                app(\App\Services\MediaLibraryService::class)->recordUsage($heroImage->id, $tourPackage, 'hero_image_id');
            }
        }

        // ── Gallery Images ────────────────────────────────────────────────────
        // Picker-only per decision: direct upload removed entirely for this field.
        app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
            $tourPackage,
            'gallery',
            array_values($request->input('gallery_image_ids', []))
        );

        // ── Safari Car Images ─────────────────────────────────────────────────
        // Picker-only per decision: direct upload removed entirely for this field.
        app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
            $tourPackage,
            'safari_car_images',
            array_values($request->input('safari_car_image_ids', []))
        );

        // Note: itinerary day images and accommodation tier images are picker-only
        // and were already correctly included in $itinerary when TourPackage::create()
        // ran above (both came from existing_image_ids / existing_accommodation_image_*
        // in the itinerary-construction block earlier in this method) — no further
        // processing or re-save is needed here. (This used to be a second pass that
        // uploaded files and re-saved $tourPackage->itinerary; once upload was
        // removed, that pass turned out to just re-filter and re-save identical data
        // that was already correct, so it's removed rather than kept as a no-op.)


        // ── Extra Sections ────────────────────────────────────────────────────
        // Picker-only per decision: direct upload removed entirely for the section
        // image field — image_id now comes straight from existing_image_id, the
        // field <x-media-picker> submits.
        if ($request->has('extra_sections')) {
            $sections = [];
            foreach ($request->extra_sections as $index => $sectionData) {
                $section = [
                    'title'      => $sectionData['title'] ?? '',
                    'content'    => $sectionData['content'] ?? '',
                    'image_side' => $sectionData['image_side'] ?? 'left',
                    'image_id'   => $sectionData['existing_image_id'] ?? null,
                ];

                if (!empty($section['title']) || !empty($section['content']) || $section['image_id']) {
                    $sections[] = $section;
                }
            }

            $tourPackage->extra_sections = $sections;
            $tourPackage->save();
        }

        // ── Destinations ──────────────────────────────────────────────────────
        if ($request->has('destinations')) {
            $tourPackage->destinations()->sync($request->destinations);
        }

// ── Categories ────────────────────────────────────────────────────────
        if ($request->has('categories')) {
            $tourPackage->categories()->sync($request->categories);
        }

// ── Phase 2 Pricing ──────────────────────────────────────────────────
        $this->persistPricing($request, $tourPackage);

        $this->persistMega($request, $tourPackage);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-packages.index')
                         ->with('success', 'Tour package created successfully!');
        });
    }

public function edit(TourPackage $tourPackage)
    {
        $tourPackage->load('packagePrices');

        return view('admin.tour-packages.edit', compact('tourPackage'));
    }

    public function update(Request $request, TourPackage $tourPackage)
    {
        $request->validate([
            'title'                                         => 'required|string|max:255',
            'slug'                                          => 'required|string|max:255|unique:tour_packages,slug,' . $tourPackage->id,
            'duration_days'                                 => 'nullable|integer|min:1',
            'video_url'                                     => 'nullable|string|max:500',
            'embed_map'                                     => 'nullable|string',
            'physical_rating'                               => 'nullable|in:relaxing,easy,moderate,complex,super_complex',
            'tour_level'                                    => 'nullable|in:budget_camping,budget_lodge,mid_range,luxury',
            'overview'                                      => 'nullable|string',
            'highlights_text'                               => 'nullable|string',
            'status'                                        => 'required|in:draft,published,archived',
            'is_featured'                                   => 'boolean',
            'is_group_departure'                            => 'boolean',
            'starting_point'                                => 'nullable|string|max:255',
            'ending_point'                                  => 'nullable|string|max:255',
            'meta_title'                                    => 'nullable|string|max:255',
            'meta_description'                              => 'nullable|string',
            'meta_keywords'                                 => 'nullable|string|max:255',
            'hero_image'                                    => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'hero_image_id'                                 => 'nullable|integer|exists:media,id',
            'gallery_image_ids'                             => 'nullable|array',
            'gallery_image_ids.*'                           => 'integer|exists:media,id',
            'safari_car_image_ids'                          => 'nullable|array',
            'safari_car_image_ids.*'                        => 'integer|exists:media,id',
            'itinerary_days'                                => 'nullable|array',
            'itinerary_days.*.title'                        => 'nullable|string|max:255',
            'itinerary_days.*.description'                  => 'nullable|string',
            'itinerary_days.*.existing_image_ids'            => 'nullable|array',
            'itinerary_days.*.existing_image_ids.*'          => 'integer|exists:media,id',
            'itinerary_days.*.meals'                        => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_silver'    => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_gold'      => 'nullable|string|max:255',
            'itinerary_days.*.accommodation_name_platinum'  => 'nullable|string|max:255',
            'itinerary_days.*.existing_accommodation_image_silver'   => 'nullable|integer|exists:media,id',
            'itinerary_days.*.existing_accommodation_image_gold'     => 'nullable|integer|exists:media,id',
            'itinerary_days.*.existing_accommodation_image_platinum' => 'nullable|integer|exists:media,id',
            'itinerary_days.*.accommodations'               => 'nullable|array',
            'itinerary_days.*.accommodations.*.type'        => 'nullable|string|in:SILVER,GOLD,PLATINUM',
            'itinerary_days.*.accommodations.*.name'        => 'nullable|string|max:255',
            'itinerary_days.*.location_name'                => ['nullable', 'string', 'max:255'],
            'itinerary_days.*.lat'                          => ['nullable', 'numeric', 'between:-90,90'],
            'itinerary_days.*.lng'                          => ['nullable', 'numeric', 'between:-180,180'],
            'inclusions_items'                              => 'nullable|array',
            'exclusions_items'                              => 'nullable|array',
            'extra_sections'                                => 'nullable|array',
            'extra_sections.*.title'                        => 'nullable|string|max:255',
            'extra_sections.*.content'                      => 'nullable|string',
            'extra_sections.*.existing_image_id'        => 'nullable|integer|exists:media,id',
            'extra_sections.*.image_side'                   => 'nullable|in:left,right',
            'faqs'                                          => 'nullable|array',
            'faqs.*.question'                               => 'nullable|string',
            'faqs.*.answer'                                 => 'nullable|string',
'detail.title'                                  => 'nullable|string|max:255',
            'detail.description'                            => 'nullable|string',
            // ── Phase 2 Pricing ────────────────────────────────────────────────
            'pricing_source'                                 => 'required|in:none,manual,calculator',
            'package_duration_type'                          => 'sometimes|nullable|in:single_day,multi_day',
            'tour_type'                                      => 'required_if:pricing_source,calculator|nullable|in:KILIMANJARO,SAFARI',
            'package_category'                               => 'nullable|in:LUXURY,MID_RANGE,BUDGET',
            'calculator_payload'                             => 'required_if:pricing_source,calculator|json',
            'manual_prices'                                  => 'required_if:pricing_source,manual|array|max:6',
            'manual_prices.*.level_key'                      => 'required|string',
            'manual_prices.*.season_code'                    => 'required|in:HIGH,LOW_WET',
            'manual_prices.*.price_2p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_prices.*.price_4p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_prices.*.price_6p'                       => 'required|regex:/^\d{1,10}(\.\d{1,2})?$/|gt:0',
            'manual_currency'                                => 'required_if:pricing_source,manual|nullable|regex:/^[A-Za-z]{3}$/',
            // ── Navigation Mega Menu ─────────────────────────────────────────────
            ...$this->megaRules(),
        ]);

        $this->assertMegaEnableable($request);

        return DB::transaction(function () use ($request, $tourPackage) {

        // ── 1. Gallery removal is now handled entirely by setOrderedUsages() below
        // (see the "Gallery — picker-only" block further down this method) — any
        // image not included in the submitted gallery_image_ids list is simply not
        // re-recorded, which is correct: it stops being "used" in this gallery but is
        // NOT deleted from the library, since it may be in use elsewhere. The old
        // detach_gallery_ids mechanism actually deleted the underlying file outright,
        // which would have been wrong once images can be shared across multiple
        // places — removed along with the now-unused hidden input in the edit view.

        // ── 2. Safari car image removal is now handled entirely by
        // setOrderedUsages() below (see the "Safari Car Images" block further down
        // this method), for the same reason as gallery removal above — removing an
        // image from this collection should not delete the underlying file, since it
        // may be shared with another tour, destination, or section.

        // ── Season Pricing ────────────────────────────────────────────────────
        $seasonPricing = [];
        for ($i = 1; $i <= 3; $i++) {
            $seasonName = $request->input("season.$i");
            if ($seasonName) {
                $seasonPricing[] = [
                    'season'   => $seasonName,
                    'price_2p' => $request->input("season_{$i}_2p"),
                    'price_4p' => $request->input("season_{$i}_4p"),
                    'price_6p' => $request->input("season_{$i}_6p"),
                ];
            }
        }

        // ── Highlights ────────────────────────────────────────────────────────
        $highlights = $request->filled('highlights_text')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->highlights_text))))
            : null;

        // ── Inclusions & Exclusions ───────────────────────────────────────────
        $inclusions = $request->filled('inclusions_items')
            ? array_values(array_filter(array_map('trim', $request->inclusions_items)))
            : null;

        $exclusions = $request->filled('exclusions_items')
            ? array_values(array_filter(array_map('trim', $request->exclusions_items)))
            : null;

        // ── FAQs ──────────────────────────────────────────────────────────────
        $faqs = [];
        if ($request->filled('faqs')) {
            foreach ($request->faqs as $faq) {
                if (!empty($faq['question']) || !empty($faq['answer'])) {
                    $faqs[] = [
                        'question' => trim($faq['question'] ?? ''),
                        'answer'   => trim($faq['answer'] ?? ''),
                    ];
                }
            }
        }

        // ── Trip Details ──────────────────────────────────────────────────────
        // FIX: fall back to the existing DB value, and if that is also null,
        //      use an empty JSON object — so the NOT NULL column is never null.
        $tripDetails = $tourPackage->trip_details ?? json_encode(['title' => '', 'description' => '']);
        $detailInput = $request->input('detail');
        if (!empty($detailInput['title']) || !empty($detailInput['description'])) {
            $tripDetails = json_encode([
                'title'       => trim($detailInput['title'] ?? ''),
                'description' => trim($detailInput['description'] ?? ''),
            ]);
        }

        // ── Itinerary ─────────────────────────────────────────────────────────
        $itinerary = [];
        if ($request->filled('itinerary_days')) {
            foreach ($request->itinerary_days as $dayData) {
                if (!empty($dayData['title']) || !empty($dayData['description'])) {
                    $dayEntry = [
                        'title'          => $dayData['title'] ?? '',
                        'description'    => $dayData['description'] ?? '',
                        'accommodations' => $this->normalizeAccommodationTiers($dayData),
                        'meals'          => $dayData['meals'] ?? '',
                        'image_ids'      => $dayData['existing_image_ids'] ?? [],
                    ];
                    $lat = $this->cleanCoordinate($dayData['lat'] ?? null, -90, 90);
                    $lng = $this->cleanCoordinate($dayData['lng'] ?? null, -180, 180);
                    if ($lat !== null && $lng !== null) {
                        $dayEntry['lat'] = $lat;
                        $dayEntry['lng'] = $lng;
                        $dayEntry['location_name'] = $dayData['location_name'] ?? '';
                    }
                    $itinerary[] = $dayEntry;
                }
            }
        }

        // ── Update Model ──────────────────────────────────────────────────────
        // Captured before update() runs — getOriginal() would also still be correct
        // afterward (Eloquent's save() only syncs $changes, not $original — see the
        // detailed confirmation against framework source in
        // DestinationController::update()), but reading it up front here keeps this
        // logic easy to follow alongside this method's existing manual-array style.
        $previousHeroId = $tourPackage->hero_image_id;

        $tourPackage->update([
            'slug'               => $request->slug,
            'title'              => $request->title,
            'duration_days'      => $request->duration_days,
            'video_url'          => $request->video_url,
            'embed_map'          => $request->has('embed_map') ? $request->embed_map : $tourPackage->embed_map,
            'season_pricing'     => !empty($seasonPricing) ? $seasonPricing : null,
            'currency'           => $request->input('currency', 'USD'),
            'physical_rating'    => $request->input('physical_rating', 'moderate'),
            'tour_level'         => $request->input('tour_level', 'mid_range'),
            'is_group_departure' => $request->has('is_group_departure') ? 1 : 0,
            'starting_point'     => $request->starting_point,
            'ending_point'       => $request->ending_point,
            'overview'           => $request->overview,
            'highlights'         => $highlights,
            'inclusions'         => $inclusions,
            'exclusions'         => $exclusions,
            'itinerary'          => !empty($itinerary) ? $itinerary : null,
            'faqs'               => !empty($faqs) ? $faqs : null,
            'trip_details'       => $tripDetails,
            'meta_title'         => $request->meta_title,
            'meta_description'   => $request->meta_description,
            'meta_keywords'      => $request->meta_keywords,
'status'             => $request->status,
            'is_featured'        => $request->has('is_featured') ? 1 : 0,
            'no_robots'          => $request->has('no_robots') ? 1 : 0,
            'hero_image_id'      => $request->input('hero_image_id'),
            'package_duration_type' => $request->input('package_duration_type') ?: null,
            'tour_type'            => $request->input('tour_type') ? strtoupper($request->input('tour_type')) : null,
            'package_category'     => $request->input('package_category') ?: null,
            'pricing_source'       => $request->input('pricing_source', 'none'),
        ]);

        // Media Library hero selection — additive alongside the existing direct-upload
        // path below, mirroring DestinationController::update()/PageController::update().
        if ($request->filled('hero_image_id') && (int) $request->input('hero_image_id') !== (int) $previousHeroId) {
            $mediaLibrary = app(\App\Services\MediaLibraryService::class);

            if ($previousHeroId) {
                $oldHero = \App\Models\GalleryImage::find($previousHeroId);
                if ($oldHero) {
                    $mediaLibrary->forgetUsage($oldHero->id, $tourPackage, 'hero_image_id');
                }
            }

            $newHero = \App\Models\GalleryImage::find($request->input('hero_image_id'));
            if ($newHero) {
                $mediaLibrary->recordUsage($newHero->id, $tourPackage, 'hero_image_id');
            }
        }

        // ── Gallery — picker-only (direct upload, manual reorder inputs removed) ──
        // setOrderedUsages() does a full replace, which is correct since the picker
        // always submits the complete current gallery state, not a delta.
        app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
            $tourPackage,
            'gallery',
            array_values($request->input('gallery_image_ids', []))
        );

        // ── Hero Image ────────────────────────────────────────────────────────
        if ($request->hasFile('hero_image')) {
            $tourPackage->clearMediaCollection('hero');
            $tourPackage->addMediaFromRequest('hero_image')
                        ->toMediaCollection('hero', 'public');
        }

        // ── Safari Car Images ─────────────────────────────────────────────────
        // Picker-only per decision: direct upload removed entirely for this field.
        app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
            $tourPackage,
            'safari_car_images',
            array_values($request->input('safari_car_image_ids', []))
        );

        // Note: itinerary day images and accommodation tier images are picker-only.
        // day['image_ids'] already came from existing_image_ids, and
        // day['accommodations'][*]['image_id'] already came from
        // normalizeAccommodationTiers() reading existing_accommodation_image_* — both
        // in the itinerary-construction block above — so $itinerary is already
        // correct with no further processing needed. (This used to be a second pass
        // that handled file uploads and a 'detach_image_ids' mechanism that
        // permanently deleted the underlying file — the same data-loss risk already
        // fixed for gallery/safari car images, since an image can now be shared
        // across multiple places. Once upload was removed, this pass turned out to
        // just re-filter and re-save identical data that was already correct, so
        // it's removed entirely rather than kept as a no-op.)

        // ── Extra Sections ────────────────────────────────────────────────────
        // Picker-only per decision: direct upload, the remove-flag mechanism, and
        // the "delete the old file on replace" logic all removed — image_id now
        // simply reflects whatever the picker currently has selected (or null if
        // cleared), with no file ever deleted as a side effect of editing a section,
        // since the same image may be used elsewhere.
        if ($request->has('extra_sections')) {
            $sections = [];
            foreach ($request->extra_sections as $index => $sectionData) {
                $section = [
                    'title'      => $sectionData['title'] ?? '',
                    'content'    => $sectionData['content'] ?? '',
                    'image_side' => $sectionData['image_side'] ?? 'left',
                    'image_id'   => $sectionData['existing_image_id'] ?? null,
                ];

                if (!empty($section['title']) || !empty($section['content']) || $section['image_id']) {
                    $sections[] = $section;
                }
            }

            $tourPackage->extra_sections = $sections;
            $tourPackage->save();
        }

        // ── Destinations ──────────────────────────────────────────────────────
        if ($request->has('destinations')) {
            $tourPackage->destinations()->sync($request->destinations);
        }

        // ── Categories ────────────────────────────────────────────────────────
        // NOTE: same limitation as the destinations sync above — if every category
        // is deselected, the <select multiple> field is entirely absent from the
        // submitted form data, so $request->has('categories') is false and the old
        // categories are never cleared. This matches existing, pre-existing behavior
        // for destinations rather than introducing a special case only here; if this
        // ever needs fixing, both should be fixed together (e.g. a hidden
        // categories_submitted=1 marker field).
        if ($request->has('categories')) {
            $tourPackage->categories()->sync($request->categories);
        }

        // ── Activities ────────────────────────────────────────────────────────
        // Same fix as store() — this field existed on the form but was never wired
        // up to actually persist the selection.
        if ($request->has('activities')) {
            $tourPackage->activities()->sync($request->activities);
        }

        // ── Group Departures ──────────────────────────────────────────────────
        if ($request->has('departures')) {
            $tourPackage->groupDepartures()->delete();
            foreach ($request->departures as $depData) {
                if (!empty($depData['departure_date'])) {
                    $tourPackage->groupDepartures()->create([
                        'departure_date'  => $depData['departure_date'],
                        'return_date'     => $depData['return_date'] ?? null,
                        'total_spots'     => $depData['total_spots'] ?? 12,
                        'available_spots' => $depData['available_spots'] ?? 12,
                        'group_price'     => $depData['group_price'] ?? null,
                        'currency'        => $tourPackage->currency ?? 'USD',
                        'status'          => $depData['status'] ?? 'open',
                        'is_featured'     => isset($depData['is_featured']),
                        'notes'           => $depData['notes'] ?? null,
                    ]);
                }
            }
        }

// ── Phase 2 Pricing ──────────────────────────────────────────────────
        $this->persistPricing($request, $tourPackage);

        $this->persistMega($request, $tourPackage);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-packages.index')
                         ->with('success', 'Tour package updated successfully!');
        });
    }

    private function normalizeAccommodationTiers(array $dayData): array
    {
        $tiers = [
            'silver'   => 'SILVER',
            'gold'     => 'GOLD',
            'platinum' => 'PLATINUM',
        ];

        $hasTierFields = collect(array_keys($tiers))->contains(function ($key) use ($dayData) {
            return array_key_exists("accommodation_name_$key", $dayData)
                || array_key_exists("existing_accommodation_image_$key", $dayData)
                || array_key_exists("remove_accommodation_image_$key", $dayData);
        });

        if (!$hasTierFields) {
            return array_values($dayData['accommodations'] ?? []);
        }

        $accommodations = [];
        foreach ($tiers as $key => $label) {
            $name = trim($dayData["accommodation_name_$key"] ?? '');
            $imageId = $dayData["existing_accommodation_image_$key"] ?? null;

            // Skip a tier with neither a name nor an image — this used to also
            // require a 'remove_accommodation_image_*' flag to be set, a field that
            // no longer exists now that accommodation images are picker-only (no
            // upload, so nothing to "remove" via a separate flag — clearing the
            // picker selection is enough). That old condition meant a genuinely
            // blank tier was never actually skipped unless a removal had just
            // happened to occur; fixed here to skip any blank tier outright.
            if ($name === '' && empty($imageId)) {
                continue;
            }

            $accommodations[] = [
                'type'              => $label,
                'tier_key'          => $key,
                'name'              => $name,
                'image_id'          => $imageId,
                'existing_image_id' => $imageId,
            ];
        }

        return $accommodations;
    }

    public function destroy(TourPackage $tourPackage)
    {
        $tourPackage->clearMediaCollection('hero');
        $tourPackage->clearMediaCollection('gallery');
        $tourPackage->clearMediaCollection('safari_car_images');
        $tourPackage->clearMediaCollection('itinerary_images');
        $tourPackage->clearMediaCollection('accommodation_images');
        $tourPackage->clearMediaCollection('extra_sections');

        // Clean up media_usages too — without this, deleting a tour package would
        // leave orphaned usage rows pointing at a model_id that no longer exists,
        // which would incorrectly keep images "in use" forever (see
        // MediaLibraryService::isInUse()) and block their deletion from the library.
        app(\App\Services\MediaLibraryService::class)->forgetAllUsagesFor($tourPackage);

$tourPackage->groupDepartures()->delete();
        app(\App\Services\NavigationMegaMenuService::class)->clearForSource($tourPackage);
        $tourPackage->delete();

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-packages.index')
                         ->with('success', 'Tour package deleted successfully!');
    }

    /**
     * Persist the Phase 2 pricing state selected on the form. Must be called
     * inside the store/update DB transaction so pricing persistence and the
     * tour save always succeed or roll back together. The source determines
     * what (if anything) is written to package_prices:
     *   - none       → all current package prices are removed (no new pricing)
     *   - manual     → rows from the manual grid are validated and written
     *   - calculator → one immutable snapshot + item rows are created, then
     *                  package prices are derived from the engine result
     * Any PricingValidationException from the pure engine is converted to a
     * ValidationException so the form displays the message inline.
     */
    private function persistPricing(Request $request, TourPackage $tourPackage): void
    {
        $source = (string) $request->input('pricing_source', 'none');

        if ($source === 'none') {
            app(PricingPersistenceService::class)->removeAllCurrentPackagePrices($tourPackage);

            return;
        }

        $duration = $source !== 'none'
            ? PackageDurationType::tryFrom((string) $request->input('package_duration_type', 'multi_day'))
            : null;

        if ($duration === null) {
            throw ValidationException::withMessages([
                'package_duration_type' => 'Package duration type is required when pricing is configured.',
            ]);
        }

        $user = $request->user();
        $currency = strtoupper((string) $request->input('currency', 'USD'));
        $category = $duration === PackageDurationType::SINGLE_DAY
            ? null
            : $request->input('package_category');

        try {
            $persistence = app(PricingPersistenceService::class);

            if ($source === 'manual') {
                $persistence->replaceManualPackagePrices(
                    $tourPackage,
                    $this->normalizeManualRows($request, $duration, $category),
                    (string) $category,
                    $currency,
                    $duration,
                    $user->id,
                );

                return;
            }

            if ($source === 'calculator') {
                $decoded = json_decode(trim((string) $request->input('calculator_payload', '')), true);
                if (! is_array($decoded)) {
                    throw PricingValidationException::for('Calculator payload is not valid JSON.');
                }

                $raw = $decoded;
                $pricing = new PricingService();
                [$input, $serialized] = $pricing->calculateWithInput($raw);

                $calculation = $persistence->createCalculation(
                    $tourPackage,
                    $raw,
                    $serialized,
                    $user->id,
                    $raw['items'] ?? [],
                );

                $persistence->replacePackagePricesFromCalculation(
                    $tourPackage,
                    $calculation,
                    $serialized,
                    $user->id,
                    [
                        'single_day' => $input->isSingleDay(),
                        'package_category' => $input->packageCategory,
                        'currency' => $currency,
                    ],
                );

                return;
            }

            throw PricingValidationException::for("Unknown pricing source '{$source}'.");
        } catch (PricingValidationException $e) {
            throw ValidationException::withMessages(['pricing' => $e->getMessage()]);
        }
    }

    /**
     * Rebuild the manual price payload into canonical per-person rows, asserting
     * that every expected level x season combination is present exactly once and
     * that no combination targets a level outside the package's catalog.
     *
     * @return array<int, array<string, string>>
     */
    private function normalizeManualRows(Request $request, PackageDurationType $duration, ?string $category): array
    {
        $pricing = new PricingService();
        $expected = $duration === PackageDurationType::SINGLE_DAY
            ? $pricing->singleDayLevel()
            : $pricing->levelsFor((string) $category);

        if ($expected === []) {
            throw PricingValidationException::for('No pricing levels available for the selected category.');
        }

        $expectedLevels = array_keys($expected);

        $rows = [];
        foreach ((array) $request->input('manual_prices', []) as $index => $row) {
            $levelKey = (string) ($row['level_key'] ?? '');
            $season = (string) ($row['season_code'] ?? '');

            if (! in_array($levelKey, $expectedLevels, true)) {
                throw PricingValidationException::for(
                    "Manual price row #{$index}: level '{$levelKey}' is not a valid level for this package."
                );
            }

            $rows[] = [
                'level_key' => $levelKey,
                'season_code' => $season,
                'price_2p' => (string) $row['price_2p'],
                'price_4p' => (string) $row['price_4p'],
                'price_6p' => (string) $row['price_6p'],
            ];
        }

        $combos = [];
        foreach ($expectedLevels as $levelKey) {
            foreach (PricingService::SEASONS as $seasonCode) {
                $combos["{$levelKey}|{$seasonCode}"] = false;
            }
        }

        foreach ($rows as $row) {
            $combo = $row['level_key'] . '|' . $row['season_code'];
            $label = $expected[$row['level_key']] . ' (' . $row['season_code'] . ')';

            if (! array_key_exists($combo, $combos)) {
                throw PricingValidationException::for(
                    "Manual price row for {$label} is not a valid combination for this package."
                );
            }

            if ($combos[$combo]) {
                throw PricingValidationException::for("Duplicate manual price row for {$label}.");
            }

            $combos[$combo] = true;
        }

        foreach ($combos as $combo => $present) {
            if (! $present) {
                [$levelKey, $seasonCode] = explode('|', $combo);
                $label = $expected[$levelKey] . ' (' . $seasonCode . ')';
                throw PricingValidationException::for("Missing manual price row for {$label}.");
            }
        }

        return $rows;
    }

    /**
     * Validation rules for the "Navigation Mega Menu" section shared by the
     * store() and update() methods. A tour is only ever persisted as a mega-menu
     * item when 'mega_menu.enabled' is true; then the parent/label are required.
     */
    protected function megaRules(): array
    {
        return app(\App\Services\NavigationMegaMenuService::class)->megaRules();
    }

    /**
     * Called after validation (before the DB transaction) to enforce the
     * per-parent item cap and duplicate-source guard. Only runs when the item is
     * being enabled. Throws a ValidationException surfaced inline on the form.
     */
    protected function assertMegaEnableable(Request $request): void
    {
        $mega = $request->input('mega_menu');

        if (! is_array($mega) || empty($mega['enabled'])) {
            return;
        }

        $source = $request->route('tour_package');

        if (! $source) {
            return;
        }

        app(\App\Services\NavigationMegaMenuService::class)->assertCanEnable(
            (string) $mega['parent_key'],
            $source,
            app(\App\Services\NavigationMegaMenuService::class)->maxItemsPerMenu(),
        );
    }

    /**
     * Persist (or clear) the mega-menu entry for a tour, inside the store/update
     * DB transaction so the tour save and its menu row commit or roll back together.
     */
    protected function persistMega(Request $request, $tourPackage): void
    {
        $mega = $request->input('mega_menu');
        $actorId = $request->user()?->id;

        app(\App\Services\NavigationMegaMenuService::class)
            ->persistForSource($tourPackage, is_array($mega) ? $mega : null, $actorId);
    }

    /**
     * Sanitize a coordinate value: trim whitespace, convert empty to null,
     * cast to float, and reject if out of range.
     */
    protected function cleanCoordinate($value, float $min, float $max): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = trim((string) $value);

        if ($cleaned === '') {
            return null;
        }

        if (! is_numeric($cleaned)) {
            return null;
        }

        $float = (float) $cleaned;

        if ($float < $min || $float > $max) {
            return null;
        }

        return $float;
    }
}
