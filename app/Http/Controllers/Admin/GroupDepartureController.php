<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupDeparture;

class GroupDepartureController extends Controller
{
    public function calendar()
    {
        // Optional: pass filters from query string if you want
        $departures = GroupDeparture::with('tour')
                                    ->orderBy('departure_date')
                                    ->get();

        // Format events for FullCalendar
        $events = $departures->map(function ($dep) {
            $color = match ($dep->status) {
                'guaranteed' => '#28a745',     // green
                'limited'    => '#ffc107',     // yellow
                'sold_out'   => '#dc3545',     // red
                'cancelled'  => '#6c757d',     // gray
                default      => '#007bff',     // blue (open)
            };

            return [
                'title'      => $dep->tour?->title ?? 'Untitled Tour',
                'start'      => $dep->departure_date->format('Y-m-d'),
                'end'        => $dep->return_date ? $dep->return_date->addDay()->format('Y-m-d') : null,
                'url'        => route('admin.tour-packages.edit', $dep->tour_package_id),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#fff',
                'extendedProps'   => [
                    'spots'    => $dep->spotsLeft() . ' / ' . $dep->total_spots,
                    'status'   => ucfirst($dep->status),
                    'price'    => $dep->group_price ? '$' . number_format($dep->group_price, 0) : 'As per tour',
                ],
            ];
        });

        return view('admin.group-departures.calendar', compact('events'));
    }
}
