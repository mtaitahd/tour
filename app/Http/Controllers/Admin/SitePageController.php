<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * Admin screen for the fixed "Site Information" pages.
 *
 * About Us / Why Choose Us / Contact Us / Terms and Conditions describe the site
 * itself rather than being standalone content, so they are edited here instead
 * of being mixed into Admin → Pages → All Pages (which now excludes them).
 *
 * This controller deliberately owns no editing logic of its own: the edit form
 * and the whole update pipeline (validation, Media Library usage tracking, the
 * About-page repeaters, sitemap regeneration) already live in PageController.
 * Reusing them here means a site-information page is saved by exactly the same
 * code as any other page, so the two entry points can never drift apart.
 *
 * @see \App\Models\Page::SITE_INFO_PAGES for the canonical list.
 */
class SitePageController extends Controller
{
    public function create(string $slug)
    {
        abort_unless(array_key_exists($slug, Page::SITE_INFO_PAGES), 404);

        if (Page::where('slug', $slug)->exists()) {
            return redirect()->route('admin.site-pages.edit', $slug);
        }

        return view('admin.pages.create', [
            'creatingSiteInfo' => true,
            'sitePageSlug' => $slug,
            'sitePageLabel' => Page::SITE_INFO_PAGES[$slug],
            'draft' => null,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, Page::SITE_INFO_PAGES), 404);

        if (Page::where('slug', $slug)->exists()) {
            return redirect()->route('admin.site-pages.edit', $slug);
        }

        // The URL identifies one of the fixed site-information pages; keep its
        // canonical slug and label even if the submitted form is altered.
        $request->merge([
            'slug' => $slug,
            'title' => Page::SITE_INFO_PAGES[$slug],
        ]);

        return app(PageController::class)->store($request, 'admin.site-pages.index');
    }

    /**
     * The site-information pages, in display order, each paired with its
     * database row when one exists.
     *
     * A missing row is normal and not an error: "Why Choose Us" is currently a
     * homepage section rather than a page row, so the list has to render even
     * when only some of the four exist. The view offers a create link for those.
     */
    public function index()
    {
        $rows = Page::whereIn('slug', Page::siteInfoSlugs())->get()->keyBy('slug');

        $sitePages = collect(Page::SITE_INFO_PAGES)
            ->map(fn (string $label, string $slug) => [
                'slug'  => $slug,
                'label' => $label,
                'page'  => $rows->get($slug),
            ])
            ->values();

        return view('admin.site-pages.index', compact('sitePages'));
    }

    public function edit(string $slug)
    {
        $page = $this->findSitePage($slug);

        // Reuse the standard page form verbatim. 'editingSiteInfo' lets the form
        // send the save back here instead of to the generic pages list, so the
        // user lands where they started.
        return view('admin.pages.edit', [
            'page'            => $page,
            'editingSiteInfo' => true,
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $page = $this->findSitePage($slug);

        return app(PageController::class)->update($request, $page, 'admin.site-pages.index');
    }

    /**
     * Resolve a site-information slug to its row, refusing anything outside the
     * fixed list so this screen can never be used to edit an ordinary page.
     */
    private function findSitePage(string $slug): Page
    {
        abort_unless(array_key_exists($slug, Page::SITE_INFO_PAGES), 404);

        return Page::where('slug', $slug)->firstOrFail();
    }
}
