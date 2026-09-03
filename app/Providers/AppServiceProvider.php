<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Destination;
use App\Models\TourCategory;
use App\Pricing\LevelCatalog;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\MediaLibrary\CustomUrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LevelCatalog::class, function ($app) {
            return LevelCatalog::fromConfig(config('tour.level_catalog'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Media::saving(function (Media $media) {
            $media->disk = 'public';
        });
        View::composer('frontend.layouts.app', function ($view) {
            $meta = [
                'title' => 'Afro-Vertex Tours & Safaris',
                'description' => 'Discover the best safaris, climbs, and beach holidays in East Africa.',
                'keywords' => 'safari, kilimanjaro, zanzibar, tanzania tours, africa travel',
                'og_image' => asset('assets/img/og-default.jpg'),
                'canonical'   => request()->url(),
            ];

            // Override for specific routes/pages
            if (request()->routeIs('blog.index')) {
                $meta['title'] = 'Blog & Travel Stories | Afro-Vertex Tours & Safaris';
                $meta['description'] = 'Read our latest travel stories, safari tips, Kilimanjaro climbing guides, Zanzibar beach advice, and more.';
                $meta['keywords'] = 'travel blog, safari tips, kilimanjaro guide, tanzania travel, africa adventures';
                $meta['canonical']   = route('blog.index'); // always point to clean /blog
            } elseif (request()->routeIs('tours.index')) {
                $meta['title'] = 'Tours & Safaris | Afro-Vertex Tours & Safaris';
                $meta['description'] = 'Explore our wide range of Africa safaris, Kilimanjaro climbs, Zanzibar beaches, and group departures.';
                $meta['keywords'] = 'africa safaris, kilimanjaro trekking, zanzibar tours, tanzania travel packages';
                $meta['canonical']   = route('tours.index');
            } elseif (request()->routeIs('destinations.index')) {
                $meta['title'] = 'Destinations | Afro-Vertex Tours & Safaris';
                $meta['description'] = 'Discover East Africa’s most breathtaking destinations – Serengeti, Ngorongoro, Zanzibar, Kilimanjaro, and more.';
                $meta['keywords'] = 'serengeti national park, ngorongoro crater, zanzibar islands, kilimanjaro, east africa destinations';
                $meta['canonical']   = route('destinations.index');
            }
            elseif (request()->routeIs('home')) {
                $meta['title'] = 'Afro-Vertex Tours & Safaris - Best Africa Safaris & Adventures';
                $meta['description'] = 'Experience unforgettable safaris, Kilimanjaro climbs, Zanzibar beaches, and gorilla trekking with Afro-Vertex Tours.';
                $meta['keywords'] = 'safari, kilimanjaro, zanzibar, tanzania tours, africa travel';
                $meta['canonical']   = route('home');
            }

            $tanzaniaDestinations = Destination::where('country_code', 'TZ')
                                               ->orderBy('order')
                                               ->orderBy('name')
                                               ->take(6)
                                               ->get();

            $otherDestinations = Destination::where('country_code', '!=', 'TZ')
                                            ->orderBy('country_code')
                                            ->orderBy('order')
                                            ->orderBy('name')
                                            ->take(6)
                                            ->get();

            // Categories shown in the header's "All Tours" dropdown (replacing the
            // former Activities mega-menu) and the footer's tour-listing links
            // (Tanzania Tours, Kenya Safari, Kilimanjaro Climbing Package, etc.) —
            // only categories that actually have at least one published tour, so
            // neither menu ever links to an empty listing page. Data-driven rather
            // than hardcoded, so adding a category in the admin automatically makes
            // it appear in both places. Same query shared under both variable names
            // rather than run twice for what's the same data.
            $tourCategoriesForNav = TourCategory::withPublishedTours()
                ->orderBy('order')
                ->orderBy('name')
                ->get();

            $view->with([
                'meta'=> $meta,
                'tanzaniaDestinations' => $tanzaniaDestinations,
                'otherDestinations'    => $otherDestinations,
                'footerTourCategories' => $tourCategoriesForNav,
                'navTourCategories' => $tourCategoriesForNav,
            ]);
        });
    }
}
