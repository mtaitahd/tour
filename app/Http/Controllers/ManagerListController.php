<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\ManagerList;
use App\Models\Page;
use App\Models\Setting;
use App\Models\TourCategory;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerListController extends Controller
{
    public function show(string $slug)
    {
        $managerList = ManagerList::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return $this->renderListing($managerList, request());
    }

    /** This action is registered only inside the permission-protected admin routes. */
    public function preview(ManagerList $managerList)
    {
        // Prevent search engines from indexing this admin-only rendering,
        // including when the listing itself is already published.
        $managerList->setAttribute('no_robots', true);
        return $this->renderListing($managerList, request(), true);
    }

    private function renderListing(ManagerList $managerList, Request $request, bool $previewMode = false)
    {
        $items = collect();
        $filters = [];
        $facets = [];

        if ($managerList->content_type === 'tours') {
            $rules = [
                'page' => ['nullable', 'integer', 'min:1'],
                'search' => ['nullable', 'string', 'max:255'],
                'duration_min' => ['nullable', 'integer', 'min:1'],
                'duration_max' => ['nullable', 'integer', 'min:1', 'gte:duration_min'],
                'price_min' => ['nullable', 'numeric', 'min:0'],
                'price_max' => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
                'categories' => ['nullable', 'array'],
                'categories.*' => ['string', 'max:255'],
                'accommodation' => ['nullable', 'array'],
                'accommodation.*' => ['in:camping,lodge'],
                'luxury' => ['nullable', 'array'],
                'luxury.*' => ['in:budget,mid_range,luxury'],
                'countries' => ['nullable', 'array'],
                'countries.*' => ['string', 'size:2'],
                'parks' => ['nullable', 'array'],
                'parks.*' => ['integer'],
                'starting_point' => ['nullable', 'string', 'max:255'],
            ];
            $validator = Validator::make($request->query(), $rules);
            if ($validator->fails()) {
                $offending = collect(array_keys($validator->failed()))
                    ->map(fn ($key) => explode('.', $key)[0])->unique()->all();
                return redirect()->to($request->fullUrlWithoutQuery($offending), 302);
            }

            $categoryIds = array_map('intval', $managerList->category_ids ?: []);
            $baseQuery = TourPackage::where('status', 'published')
                ->where('no_robots', false)
                ->whereHas('categories', fn ($query) => $query->whereIn('tour_categories.id', $categoryIds ?: [0]));
            $query = (clone $baseQuery)->with(['categories', 'destinations'])
                ->orderByDesc('is_featured')->orderBy('order')->orderBy('title');

            if ($request->filled('search')) {
                $term = '%' . trim($request->input('search')) . '%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('overview', 'like', $term));
            }
            if ($request->filled('duration_min')) $query->where('duration_days', '>=', $request->integer('duration_min'));
            if ($request->filled('duration_max')) $query->where('duration_days', '<=', $request->integer('duration_max'));
            if ($request->filled('price_min')) $query->where('base_price', '>=', $request->input('price_min'));
            if ($request->filled('price_max')) $query->where('base_price', '<=', $request->input('price_max'));
            if ($request->filled('categories')) {
                $slugs = array_values(array_intersect((array) $request->input('categories'), TourCategory::whereIn('id', $categoryIds)->pluck('slug')->all()));
                if ($slugs) {
                    $query->whereHas('categories', fn ($q) => $q->whereIn('tour_categories.slug', $slugs));
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
            if ($request->filled('accommodation')) {
                $query->where(function ($q) use ($request) {
                    foreach ((array) $request->input('accommodation') as $style) {
                        $q->orWhere('tour_level', 'like', $style === 'camping' ? '%camping%' : '%lodge%');
                    }
                });
            }
            if ($request->filled('luxury')) {
                $query->where(function ($q) use ($request) {
                    foreach ((array) $request->input('luxury') as $level) {
                        $q->orWhere('tour_level', $level === 'budget' ? 'like' : '=', $level === 'budget' ? 'budget%' : $level);
                    }
                });
            }
            if ($request->filled('countries')) {
                $query->whereHas('destinations', fn ($q) => $q->whereIn('destinations.country_code', (array) $request->input('countries')));
            }
            if ($request->filled('parks')) {
                $query->whereHas('destinations', fn ($q) => $q->whereIn('destinations.id', array_map('intval', (array) $request->input('parks'))));
            }
            if ($request->filled('starting_point')) $query->where('starting_point', $request->input('starting_point'));

            $items = $query->paginate(10)->withQueryString();
            $scopedTourIds = (clone $baseQuery)->select('tour_packages.id');
            $categories = TourCategory::whereIn('id', $categoryIds)
                ->whereHas('tourPackages', fn ($q) => $q->whereIn('tour_packages.id', clone $scopedTourIds))
                ->orderBy('order')->orderBy('name')->get();
            $allDestinations = Destination::whereHas('tours', fn ($q) => $q->whereIn('tour_packages.id', clone $scopedTourIds))
                ->withCount(['tours' => fn ($q) => $q->whereIn('tour_packages.id', clone $scopedTourIds)])
                ->orderBy('name')->get();
            $countries = $allDestinations->whereNotNull('country_code')->groupBy('country_code')
                ->map(fn ($group, $code) => (object) [
                    'code' => $code,
                    'name' => ['TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda'][$code] ?? $code,
                    'count' => $group->sum('tours_count'),
                ])->sortByDesc('count')->values();
            $parks = $allDestinations->filter(fn ($destination) => ! in_array(strtolower((string) $destination->type), ['country', 'region', 'continent'], true))->values();
            if ($parks->isEmpty()) $parks = $allDestinations;
            $startingPoints = (clone $baseQuery)->whereNotNull('starting_point')->where('starting_point', '!=', '')
                ->distinct()->orderBy('starting_point')->pluck('starting_point');
            $durationValues = (clone $baseQuery)->whereNotNull('duration_days')->pluck('duration_days');
            $priceValues = (clone $baseQuery)->whereNotNull('base_price')->pluck('base_price');
            $facets = compact('categories', 'countries', 'parks', 'startingPoints');
            $facets['durationMax'] = max(1, min(28, (int) ($durationValues->max() ?: 14)));
            $facets['priceMin'] = (int) ($priceValues->min() ?: 0);
            $facets['priceMax'] = max($facets['priceMin'] + 1, (int) ($priceValues->max() ?: 5000));
            $filters = $request->query();
        } else {
            $items = Page::where('status', 'published')
                ->where('no_robots', false)
                ->whereIn('id', $managerList->page_ids ?: [0])
                ->whereNotIn('slug', Page::siteInfoSlugs())
                ->orderBy('order')->orderBy('title')->paginate(12);
        }

        $faqs = collect($managerList->faqs ?? [])->filter(fn ($faq) => trim((string) ($faq['question'] ?? '')) !== '' && trim((string) ($faq['answer'] ?? '')) !== '')->values();
        $faqExpertName = Setting::get('faq_expert_name', 'Afro-Vertex Safari Experts');
        $faqExpert = [
            'name' => $faqExpertName,
            'image' => Setting::imageUrl('faq_expert_image_id') ?: Setting::logoUrlOrDefault(),
            'bio' => Setting::get('faq_expert_bio', 'Our local travel experts share practical answers based on years of planning journeys across East Africa.'),
            'link' => Setting::get('faq_expert_link', route('home')),
        ];
        $meta = [
            'title' => $managerList->meta_title ?: $managerList->title . ' | Afro-Vertex Tours & Safaris',
            'description' => $managerList->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($managerList->caption ?: $managerList->introduction)), 160),
            'keywords' => $managerList->meta_keywords ?: 'East Africa tours, safaris, travel information',
            'canonical' => route('manager-lists.show', $managerList->slug),
            'no_robots' => $managerList->no_robots,
        ];

        return view('frontend.manager-lists.show', compact('managerList', 'items', 'faqs', 'faqExpert', 'meta', 'previewMode', 'filters', 'facets'));
    }
}
