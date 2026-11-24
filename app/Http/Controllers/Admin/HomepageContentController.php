<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\TourPackage;

/** Admin editor for tour starting and ending point suggestions. */
class HomepageContentController extends Controller
{
    /* ── 7: Tour starting & ending points ──────────────────────────────── */

    public function startEndPoints()
    {
        // Fall back to the distinct points currently used by published tours
        // so the editors are never empty on first visit.
        if (! Setting::get('starting_points')) {
            $existing = TourPackage::whereNotNull('starting_point')
                ->where('starting_point', '!=', '')
                ->distinct()
                ->orderBy('starting_point')
                ->pluck('starting_point')
                ->values()
                ->all();

            Setting::set('starting_points', json_encode(array_values(array_filter($existing))));
        }

        if (! Setting::get('ending_points')) {
            $existing = TourPackage::whereNotNull('ending_point')
                ->where('ending_point', '!=', '')
                ->distinct()
                ->orderBy('ending_point')
                ->pluck('ending_point')
                ->values()
                ->all();

            Setting::set('ending_points', json_encode(array_values(array_filter($existing))));
        }

        return view('admin.settings.start-end-points');
    }

    public function updateStartEndPoints(Request $request)
    {
        $request->validate([
            'starting_points'   => 'nullable|array',
            'starting_points.*' => 'nullable|string|max:255',
            'ending_points'     => 'nullable|array',
            'ending_points.*'   => 'nullable|string|max:255',
        ]);

        $startPoints = collect($request->input('starting_points', []))
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $endPoints = collect($request->input('ending_points', []))
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->unique()
            ->values()
            ->all();

        Setting::set('starting_points', json_encode($startPoints));
        Setting::set('ending_points', json_encode($endPoints));

        return redirect()->route('admin.start-end-points')
                         ->with('success', 'Start and end points updated successfully!');
    }
}