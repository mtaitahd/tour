<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;

class DestinationController extends Controller
{
    public function indexPublic(Request $request)
    {
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

        return view('frontend.destinations.index', compact('destinations'));
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
