<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Page;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Services\SitemapGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitemapController extends Controller
{
    public function index(): View
    {
        $filePath = SitemapGenerator::filePath();

        $counts = [
            'tours'        => TourPackage::where('status', 'published')->where('no_robots', false)->count(),
            'categories'   => TourCategory::where('no_robots', false)
                                 ->whereHas('tourPackages', fn ($q) => $q->where('status', 'published'))
                                 ->count(),
            'destinations' => Destination::whereNotNull('slug')->where('slug', '!=', '')->count(),
            'pages'        => Page::where('status', 'published')->where('no_robots', false)->count(),
            'blog'         => BlogPost::where('status', 'published')->where('no_robots', false)->count(),
            'total'        => count(SitemapGenerator::entries()),
        ];

        return view('admin.sitemap.index', [
            'counts'    => $counts,
            'fileExists'=> file_exists($filePath),
            'filePath'  => $filePath,
            'fileUrl'   => asset('sitemap.xml'),
            'lastMod'   => file_exists($filePath) ? date('Y-m-d H:i', filemtime($filePath)) : null,
        ]);
    }

    public function generate(): RedirectResponse
    {
        $ok = SitemapGenerator::generate();

        return back()->with(
            $ok ? 'success' : 'error',
            $ok
                ? 'Sitemap generated successfully (' . count(SitemapGenerator::entries()) . ' URLs written).'
                : 'Sitemap generation failed — check the public/ directory is writable.'
        );
    }
}
