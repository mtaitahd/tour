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
        $tourPackages = TourPackage::with('destinations')->orderBy('title')->get();
        return view('admin.tour-packages.index', compact('tourPackages'));
    }

    public function create(Request $request)
    {
        $draftId = $request->session()->getOldInput('draft_id') ?: $request->query('draft_id');
        $draft = $draftId
            ? TourPackage::where('status', 'draft')->find($draftId)
            : null;

        if ($draft && $request->query->has('draft_id')) {
            $draftInput = $draft->draft_payload ?? [];
            $draftInput['draft_id'] = $draft->getKey();
            $draftInput['status'] = 'draft';
            $request->session()->flashInput(array_merge($draftInput, $request->session()->getOldInput()));
        }

        return view('admin.tour-packages.create', compact('draft'));
    }

    /**
     * Save an incomplete tour form as a private draft without running the final
     * pricing or publication validation. The payload lets the editor restore
     * fields that have not reached their final database representation yet.
     */
    public function autosaveDraft(Request $request)
    {
        $request->validate([
            'draft_id' => 'nullable|integer|exists:tour_packages,id',
            'title'    => 'nullable|string|max:255',
            'slug'     => 'nullable|string|max:255',
        ]);

        $tourPackage = null;
        if ($request->filled('draft_id')) {
            $tourPackage = TourPackage::where('status', 'draft')->findOrFail($request->integer('draft_id'));
        }

        $title = trim((string) $request->input('title', ''));
        $slug = Str::slug((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = Str::slug($title);
        }
        if ($slug === '') {
            $slug = 'tour-draft-' . Str::lower(Str::random(10));
        }

        $baseSlug = $slug;
        $suffix = 1;
        while (TourPackage::where('slug', $slug)
            ->when($tourPackage, fn ($query) => $query->whereKeyNot($tourPackage->getKey()))
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $payload = $this->jsonSafe($request->input());
        unset($payload['_token'], $payload['_method'], $payload['draft_id']);

        // Belt and braces: a repaired payload should always encode, but if it does
        // not, say which field to fix instead of returning a 500 from deep inside
        // Eloquent's JSON cast.
        if (json_encode($payload) === false) {
            throw ValidationException::withMessages([
                'overview' => 'The Overview contains text that cannot be saved. Remove any pasted content and try again.',
            ]);
        }

        $tourPackage ??= new TourPackage();
        $tourPackage->fill([
            'title'         => $title !== '' ? $title : 'Untitled tour draft',
            'slug'          => $slug,
            'status'        => 'draft',
            // Some production databases still define trip_details as NOT NULL
            // without a default. The field stays out of the form; persist an
            // empty JSON array so incomplete drafts can be inserted safely.
            'trip_details'   => [],
            'draft_payload' => $payload,
        ]);
        $tourPackage->save();

        return response()->json([
            'draft_id' => $tourPackage->getKey(),
            'slug'     => $tourPackage->slug,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Make a posted payload safe to store in a JSON column.
     *
     * json_encode() returns false on malformed UTF-8 rather than throwing, and
     * Eloquent turns that false into a JsonEncodingException — a 500 for
     * something the editor did to their own text. Pasted content carries lone
     * surrogates and stray bytes often enough to matter: a draft is saved on every
     * keystroke pause, so one bad character in the Overview or an itinerary day
     * takes down every subsequent save, not just the one that pasted it.
     *
     * Keys are left alone: they are the form's own field names, not user text.
     */
    private function jsonSafe(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->jsonSafe($item), $value);
        }

        if (! is_string($value) || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Drops the malformed sequences and keeps the rest, which is what the
        // browser would have rendered anyway.
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                                         => 'required|string|max:255',
            'slug'                                          => 'nullable|string|unique:tour_packages,slug',
            // Many-to-many pickers. Validated so a hand-crafted or stale POST can't
            // attach ids that don't exist, which would fail the pivot FK.
            'destinations'                                  => 'nullable|array',
            'destinations.*'                                => 'integer|exists:destinations,id',
            'categories'                                    => 'nullable|array',
            'categories.*'                                  => 'integer|exists:tour_categories,id',
            'activities'                                    => 'nullable|array',
            'activities.*'                                  => 'integer|exists:activities,id',
            'mountain_id'                                   => 'nullable|integer|exists:mountains,id',
            'mountain_route_ids'                            => 'nullable|array',
            'mountain_route_ids.*'                          => 'integer|exists:mountain_routes,id',
            'related_tour_ids'                              => 'nullable|array',
            'related_tour_ids.*'                            => 'integer|exists:tour_packages,id',
            'related_tour_rules'                            => 'nullable|array',
            'related_tour_rules.*'                          => 'in:tour_type,budget,categories',
            'related_tour_rules_submitted'                  => 'nullable|boolean',
            'tour_format'                                   => 'nullable|in:private,group',
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
            'available_months'                              => 'nullable|array',
            'available_months.*'                            => 'integer|between:1,12',
            'meta_title'                                    => 'nullable|string|max:255',
            'meta_description'                              => 'nullable|string',
            'meta_keywords'                                 => 'nullable|string|max:255',
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
        ]);

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
            'available_months'   => $this->normalizeMonths($request->input('available_months')),
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
            'tour_format'          => $request->input('tour_format', 'private'),
            'mountain_id'          => $request->input('mountain_id'),
            'mountain_route_ids'   => array_values(array_map('intval', $request->input('mountain_route_ids', []))),
            'related_tour_ids'     => array_values(array_map('intval', $request->input('related_tour_ids', []))),
            'related_tour_rules'   => $request->has('related_tour_rules_submitted')
                ? array_values(array_unique($request->input('related_tour_rules', [])))
                : ['tour_type', 'budget', 'categories'],
            'pricing_source'       => $request->input('pricing_source', 'none'),
        ]);

        // ── Hero Image ────────────────────────────────────────────────────────
        // Picker-only: the Media Library is the single source of tour images, so
        // the old direct-upload branch (clearMediaCollection + addMediaFromRequest)
        // is gone. Tours that predate the picker keep their legacy 'hero' media
        // untouched — clearing the picker selection simply stops re-attaching it.
        //
        // Media Library selection, on create — no previous usage to forget here.
        if ($request->filled('hero_image_id')) {
            $heroImage = \App\Models\GalleryImage::find($request->input('hero_image_id'));
            if ($heroImage) {
                app(\App\Services\MediaLibraryService::class)->recordUsage($heroImage->id, $tourPackage, 'hero_image_id');
            }
        }

        // ── Gallery Images ────────────────────────────────────────────────────
        // Picker-only per decision: direct upload removed entirely for this field.
        if ($request->has('gallery_image_ids')) {
            app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
                $tourPackage,
                'gallery',
                array_values($request->input('gallery_image_ids', []))
            );
        }

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

        // ── Destinations / Categories / Activities ─────────────────────────────
        // These sync unconditionally rather than behind ->has(). Checkbox and
        // multi-select fields are simply absent from the POST when the admin unticks
        // everything, so a has() guard means "clear all" silently keeps the old
        // selection instead of removing it. Both forms always render these fields,
        // so an absent key genuinely means "none".
        $tourPackage->destinations()->sync((array) $request->input('destinations', []));
        $tourPackage->categories()->sync((array) $request->input('categories', []));
        $tourPackage->activities()->sync((array) $request->input('activities', []));

        // ── Phase 2 Pricing ──────────────────────────────────────────────────
        $this->persistPricing($request, $tourPackage);

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-packages.index')
                         ->with('success', 'Tour package created successfully!');
        });
    }

public function edit(TourPackage $tourPackage)
    {
        $tourPackage->load('packagePrices');

        if ($tourPackage->status === 'draft' && is_array($tourPackage->draft_payload)) {
            $previousInput = request()->session()->getOldInput();
            $draftInput = $tourPackage->draft_payload;
            $draftInput['status'] = 'draft';
            request()->session()->flashInput(array_merge($draftInput, $previousInput));
        }

        return view('admin.tour-packages.edit', compact('tourPackage'));
    }

    public function update(Request $request, TourPackage $tourPackage)
    {
        $request->validate([
            'title'                                         => 'required|string|max:255',
            'slug'                                          => 'required|string|max:255|unique:tour_packages,slug,' . $tourPackage->id,
            // Same many-to-many guards as store() — see the note there.
            'destinations'                                  => 'nullable|array',
            'destinations.*'                                => 'integer|exists:destinations,id',
            'categories'                                    => 'nullable|array',
            'categories.*'                                  => 'integer|exists:tour_categories,id',
            'activities'                                    => 'nullable|array',
            'activities.*'                                  => 'integer|exists:activities,id',
            'mountain_id'                                   => 'nullable|integer|exists:mountains,id',
            'mountain_route_ids'                            => 'nullable|array',
            'mountain_route_ids.*'                          => 'integer|exists:mountain_routes,id',
            'related_tour_ids'                              => 'nullable|array',
            'related_tour_ids.*'                            => 'integer|exists:tour_packages,id',
            'related_tour_rules'                            => 'nullable|array',
            'related_tour_rules.*'                          => 'in:tour_type,budget,categories',
            'related_tour_rules_submitted'                  => 'nullable|boolean',
            'tour_format'                                   => 'nullable|in:private,group',
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
            'available_months'                              => 'nullable|array',
            'available_months.*'                            => 'integer|between:1,12',
            'meta_title'                                    => 'nullable|string|max:255',
            'meta_description'                              => 'nullable|string',
            'meta_keywords'                                 => 'nullable|string|max:255',
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
        ]);

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
            'video_url'          => $request->input('video_url', $tourPackage->video_url),
            'embed_map'          => $request->has('embed_map') ? $request->embed_map : $tourPackage->embed_map,
            'season_pricing'     => !empty($seasonPricing) ? $seasonPricing : null,
            'currency'           => $request->input('currency', 'USD'),
            'physical_rating'    => $request->input('physical_rating', 'moderate'),
            'tour_level'         => $request->input('tour_level', 'mid_range'),
            'is_group_departure' => $request->has('is_group_departure') ? 1 : $tourPackage->is_group_departure,
            'available_months'   => $this->normalizeMonths($request->input('available_months')),
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
            'tour_format'          => $request->input('tour_format', 'private'),
            'mountain_id'          => $request->input('mountain_id'),
            'mountain_route_ids'   => array_values(array_map('intval', $request->input('mountain_route_ids', []))),
            'related_tour_ids'     => array_values(array_map('intval', $request->input('related_tour_ids', []))),
            'related_tour_rules'   => $request->has('related_tour_rules_submitted')
                ? array_values(array_unique($request->input('related_tour_rules', [])))
                : ['tour_type', 'budget', 'categories'],

            'pricing_source'       => $request->input('pricing_source', 'none'),
            'draft_payload'        => null,
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
        if ($request->has('gallery_image_ids')) {
            app(\App\Services\MediaLibraryService::class)->setOrderedUsages(
                $tourPackage,
                'gallery',
                array_values($request->input('gallery_image_ids', []))
            );
        }

        // ── Hero Image ────────────────────────────────────────────────────────
        // Picker-only: see the matching note in store() — the direct-upload
        // branch that used to live here is gone.

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

        // ── Destinations / Categories / Activities ─────────────────────────────
        // Now unconditional, matching store(). The old has() guards were a known
        // bug: a <select multiple> / checkbox group is entirely absent from the POST
        // when everything is deselected, so "clear all" left the old rows attached
        // and the admin could never unassign a category, activity or destination.
        $tourPackage->destinations()->sync((array) $request->input('destinations', []));
        $tourPackage->categories()->sync((array) $request->input('categories', []));
        $tourPackage->activities()->sync((array) $request->input('activities', []));

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

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.tour-packages.index')
                         ->with('success', 'Tour package updated successfully!');
        });
    }

    /**
     * Coerce the submitted month checkboxes into a clean, ordered, de-duplicated
     * list of month numbers, or null when none are ticked.
     *
     * Checkbox arrays arrive in DOM order and are absent entirely when nothing is
     * ticked, so storing them raw would make "all months" and "no months" hard to
     * tell apart and would leave duplicates/spaces in the JSON. null means "no
     * month restriction", which is a real, distinct state — see runsAllYear().
     *
     * @param  mixed  $months
     * @return int[]|null
     */
    private function normalizeMonths($months): ?array
    {
        if (!is_array($months)) {
            return null;
        }

        $normalized = collect($months)
            ->map(fn ($month) => (int) $month)
            ->filter(fn ($month) => $month >= 1 && $month <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $normalized ?: null;
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
            // Legacy array-shaped accommodations (the old repeatable repeater, now
            // removed from the tour forms). Normalise rather than passing through
            // verbatim: that pass-through stored whatever the form happened to post
            // under 'existing_image_id' and never wrote 'image_id', which is the key
            // every reader (the edit form, the public tour page) actually looks at —
            // so images picked for these entries were silently dropped. The upload
            // era's 'image' key is discarded since images are picker-only now.
            $legacy = [];
            foreach (($dayData['accommodations'] ?? []) as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $imageId = $entry['image_id'] ?? ($entry['existing_image_id'] ?? null);

                $legacy[] = [
                    'type'       => $entry['type'] ?? '',
                    'tier_key'   => $entry['tier_key'] ?? null,
                    'name'       => trim((string) ($entry['name'] ?? '')),
                    'image_id'   => $imageId ?: null,
                ];
            }

            return $legacy;
        }

        $accommodations = [];
        foreach ($tiers as $key => $label) {
            $name = trim($dayData["accommodation_name_$key"] ?? '');
            $selectedAccommodation = $name !== '' ? \App\Models\Accommodation::published()->where('name', $name)->first() : null;
            $imageId = ! empty($dayData["existing_accommodation_image_$key"])
                ? $dayData["existing_accommodation_image_$key"]
                : $selectedAccommodation?->hero_image_id;

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
                'accommodation_id'  => $selectedAccommodation?->id,
                'description'       => $selectedAccommodation?->description,
                'amenities'         => $selectedAccommodation?->amenities ?? [],
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
