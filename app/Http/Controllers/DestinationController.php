<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;

class DestinationController extends Controller
{
    public function indexPublic(Request $request)
    {
        // Filter metadata computed from the full set so checkboxes show real totals.
        $countryCounts = Destination::query()
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->selectRaw('country_code, count(*) as total')
            ->pluck('total', 'country_code');

        $typeCounts = Destination::query()
            ->whereNotNull('type')
            ->groupBy('type')
            ->selectRaw('type, count(*) as total')
            ->pluck('total', 'type');

        $featuredCount = Destination::where('is_featured', true)->count();
        $destinationsTotal = Destination::count();

        $typeNames = [
            'national_park' => 'National Park',
            'mountain'      => 'Mountain',
            'beach'         => 'Beach',
            'lake'          => 'Lake',
            'city'          => 'City',
            'village'       => 'Village',
        ];

        $query = Destination::query();

        // Optional filters
        if ($request->filled('country')) {
            $query->where('country_code', $request->country);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        $destinations = $query->orderBy('order')
                              ->orderBy('name')
                              ->paginate(12)
                              ->withQueryString();

        return view('frontend.destinations.index', compact(
            'destinations',
            'countryCounts',
            'typeCounts',
            'featuredCount',
            'destinationsTotal',
            'typeNames'
        ));
    }
    public function showPublic($slug)
    {
        $destination = Destination::where('slug', $slug)->firstOrFail();

        // Load related tours (published only), paginated. Uses a distinct page-name
        // ('tours_page') instead of the default 'page' so this doesn't collide if the
        // destination page ever grows a second paginated section on the same URL.
        $relatedTours = $destination->tours()
                                    ->where('status', 'published')
                                    ->orderBy('order')
                                    ->orderBy('title')
                                    ->paginate(6, ['*'], 'tours_page')
                                    ->withQueryString();

        // Other featured destinations -> "Related Links" style sidebar.
        $relatedLinks = Destination::where('id', '<>', $destination->id)
                                    ->where('is_featured', true)
                                    ->orderBy('order')
                                    ->orderBy('name')
                                    ->limit(6)
                                    ->get(['id', 'name', 'slug', 'country_code'])
                                    ->map(function ($d) {
                                        return [
                                            'label' => $d->name,
                                            'url'   => route('destination.show', $d->slug),
                                        ];
                                    });

        return view('frontend.destinations.show', compact('destination', 'relatedTours', 'relatedLinks'))
            ->with('fromPrices', \App\Services\TourPriceResolver::fromPriceMap($relatedTours));
    }
}
