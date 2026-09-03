<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\TourPackage;
use App\Models\Destination;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Tours
        $totalTours = TourPackage::count();
        $newToursThisMonth = TourPackage::whereMonth('created_at', now()->month)->count();

        // Inquiries (assuming you have an Inquiry model)
        $totalInquiriesThisMonth = Inquiry::whereMonth('created_at', now()->month)->count();
        $newInquiriesToday = Inquiry::whereDate('created_at', now()->today())->count();
        $pendingInquiries = Inquiry::where('status', 'new')->count();

        // Customers (registered users or inquiry senders)
        $totalCustomers = User::count(); // adjust based on your user roles
        $newCustomersThisMonth = User::whereMonth('created_at', now()->month)->count();

        // Revenue (if you have paid bookings)
        $confirmedThisMonth = Inquiry::where('status', 'confirmed') // or 'booking', 'paid' — choose your final status
                                     ->whereMonth('updated_at', now()->month) // better to use updated_at for revenue
                                     ->whereYear('updated_at', now()->year);

        $revenueThisMonth = $confirmedThisMonth->sum('total_amount') ?? 0;

        // Last month
        $revenueLastMonth = Inquiry::where('status', 'confirmed')
                                   ->whereMonth('updated_at', now()->subMonth()->month)
                                   ->whereYear('updated_at', now()->subMonth()->year)
                                   ->sum('total_amount') ?? 0;

        $revenueGrowthPercent = $revenueLastMonth > 0 
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) 
            : 0;

        // Recent Inquiries
        $recentInquiries = Inquiry::with(['tour'])
                                  ->latest()
                                  ->take(10)
                                  ->get();

        // Recent Activities (simple log or combined inquiries/bookings)
        $recentActivities = Inquiry::latest()->take(5)->get()->map(function($inquiry) {
            return (object) [
                'created_at' => $inquiry->created_at,
                'type' => 'inquiry',
                'description' => "New inquiry from <strong>{$inquiry->name}</strong> for <strong>" . ($inquiry->tour?->title ?? $inquiry->destination?->name ?? 'General') . "</strong>"
            ];
        });

        // Inquiries Trend (monthly totals — last 12 months, oldest → newest)
        $inquiriesTrend = Inquiry::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as count")
                                 ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                                 ->groupBy('ym')
                                 ->orderBy('ym')
                                 ->get();

        $inquiriesTrendMonths = $inquiriesTrend->pluck('ym')->toArray();
        $inquiriesTrendData   = $inquiriesTrend->pluck('count')->toArray();

        // Build "May 2026"-style labels — filling in empty months so the chart
        // axis always runs continuously even when a month has zero inquiries.
        $monthLabels = [];
        $monthData = [];
        $start = now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i <= 11; $i++) {
            $cursor = $start->copy()->addMonths($i);
            $ym = $cursor->format('Y-m');
            $monthLabels[] = $cursor->format('M Y');
            $monthData[] = $inquiriesTrend->firstWhere('ym', $ym)->count ?? 0;
        }
        $inquiriesTrendDates = $monthLabels;
        $inquiriesTrendData = $monthData;

        // Featured Tours Count
        $featuredToursCount = TourPackage::where('is_featured', true)->count();

        return view('admin.dashboard.index', compact(
            'totalTours', 'newToursThisMonth',
            'totalInquiriesThisMonth', 'newInquiriesToday', 'pendingInquiries',
            'totalCustomers', 'newCustomersThisMonth',
            'revenueThisMonth', 'revenueGrowthPercent',
            'recentInquiries', 'recentActivities',
            'inquiriesTrendDates', 'inquiriesTrendData',
            'featuredToursCount'
        ));
    }
}
