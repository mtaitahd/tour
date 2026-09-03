<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Page;
use App\Models\TourCategory;
use App\Models\TourPackage;
use Illuminate\Support\Facades\File;

/**
 * Sitemap.xml generator.
 *
 * Mirrors the SEO behaviour of the legacy kizza-tours platform: builds a single
 * flat urlset that lists static routes plus published Tours, Destinations,
 * Pages and Blog posts — each with a priority / changefreq / lastmod — while
 * honouring each entity's `no_robots` (noindex) flag so admins can keep
 * specific items out of Google's index.
 */
class SitemapGenerator
{
    /**
     * Static (non-model) URLs. Priority mirrors the kizza reference:
     * home is the highest, then listing pages.
     */
    protected static function staticUrls(): array
    {
        return [
            ['loc' => route('home'),                'priority' => '1.0', 'changefreq' => 'weekly',   'lastmod' => static::globalLastmod()],
            ['loc' => route('tours.index'),         'priority' => '0.9', 'changefreq' => 'weekly',   'lastmod' => static::globalLastmod()],
            ['loc' => route('destinations.index'),  'priority' => '0.8', 'changefreq' => 'weekly',   'lastmod' => static::globalLastmod()],
            ['loc' => route('blog.index'),          'priority' => '0.7', 'changefreq' => 'weekly',   'lastmod' => static::globalLastmod()],
            ['loc' => route('accommodations.index'),'priority' => '0.6', 'changefreq' => 'monthly',  'lastmod' => static::globalLastmod()],
        ];
    }

    /**
     * Newest content on the site is used as the lastmod for the static pages,
     * so search engines see the sitemap as fresh.
     */
    protected static function globalLastmod(): string
    {
        $latest = collect([
            TourPackage::where('status', 'published')->max('updated_at'),
            Destination::max('updated_at'),
            Page::where('status', 'published')->max('updated_at'),
            BlogPost::where('status', 'published')->max('updated_at'),
        ])->filter()->max();

        return $latest ? \Illuminate\Support\Carbon::parse($latest)->format('Y-m-d') : date('Y-m-d');
    }

    /**
     * Every public URL entry as [loc, lastmod, changefreq, priority].
     */
    public static function entries(): array
    {
        $entries = static::staticUrls();

        foreach (TourPackage::where('status', 'published')
                     ->where('no_robots', false)
                     ->get() as $tour) {
            $entries[] = [
                'loc'       => route('tour.show', $tour->slug),
                'priority'  => '0.8',
                'changefreq'=> 'weekly',
                'lastmod'   => $tour->updated_at?->format('Y-m-d') ?: date('Y-m-d'),
            ];
        }

        foreach (TourCategory::where('no_robots', false)
                     ->whereHas('tourPackages', fn ($q) => $q->where('status', 'published'))
                     ->get() as $category) {
            $entries[] = [
                'loc'       => route('tours.category', $category->slug),
                'priority'  => '0.7',
                'changefreq'=> 'weekly',
                'lastmod'   => $category->updated_at?->format('Y-m-d') ?: date('Y-m-d'),
            ];
        }

        foreach (Destination::whereNotNull('slug')->where('slug', '!=', '')->get() as $dest) {
            $entries[] = [
                'loc'       => route('destination.show', $dest->slug),
                'priority'  => '0.6',
                'changefreq'=> 'monthly',
                'lastmod'   => $dest->updated_at?->format('Y-m-d') ?: date('Y-m-d'),
            ];
        }

        foreach (Page::where('status', 'published')
                     ->where('no_robots', false)
                     ->get() as $page) {
            $entries[] = [
                'loc'       => route('page.show', $page->slug),
                'priority'  => '0.6',
                'changefreq'=> 'monthly',
                'lastmod'   => $page->updated_at?->format('Y-m-d') ?: date('Y-m-d'),
            ];
        }

        foreach (BlogPost::where('status', 'published')
                     ->where('no_robots', false)
                     ->get() as $post) {
            $entries[] = [
                'loc'       => route('blog.show', $post->slug),
                'priority'  => '0.6',
                'changefreq'=> 'monthly',
                'lastmod'   => $post->updated_at?->format('Y-m-d') ?: date('Y-m-d'),
            ];
        }

        return $entries;
    }

    /**
     * Build the full sitemap.xml string.
     */
    public static function build(): string
    {
        $entries = static::entries();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($entries as $e) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($e['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . htmlspecialchars($e['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
            $xml .= '    <changefreq>' . htmlspecialchars($e['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
            $xml .= '    <priority>' . htmlspecialchars($e['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>' . "\n";
        return $xml;
    }

    /**
     * Write sitemap.xml into the web root (public/). Returns true on success.
     */
    public static function generate(): bool
    {
        return File::put(public_path('sitemap.xml'), static::build()) !== false;
    }

    /**
     * Path of the generated static sitemap file (useful for the admin UI).
     */
    public static function filePath(): string
    {
        return public_path('sitemap.xml');
    }
}
