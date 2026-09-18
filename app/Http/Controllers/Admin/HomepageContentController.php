<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\Setting;
use App\Models\TourCategory;

/**
 * Admin editors for homepage content that has no dedicated module yet:
 * section buttons & captions, the Traveller Stories (map + Tripadvisor) section,
 * the YouTube subscribe section, the footer link groups, and tour starting points.
 *
 * All values are stored through the existing Setting model (get/set/json), so the
 * public views read them with the same Setting::get(...) fallback convention used
 * everywhere else.
 */
class HomepageContentController extends Controller
{
    /* ── 1 & 2: Homepage section buttons + captions ─────────────────────── */

    public function home()
    {
        return view('admin.settings.homepage');
    }

    public function updateHome(Request $request)
    {
        $request->validate([
            'settings'       => 'required|array',
            'settings.*'     => 'nullable|string|max:1000',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::set($key, trim((string) $value));
        }

        return redirect()->route('admin.homepage-content')
                         ->with('success', 'Homepage buttons & captions updated successfully!');
    }

    /* ── 3: Traveller Stories (left map embed, right Tripadvisor) ──────── */

    public function travellers()
    {
        // Seed sensible defaults the first time the editor is opened so the
        // homepage section works even before anything is saved.
        if (! Setting::get('traveller_review_link')) {
            Setting::set('traveller_review_link', 'https://g.page/r/CR7qe8CBnNH7EBM/review');
        }
        if (! Setting::get('traveller_map_heading')) {
            Setting::set('traveller_map_heading', 'Where We Are');
        }
        if (! Setting::get('traveller_tripadvisor_heading')) {
            Setting::set('traveller_tripadvisor_heading', 'Loved by Travellers on Tripadvisor');
        }

        return view('admin.settings.travellers');
    }

    public function updateTravellers(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::set($key, trim((string) $value));
        }

        return redirect()->route('admin.traveller-stories')
                         ->with('success', 'Traveller Stories section updated successfully!');
    }

    /* ── 4: YouTube Subscribe section ──────────────────────────────────── */

    public function subscribe()
    {
        if (! Setting::get('subscribe_youtube_url') && Setting::get('social_youtube')) {
            Setting::set('subscribe_youtube_url', Setting::get('social_youtube'));
        }

        return view('admin.settings.subscribe');
    }

    public function updateSubscribe(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::set($key, trim((string) $value));
        }

        // Checkbox: always persist an explicit 0/1 so unchecking actually unhides.
        Setting::set('subscribe_hidden', $request->boolean('settings.subscribe_hidden') ? '1' : '0');

        return redirect()->route('admin.subscribe-content')
                         ->with('success', 'Subscribe section updated successfully!');
    }

    /* ── 5: Footer link groups ─────────────────────────────────────────── */

    public function footer()
    {
        // Seed the four link groups with the site's current defaults the first
        // time the editor is opened, so the footer keeps its existing content.
        if (! Setting::get('footer_links_park')) {
            Setting::set('footer_links_park', json_encode([
                ['label' => 'Maasai Mara Game Reserve Safaris', 'url' => '/maasai-mara'],
                ['label' => 'Mount Kilimanjaro Treks',          'url' => '/mount-kilimanjaro'],
                ['label' => 'Serengeti National Park Safaris', 'url' => '/serengeti-national-park'],
            ]));
        }

        if (! Setting::get('footer_links_country')) {
            Setting::set('footer_links_country', json_encode([
                ['label' => 'Tanzania Safaris', 'url' => '/destinations?country=TZ'],
                ['label' => 'Kenya Safaris',    'url' => '/destinations?country=KE'],
            ]));
        }

        if (! Setting::get('footer_links_type')) {
            $typeDefaults = collect();
            foreach (TourCategory::orderBy('name')->get() as $category) {
                $typeDefaults->push(['label' => $category->name, 'url' => '/' . $category->slug]);
            }
            if ($typeDefaults->isEmpty()) {
                $typeDefaults->push(['label' => 'Kilimanjaro Climbing', 'url' => '/kilimanjaro-climbing-package']);
                $typeDefaults->push(['label' => 'Tanzania Tours',       'url' => '/tanzania-tours']);
            }
            Setting::set('footer_links_type', json_encode($typeDefaults->values()->all()));
        }

        if (! Setting::get('footer_links_general')) {
            Setting::set('footer_links_general', json_encode([
                ['label' => 'About Us',              'url' => '/pages/about-us'],
                ['label' => 'Why Choose Us',         'url' => route('home') . '#why-choose-us'],
                ['label' => 'Contact Us',            'url' => '/pages/contact'],
                ['label' => 'Blog',                  'url' => '/blog'],
                ['label' => 'Terms and Conditions',  'url' => '/pages/terms-and-conditions'],
            ]));
        }

        return view('admin.settings.footer');
    }

    public function updateFooter(Request $request)
    {
        $groups = ['park', 'country', 'type', 'general'];

        $request->validate([
            'settings'            => 'nullable|array',
            'settings.*'          => 'nullable|string|max:1000',
            'footer_groups'       => 'nullable|array',
            'footer_groups.*'     => 'nullable|array',
            'footer_groups.*.*.label' => 'nullable|string|max:255',
            'footer_groups.*.*.url'   => 'nullable|string|max:500',
        ]);

        // Plain text settings first (headings, copyright labels, ...).
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                Setting::set($key, trim((string) $value));
            }
        }

        // JSON link groups.
        foreach ($groups as $group) {
            if (! $request->has("footer_groups.$group")) {
                continue;
            }

            $links = [];
            foreach ($request->input("footer_groups.$group", []) as $item) {
                $label = trim((string) ($item['label'] ?? ''));
                $url   = trim((string) ($item['url'] ?? ''));
                if ($label !== '' || $url !== '') {
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }

            Setting::set('footer_links_' . $group, json_encode($links));
        }

        return redirect()->route('admin.footer-settings')
                         ->with('success', 'Footer settings updated successfully!');
    }

    /* ── 7: Tour starting points ───────────────────────────────────────── */

    public function startingPoints()
    {
        // Fall back to the distinct starting points currently used by published
        // tours so the editor is never empty on first visit.
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

        return view('admin.settings.starting-points');
    }

    public function updateStartingPoints(Request $request)
    {
        $request->validate([
            'starting_points'   => 'nullable|array',
            'starting_points.*' => 'nullable|string|max:255',
        ]);

        $points = collect($request->input('starting_points', []))
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->unique()
            ->values()
            ->all();

        Setting::set('starting_points', json_encode($points));

        return redirect()->route('admin.starting-points')
                         ->with('success', 'Starting points updated successfully!');
    }
}