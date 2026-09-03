<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupDeparture;
use App\Models\TourPackage;

class GroupDepartureController extends Controller
{
    public function publicIndex(Request $request)
    {
        $query = GroupDeparture::with(['tour'])
                               ->where('status', '!=', 'cancelled')
                               ->where('departure_date', '>=', now()->startOfDay())
                               ->orderBy('departure_date');

        // Filter by month/year
        if ($request->filled('month')) {
            $query->whereMonth('departure_date', $request->month)
                  ->whereYear('departure_date', $request->year ?? now()->year);
        }

        // Filter by tour
        if ($request->filled('tour')) {
            $query->where('tour_package_id', $request->tour);
        }

        $departures = $query->paginate(12)->withQueryString();

        $tours = TourPackage::where('status', 'published')
                            ->orderBy('title')
                            ->get();

        return view('frontend.group-departures.index', compact('departures', 'tours'));
    }
}
