<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\TourCategory;
use App\Models\TourPackage;use App\Services\TourPriceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TourController extends Controller
{
    /**
     * Tours per page on the public listing. Server-side pagination only —
     * the query is paginated at the database level, never on a loaded collection.
     */
    public const TOURS_PER_PAGE = 10;

    /**
     * Validation rules for every listing query-string parameter the page
     * supports (filters, sorting inputs and pagination). Mirrors the filters
     * actually read below — nothing invented.
     */
    private function listingValidationRules(): array
    {
        return [
            'page'            => ['nullable', 'integer', 'min:1'],
            'search'          => ['nullable', 'string', 'max:255'],
            'when'            => ['nullable', 'date'],
            'adults'          => ['nullable', 'integer', 'min:1'],
            'children'        => ['nullable', 'integer', 'min:0'],
            'travellers'      => ['nullable', 'integer', 'min:1'],
            'duration_min'    => ['nullable', 'integer', 'min:1'],
            'duration_max'    => ['nullable', 'integer', 'min:1', 'gte:duration_min'],
            'price_min'       => ['nullable', 'numeric', 'min:0'],
            'price_max'       => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
            'level'           => ['nullable', 'string', 'max:100'],
            'rating'          => ['nullable', 'integer', 'min:1', 'max:5'],
            'destination'     => ['nullable', 'string', 'max:255'],
            'category'        => ['nullable', 'string', 'max:255'],
            'categories'      => ['nullable', 'array'],
            'categories.*'    => ['string', 'max:255'],
            'featured'        => ['nullable', 'boolean'],
            'accommodation'   => ['nullable', 'array'],
            'accommodation.*' => ['in:camping,lodge'],
            'luxury'          => ['nullable', 'array'],
            'luxury.*'        => ['in:budget,mid_range,luxury'],
            'countries'       => ['nullable', 'array'],
            'countries.*'     => ['string', 'size:2'],
            'parks'           => ['nullable', 'array'],
            'parks.*'         => ['integer'],
            'activities'      => ['nullable', 'array'],
            'activities.*'    => ['integer'],
        ];
    }

    public function index(Request $request, ?string $categorySlug = null)
    {
        // Validate all incoming filter/sort/pagination parameters. A failed
        // validation must never loop (redirecting back to the same bad URL
        // would re-fail forever), so instead of the default back-redirect we
        // redirect to the same address with only the offending top-level
        // parameters stripped — every valid filter is preserved.
        $validator = Validator::make($request->query(), $this->listingValidationRules());
        if ($validator->fails()) {
            $offending = collect(array_keys($validator->failed()))
                ->map(fn ($key) => explode('.', $key)[0])
                ->unique()
                ->all();

            return redirect()->to($request->fullUrlWithoutQuery($offending), 302);
        }

        // Non-numeric or negative ?page= values get a clean URL without the
        // parameter (the paginator itself would silently clamp them to 1).
        $rawPage = $request->query('page');
        if ($rawPage !== null && $rawPage !== ''
            && (filter_var($rawPage, FILTER_VALIDATE_INT) === false || (int) $rawPage < 1)) {
            return redirect()->to($request->fullUrlWithoutQuery(['page']), 302);
        }

        // Start the query
        $query = TourPackage::where('status', 'published')
                            ->with(['destinations', 'categories'])
                            ->orderByDesc('is_featured')   // featured packages bubble to the top
                            ->orderBy('order')
                            ->orderBy('title');

        // Keyword / search filter
        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('overview', 'like', $term);
            });
        }

        // Duration filter
        if ($request->filled('duration_min') || $request->filled('duration_max')) {
            $min = $request->input('duration_min');
            $max = $request->input('duration_max');
            if ($min) $query->where('duration_days', '>=', $min);
            if ($max) $query->where('duration_days', '<=', $max);
        }

        // Price filter
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $min = $request->input('price_min');
            $max = $request->input('price_max');
            if ($min) $query->where('base_price', '>=', $min);
            if ($max) $query->where('base_price', '<=', $max);
        }

        // Tour level filter
        if ($request->filled('level')) {
            $query->where('tour_level', $request->input('level'));
        }

        // Physical rating filter
        if ($request->filled('rating')) {
            $query->where('physical_rating', $request->input('rating'));
        }

        // Destination filter. The homepage search sends a destination slug while the
        // original listing filter used ids, so accept both without breaking either URL.
        $headerDestination = null;
        if ($request->filled('destination')) {
            $destinationValue = $request->input('destination');
            $headerDestination = Destination::query()
                ->where(function ($q) use ($destinationValue) {
                    if (is_numeric($destinationValue)) {
                        $q->where('id', (int) $destinationValue);
                    }

                    $q->orWhere('slug', $destinationValue);
                })
                ->first();

            if ($headerDestination) {
                $query->whereHas('destinations', function ($q) use ($headerDestination) {
                    $q->where('destinations.id', $headerDestination->id);
                });
            }
        }

        // Category filter â€” supports the public per-category listing routes (e.g.
        // /tanzania-tours, /kilimanjaro-climbing) as well as a query-string filter on
        // the main /tours page (e.g. /tours?category=tanzania-tours). $categorySlug is
        // passed in directly by TourController::category() below for the dedicated
        // category routes; the query string is the fallback for the plain /tours page.
        $categorySlug = $categorySlug ?? $request->input('category');
        $activeCategory = null;
        if ($categorySlug) {
            $activeCategory = TourCategory::where('slug', $categorySlug)->first();
            if ($activeCategory) {
                $query->whereHas('categories', function ($q) use ($activeCategory) {
                    $q->where('tour_categories.id', $activeCategory->id);
                });
            }
        }

        // Tour type filter â€” multi-select categories used by the redesigned sidebar.
        if ($request->filled('categories')) {
            $categorySlugs = array_filter((array) $request->input('categories'));
            if (!empty($categorySlugs)) {
                $query->whereHas('categories', function ($q) use ($categorySlugs) {
                    $q->whereIn('tour_categories.slug', $categorySlugs);
                });
            }
        }

        // Featured-only filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Accommodation style â€” derived from tour_level (camping vs lodge)
        if ($request->filled('accommodation')) {
            $styles = array_filter((array) $request->input('accommodation'));
            $query->where(function ($q) use ($styles) {
                foreach ($styles as $style) {
                    if ($style === 'camping') {
                        $q->orWhere('tour_level', 'like', '%camping%');
                    } elseif ($style === 'lodge') {
                        $q->orWhere('tour_level', 'like', '%lodge%');
                    }
                }
            });
        }

        // Luxury level â€” budget / mid_range / luxury buckets of tour_level
        if ($request->filled('luxury')) {
            $buckets = array_filter((array) $request->input('luxury'));
            $query->where(function ($q) use ($buckets) {
                foreach ($buckets as $bucket) {
                    if ($bucket === 'budget') {
                        $q->orWhere('tour_level', 'like', 'budget%');
                    } elseif ($bucket === 'mid_range') {
                        $q->orWhere('tour_level', 'mid_range');
                    } elseif ($bucket === 'luxury') {
                        $q->orWhere('tour_level', 'luxury');
                    }
                }
            });
        }

        // Countries (multi) â€” via the tour's destinations' country_code
        if ($request->filled('countries')) {
            $codes = array_filter((array) $request->input('countries'));
            if (!empty($codes)) {
                $query->whereHas('destinations', function ($q) use ($codes) {
                    $q->whereIn('destinations.country_code', $codes);
                });
            }
        }

        // Parks & Reserves (multi destination ids)
        if ($request->filled('parks')) {
            $parkIds = array_filter((array) $request->input('parks'));
            if (!empty($parkIds)) {
                $query->whereHas('destinations', function ($q) use ($parkIds) {
                    $q->whereIn('destinations.id', $parkIds);
                });
            }
        }

        // Activities (multi activity ids)
        if ($request->filled('activities')) {
            $activityIds = array_filter((array) $request->input('activities'));
            if (!empty($activityIds)) {
                $query->whereHas('activities', function ($q) use ($activityIds) {
                    $q->whereIn('activities.id', $activityIds);
                });
            }
        }

        // Final results with pagination (keeps query string for filters).
        // All filters, eager-loading and the editorial ordering above are
        // applied BEFORE paginate(), so only one page of rows leaves the DB.
        $tours = $query->paginate(self::TOURS_PER_PAGE)->withQueryString();

        // ?page= beyond the last page (e.g. /tours?page=999999 after filters
        // shrink the result set) must never render a broken empty grid —
        // redirect to the last valid page with every filter preserved.
        if ($tours->total() > 0
            && $rawPage !== null && $rawPage !== ''
            && (int) $rawPage > $tours->lastPage()) {
            $target = $tours->lastPage() > 1
                ? $tours->url($tours->lastPage())
                : $request->fullUrlWithoutQuery(['page']);

            return redirect()->to($target, 302);
        }

        // All categories with at least one published tour, for the filter
        // sidebar/nav â€” and the currently-active one, if any, so the view can
        // highlight it and adjust the page heading.
        $categories = TourCategory::withPublishedTours()->orderBy('order')->orderBy('name')->get();

        // â”€â”€ SafariBookings-style listing context â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

        $allDestinations = Destination::withCount(['tours' => function ($q) {
            $q->where('status', 'published');
        }])->orderBy('name')->get();

        // Countries list with published-tour counts
        $countries = $allDestinations->groupBy('country_code')
            ->map(fn ($group, $code) => (object) [
                'code' => $code,
                'name' => ['TZ' => 'Tanzania', 'KE' => 'Kenya'][$code] ?? $code,
                'count' => $group->sum('tours_count'),
            ])
            ->sortByDesc('count')
            ->values();

        // Duration histogram across ALL published tours (unfiltered distribution)
        $durationCounts = TourPackage::where('status', 'published')
            ->selectRaw('duration_days, count(*) as c')
            ->groupBy('duration_days')
            ->pluck('c', 'duration_days');

        // Price bounds for the range slider
        $priceMin = (int) TourPackage::where('status', 'published')->min('base_price');
        $priceMax = (int) TourPackage::where('status', 'published')->max('base_price');
        if ($priceMax <= 0) { $priceMax = 5000; }
        if ($priceMin > $priceMax) { $priceMin = 0; }

        // Rating aggregates â€” per current result set first, operator-wide fallback
        $resultIds = $tours->getCollection()->pluck('id');
        $scopedReviews = Testimonial::published()
            ->whereIn('tour_package_id', $resultIds)->get();
        if ($scopedReviews->isEmpty() && $headerDestination) {
            $scopedReviews = Testimonial::published()
                ->where('destination_id', $headerDestination->id)->get();
        }
        $reviewSource = $scopedReviews->isNotEmpty()
            ? $scopedReviews
            : Testimonial::published()->general()->get();
        $avgRating = $reviewSource->avg('rating') ? round((float) $reviewSource->avg('rating'), 1) : null;
        $reviewCount = $reviewSource->count();

        // Per-card ratings from real testimonials only
        $tourRatings = Testimonial::published()
            ->whereIn('tour_package_id', TourPackage::where('status', 'published')->pluck('id'))
            ->selectRaw('tour_package_id, avg(rating) as avg_rating, count(*) as review_count')
            ->groupBy('tour_package_id')
            ->get()
            ->keyBy('tour_package_id');

        // â”€â”€ FAQ section content â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Admin-editable FAQs live on each Destination (faqs JSON column). When a
        // destination is selected and has entries we use those; otherwise the
        // listing falls back to a general East-Africa safari set so the section is
        // never empty. Expert identity comes from Settings with brand fallbacks.
        $faqs = collect();
        if ($headerDestination && !empty($headerDestination->faqs)) {
            $faqs = collect($headerDestination->faqs)
                ->filter(fn ($faq) => trim((string) ($faq['question'] ?? '')) !== ''
                    && trim((string) ($faq['answer'] ?? '')) !== '')
                ->map(fn ($faq) => [
                    'question' => trim($faq['question']),
                    'answer'   => trim($faq['answer']),
                ])
                ->values();
        }

        if ($faqs->isEmpty()) {
            $faqs = collect([
                [
                    'question' => 'Why should I choose a Tanzania safari tour?',
                    'answer'   => 'Tanzania combines the Serengeti, the Ngorongoro Crater and Mount Kilimanjaro in one country, so you can pair world-class wildlife viewing with a mountain climb or a Zanzibar beach escape. Parks are long established, guides are locally trained, and routes suit both first-time and returning travellers.',
                ],
                [
                    'question' => 'Which are the best destinations for a safari?',
                    'answer'   => 'The Serengeti is famous for big cats and the Great Migration, while the Ngorongoro Crater offers exceptionally dense wildlife in a single caldera. Tarangire is known for large elephant herds and baobabs, Lake Manyara for tree-climbing lions, and the nearby coast adds Zanzibar for a relaxing finish.',
                ],
                [
                    'question' => 'What time of year is best for going on safari?',
                    'answer'   => 'The June to October dry season is the classic window: vegetation is thin, animals gather around water sources, and game drives are at their best. Calving season from January to February is superb for predators, while the March to May long rains bring lower prices and lush green landscapes.',
                ],
                [
                    'question' => 'What wildlife can I expect to see?',
                    'answer'   => 'Tanzania’s northern parks host all of the Big Five – elephant, lion, leopard, buffalo and rhino – alongside cheetahs, giraffes, zebras, wildebeest and hippos. Seasonal flamingo flocks, troops of baboons and more than 500 bird species make every drive different.',
                ],
                [
                    'question' => 'What does a typical safari day look like?',
                    'answer'   => 'Days usually start before sunrise with coffee, followed by a morning game drive when wildlife is most active. After a midday rest at your lodge or camp, you head out again for an afternoon drive or guided walk, ending with sundowners and dinner under canvas or stars.',
                ],
                [
                    'question' => 'How much will the safari cost?',
                    'answer'   => 'Costs depend on the season, comfort level, group size and the parks you visit. Budget camping safaris start from a few hundred dollars per day, while luxury lodges sit at the other end of the range – every Afro-Vertex quote itemises park fees, guide, vehicle and meals up front.',
                ],
                [
                    'question' => 'What should I consider when choosing a safari tour?',
                    'answer'   => 'Look at trip length, which parks are included, accommodation style, private versus group travel, and how demanding the itinerary is. Travelling in high season costs more but maximises wildlife sightings, and an operator with licensed local guides will always get you closer – safely.',
                ],
            ]);
        }

        $faqExpertName = Setting::get('faq_expert_name', 'Afro-Vertex Safari Experts');
        $faqExpert = [
            'name'  => $faqExpertName,
            'image' => Setting::imageUrl('faq_expert_image_id')
                ?: (Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp')),
            'bio'   => Setting::get('faq_expert_bio',
                'Born and raised in northern Tanzania, our safari experts have planned hundreds of trips across the Serengeti, Ngorongoro and Kilimanjaro regions. Every answer below reflects years of first-hand guiding experience.'),
            'link'  => Setting::get('faq_expert_link', route('home')),
        ];

        // Total published tours — powers the live count on the "Show Tours" button
        // of the Where To selector.
        $totalPublishedTours = TourPackage::where('status', 'published')->count();

        // Activities for the sidebar filter (only those linked to a published tour).
        $activities = Activity::where('is_active', true)
            ->whereHas('tours', fn ($q) => $q->where('status', 'published'))
            ->orderBy('name')
            ->get();

        // Centralised "From" prices for the current page of cards (single query).
        $fromPrices = TourPriceResolver::fromPriceMap($tours->getCollection());

        return view('frontend.tours.index', compact(
            'tours', 'categories', 'activeCategory',
            'allDestinations', 'headerDestination', 'countries',
            'durationCounts', 'priceMin', 'priceMax',
            'avgRating', 'reviewCount', 'tourRatings',
            'faqs', 'faqExpert', 'totalPublishedTours', 'fromPrices', 'activities'
        ));
    }

    /**
     * JSON endpoint powering the searchable "Where To" destination selector.
     * Searches destination names, their country and their type label, then ranks
     * results: exact matches first, followed by countries, parks and reserves,
     * cities and regions, and finally attractions and highlights (alphabetical
     * within each group). Returns slug/id so selection submits a real backend
     * value rather than just a display name.
     */
    public function searchDestinations(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $countryNames = [
            'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
            'ZA' => 'South Africa', 'BW' => 'Botswana', 'ZW' => 'Zimbabwe', 'ZM' => 'Zambia',
        ];

        $kindRank = function (string $rawType): int {
            $type = str_replace(['-', ' '], '_', strtolower(trim($rawType)));
            if ($type === 'country') {
                return 1;
            }
            if (in_array($type, ['park', 'national_park', 'reserve', 'conservation_area', 'game_reserve'], true)) {
                return 2;
            }
            if (in_array($type, ['city', 'town', 'region', 'island'], true)) {
                return 3;
            }

            return 4; // highlights, attractions, mountains, anything else
        };

        $entries = Destination::withCount(['tours' => fn ($t) => $t->where('status', 'published')])
            ->orderBy('name')
            ->get()
            ->map(function (Destination $destination) use ($countryNames, $kindRank) {
                $type = str_replace(['-', ' '], '_', strtolower(trim((string) $destination->type)));

                $country = $countryNames[$destination->country_code] ?? '';
                $rank = $kindRank($type);

                return [
                    'id'       => $destination->id,
                    'slug'     => $destination->slug,
                    'name'     => $destination->name,
                    'rank'     => $rank,
                    'subtitle' => $rank === 1 ? 'Country' : trim(ucwords(str_replace('_', ' ', $type ?: 'Destination')) . ($country ? ' · ' . $country : '')),
                    'count'    => (int) $destination->tours_count,
                ];
            })
            ->filter(fn ($entry) => $entry['count'] > 0 || $entry['rank'] === 1)
            ->values();

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $entries = $entries->filter(function ($entry) use ($needle, $countryNames) {
                foreach ([$entry['name'], $entry['subtitle']] as $haystack) {
                    if (mb_stripos($haystack, $needle) !== false) {
                        return true;
                    }
                }

                return false;
            });

            $entries = $entries->sort(function ($a, $b) use ($needle) {
                $exactA = mb_strtolower($a['name']) === $needle ? 1 : 0;
                $exactB = mb_strtolower($b['name']) === $needle ? 1 : 0;
                if ($exactA !== $exactB) {
                    return $exactB - $exactA;
                }
                if ($a['rank'] !== $b['rank']) {
                    return $a['rank'] <=> $b['rank'];
                }

                return strcasecmp($a['name'], $b['name']);
            })->values();
        }

        return response()->json([
            'results' => $entries->take(30)->values(),
            'total'   => TourPackage::where('status', 'published')->count(),
        ]);
    }

    /**
     * Public category-listing page (e.g. /tanzania-tours). Looks up the category by
     * slug and reuses index()'s exact filtering/view logic â€” the only difference is
     * the category coming from the URL segment instead of the index page's own query
     * string filter. An unrecognized slug correctly 404s rather than silently
     * rendering an unfiltered tour list, since this route matches any single path
     * segment at the site root (see routes/web.php for why) and most of those won't
     * correspond to a real category.
     */
    public function category(Request $request, string $categorySlug)
    {
        TourCategory::where('slug', $categorySlug)->firstOrFail();

        return $this->index($request, $categorySlug);
    }

    /**
     * Phase 3: server-side price lookup endpoint backing the public quote UI.
     *
     * Resolves the season from the travel date (TourSeason), picks the package
     * level, and reads the exact per-person + group-total amounts for the group
     * size from configured prices. Answers 'automatic', 'custom' or
     * 'package_request'; the inquiry flow stores this same snapshot server-side.
     */
    public function priceLookup(Request $request, TourPackage $tour)
    {
        abort_unless($tour->status === 'published', 404);

        $validated = $request->validate([
            'date'       => 'nullable|date',
            'group_size' => 'required|integer|min:1',
            'level_key'  => 'nullable|string|max:50',
        ]);

        return response()->json(
            app(TourPriceResolver::class)->lookup($tour, $validated)
        );
    }

    public function show($slug)
    {
        $tour = TourPackage::where('slug', $slug)
                           ->where('status', 'published')
                           ->with([
                               'destinations',
                               'categories',
                               'packagePrices',
                           ])
                           ->firstOrFail();

        /* ── Media ──────────────────────────────────────────────────────────── */
        $galleryImages   = $tour->galleryImages();
        $safariCarImages = $tour->safariCarImages();
        $heroUrl         = $tour->hasHeroImage()
            ? ($tour->heroUrl('large-webp') ?: $tour->heroUrl())
            : $tour->cardImageUrl('medium');

        /* ── Country + flag (derived from the tour's destinations) ──────────── */
        $countryNames = [
            'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
            'ZA' => 'South Africa', 'BW' => 'Botswana', 'ZW' => 'Zimbabwe', 'ZM' => 'Zambia',
        ];
        $flagFor = function (?string $code): string {
            if (! $code || strlen($code) !== 2) {
                return '';
            }
            $code = strtoupper($code);

            return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
        };
        $primaryCode = $tour->destinations->pluck('country_code')->filter()->countBy()->sortDesc()->keys()->first() ?: 'TZ';
        $countryName = $countryNames[$primaryCode] ?? '';
        $countryFlag = $flagFor($primaryCode);

        /* ── Map embed URL (iframe src extracted from admin-pasted embed code) ─ */
        $mapEmbedUrl = null;
        if ($tour->embed_map) {
            if (str_contains($tour->embed_map, '<iframe')) {
                preg_match('/src=["\']([^"\']+)["\']/', $tour->embed_map, $mapMatch);
                $mapEmbedUrl = $mapMatch[1] ?? null;
            } else {
                $mapEmbedUrl = $tour->embed_map;
            }
            // Only allow safe HTTPS embed URLs — reject javascript:, data:, etc.
            if ($mapEmbedUrl && ! preg_match('#^https://#i', $mapEmbedUrl)) {
                $mapEmbedUrl = null;
            }
        }

        /* ── Itinerary / route ──────────────────────────────────────────────── */
        $itinerary = collect(is_array($tour->itinerary) ? $tour->itinerary : []);
        $lastDay   = $itinerary->count();

        $cleanTitle = function (string $title): string {
            return trim(preg_replace('/^(day\s*\d+\s*[:.\-–]?\s*)/i', '', $title));
        };

        $routePoints = [];
        $routePoints[] = [
            'marker' => 'start',
            'label'  => 'Start',
            'place'  => $tour->starting_point ?: ($countryName ?: 'Trip start'),
            'days'   => 'Day 1',
        ];
        foreach ($itinerary as $index => $day) {
            $title = trim((string) ($day['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $routePoints[] = [
                'marker' => 'day',
                'label'  => 'Day ' . ($index + 1),
                'place'  => $cleanTitle($title),
                'days'   => '',
            ];
        }
        $routePoints[] = [
            'marker' => 'end',
            'label'  => 'End',
            'place'  => $tour->ending_point ?: ($countryName ?: 'Trip end'),
            'days'   => $lastDay ? 'Day ' . $lastDay : '',
        ];

        /* ── Route map days (Leaflet markers from itinerary coordinates) ──── */
        $routeMapDays = [];
        foreach ($itinerary as $index => $day) {
            $lat = $day['lat'] ?? null;
            $lng = $day['lng'] ?? null;
            if ($lat !== null && $lng !== null) {
                $routeMapDays[] = [
                    'day'           => $index + 1,
                    'title'         => $day['title'] ?? 'Day ' . ($index + 1),
                    'location_name' => $day['location_name'] ?? '',
                    'lat'           => (float) $lat,
                    'lng'           => (float) $lng,
                ];
            }
        }

        /* ── Accommodation & meals table rows (one row per itinerary day) ───── */
        $accommodationRows = $itinerary->map(function (array $day, int $index) use ($tour) {
            $tiers = collect($day['accommodations'] ?? [])->map(function (array $tier) {
                $image    = ! empty($tier['image_id']) ? GalleryImage::find($tier['image_id']) : null;
                $thumbUrl = null;
                $fullUrls = [];

                if ($image) {
                    $thumbUrl = $image->getUrl('thumb-webp') ?: $image->getUrl('thumb') ?: $image->getUrl();
                    $fullUrls[] = $image->getUrl();
                }

                return [
                    'type'     => strtoupper($tier['type'] ?? $tier['level'] ?? 'SILVER'),
                    'tier_key' => strtolower($tier['tier_key'] ?? $tier['type'] ?? $tier['level'] ?? 'silver'),
                    'name'     => $tier['name'] ?? '',
                    'url'      => $tier['url'] ?? '',
                    'thumb'    => $thumbUrl,
                    'photos'   => $fullUrls,
                ];
            })->values();

            return [
                'day'      => $index + 1,
                'meals'    => trim((string) ($day['meals'] ?? '')),
                'tiers'    => $tiers,
                'hasStay'  => $tiers->isNotEmpty(),
            ];
        })->values();

        /* ── Tour features (only what applies to this tour) ─────────────────── */
        $features = [];
        $levelLabels = ['budget' => 'Budget tour', 'mid_range' => 'Mid-range tour', 'midrange' => 'Mid-range tour', 'luxury' => 'Luxury tour'];
        if ($tour->tour_level && isset($levelLabels[strtolower($tour->tour_level)])) {
            $features[] = ['icon' => 'bed', 'title' => $levelLabels[strtolower($tour->tour_level)], 'text' => 'Specialised' . strtolower($levelLabels[strtolower($tour->tour_level)]) . ' options available'];
        }
        $features[] = ['icon' => 'private', 'title' => 'Private tour', 'text' => 'This tour runs exclusively for your own group'];
        $features[] = ['icon' => 'calendar', 'title' => 'Can start any day', 'text' => 'Flexible departure — pick any date that suits you'];

        /* ── Activities & transportation rows (hidden when nothing applies) ─── */
        $transportRows = [];

        /* ── Pricing (Phase 3: centralised server-side resolver) ─────────────── */
        $seasonPricing = collect($tour->season_pricing ?? []);
        $resolver        = app(TourPriceResolver::class);
        $fromPriceResolved = TourPriceResolver::fromPrice($tour);
        $priceFrom        = $fromPriceResolved['amount'] ?? null;
        $priceCurrency    = $fromPriceResolved['currency'] ?? strtoupper((string) ($tour->currency ?: 'USD'));
        $hasTourPrice     = $fromPriceResolved !== null || $seasonPricing->isNotEmpty();
        $isPriceOnRequest = $fromPriceResolved === null && $seasonPricing->isEmpty();

        // Server-rendered price matrix (new-system tours) + request defaults.
        $priceMatrix     = $resolver->matrixFor($tour);
        $defaultLevel    = $resolver->defaultLevelKey($tour);
        $quotePreview    = $resolver->lookup($tour, ['date' => null, 'group_size' => 2, 'level_key' => $defaultLevel]);
        $priceLookupUrl  = route('tours.priceLookup', $tour->id);

        /* ── Reviews (this tour first, site-wide fallback) ──────────────────── */
        $reviews     = Testimonial::published()->forTour($tour->id)->orderByDesc('is_featured')->orderBy('order')->orderByDesc('id')->get();
        $reviewCount = $reviews->count();
        $ratingAvg   = $reviewCount ? round((float) Testimonial::published()->forTour($tour->id)->avg('rating'), 1) : null;

        $siteReviewCount = Testimonial::published()->count();
        $siteRatingAvg   = $siteReviewCount ? round((float) Testimonial::published()->avg('rating'), 1) : null;

        $displayRating  = $ratingAvg ?? $siteRatingAvg;
        $displayReviews = $reviewCount ?: $siteReviewCount;

        $nameToCode = array_flip(array_map('strtolower', $countryNames));
        $reviews = $reviews->map(function (Testimonial $testimonial) use ($flagFor, $nameToCode, $countryFlag) {
            $location = trim((string) $testimonial->location);
            $code     = $nameToCode[strtolower($location)] ?? null;
            $avatar   = $testimonial->avatarUrl('thumb-webp') ?: $testimonial->avatarUrl();

            return [
                'name'     => $testimonial->name,
                'location' => $location,
                'flag'     => $code ? $flagFor($code) : '',
                'rating'   => max(1, min(5, (int) $testimonial->rating)),
                'content'  => strip_tags((string) $testimonial->content),
                'avatar'   => $avatar,
                'initial'  => mb_strtoupper(mb_substr(trim((string) $testimonial->name), 0, 1)),
            ];
        })->values();

        /* ── Operator (the site owner — real data from Settings) ────────────── */
        $operator = [
            'name'      => Setting::get('site_name', 'Afro-Vertex Tours & Safaris'),
            'logo'      => Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp'),
            'link'      => Setting::get('faq_expert_link', route('home')),
            'location'  => Setting::get('footer_address', ''),
            'founded'   => Setting::get('operator_founded_year', ''),
            'employees' => Setting::get('operator_employees', ''),
            'phone'     => Setting::get('footer_phone', ''),
            'email'     => Setting::get('site_email', ''),
        ];

        /* ── Related links (destination / category / duration / activity) ───── */
        $relatedLinks = [];
        $firstDestination = $tour->destinations->first();
        if ($firstDestination) {
            $relatedLinks[] = ['label' => trim($countryName . ' Safaris'), 'url' => route('tours.index', ['destination' => $firstDestination->slug])];
        }
        foreach ($tour->categories->take(2) as $category) {
            $relatedLinks[] = ['label' => $category->name, 'url' => route('tours.category', ['categorySlug' => $category->slug])];
        }
        if ((int) $tour->duration_days > 0) {
            $days = (int) $tour->duration_days;
            $relatedLinks[] = ['label' => $days . '-Day ' . ($countryName ?: '') . ' Safaris', 'url' => route('tours.index', ['duration_min' => max(1, $days - 1), 'duration_max' => $days + 1])];
        }
        $relatedLinks = collect($relatedLinks)->unique('label')->take(4)->values();

        /* ── Related tours: scored by destination/category/duration/price ── */
        $destIds = $tour->destinations->pluck('id');
        $catIds  = $tour->categories->pluck('id');

        $candidates = TourPackage::query()
            ->where('status', 'published')
            ->where('id', '!=', $tour->id)
            ->with(['destinations', 'categories'])
            ->limit(80)
            ->get();

        $scored = $candidates->map(function (TourPackage $candidate) use ($tour, $destIds, $catIds) {
            $score = 0;
            $score += $candidate->destinations->pluck('id')->intersect($destIds)->count() * 4;
            $score += $candidate->categories->pluck('id')->intersect($catIds)->count() * 3;
            if ((int) $tour->duration_days > 0 && abs((int) $candidate->duration_days - (int) $tour->duration_days) <= 3) {
                $score += 2;
            }
            if ((float) $tour->base_price > 0 && (float) $candidate->base_price > 0
                && abs((float) $candidate->base_price - (float) $tour->base_price) / (float) $tour->base_price <= 0.3) {
                $score += 1;
            }

            return ['tour' => $candidate, 'score' => $score];
        })
            ->sortByDesc('score')
            ->values();

        $relatedTours = $scored->filter(fn ($item) => $item['score'] > 0)->take(6)->pluck('tour');

        // Fallback so the section never renders nearly-empty on small catalogues.
        if ($relatedTours->count() < 3) {
            $fillerIds = $relatedTours->pluck('id')->push($tour->id);
            $fillers   = TourPackage::query()
                ->where('status', 'published')
                ->whereNotIn('id', $fillerIds)
                ->with(['destinations', 'categories'])
                ->orderByDesc('is_featured')
                ->orderBy('order')
                ->limit(6 - $relatedTours->count())
                ->get();
            $relatedTours = $relatedTours->concat($fillers)->values();
        }

        // Centralised "From" prices for the related-tour + minicard displays.
        $relatedFromPrices = TourPriceResolver::fromPriceMap($relatedTours);

        /* ── FAQ section (tour-specific first, else destination, else empty) ── */
        $faqRaw = collect($tour->faqs ?? []);
        if ($faqRaw->isEmpty() && $tour->destinations->isNotEmpty()) {
            $headerDest = $tour->destinations->first();
            $faqRaw = collect($headerDest->faqs ?? []);
        }
        $faqs = $faqRaw
            ->filter(fn ($faq) => trim((string) ($faq['question'] ?? '')) !== ''
                && trim((string) ($faq['answer'] ?? '')) !== '')
            ->map(fn ($faq) => [
                'question' => trim($faq['question']),
                'answer'   => trim($faq['answer']),
            ])
            ->values();
        $faqSubject = $primaryCode === 'KE' ? 'Kenyan' : 'Tanzanian';
        $faqExpertName = Setting::get('faq_expert_name', 'Afro-Vertex Safari Experts');
        $faqExpert = [
            'name'  => $faqExpertName,
            'image' => Setting::imageUrl('faq_expert_image_id')
                ?: (Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp')),
            'bio'   => Setting::get('faq_expert_bio',
                'Born and raised in northern Tanzania, our safari experts have planned hundreds of trips across the Serengeti, Ngorongoro and Kilimanjaro regions. Every answer below reflects years of first-hand guiding experience.'),
            'link'  => Setting::get('faq_expert_link', route('home')),
        ];

        return view('frontend.tours.show', compact(
            'tour', 'relatedTours', 'relatedFromPrices', 'galleryImages', 'safariCarImages', 'heroUrl',
            'countryName', 'countryFlag', 'primaryCode', 'flagFor', 'mapEmbedUrl',
            'itinerary', 'routePoints', 'routeMapDays', 'accommodationRows', 'features', 'transportRows',
            'seasonPricing', 'hasTourPrice', 'priceFrom', 'priceCurrency', 'isPriceOnRequest',
            'priceMatrix', 'defaultLevel', 'quotePreview', 'priceLookupUrl',
            'reviews', 'reviewCount', 'ratingAvg', 'siteReviewCount', 'siteRatingAvg',
            'displayRating', 'displayReviews', 'operator', 'relatedLinks',
            'faqs', 'faqExpert', 'faqSubject'
        ));
    }
}
