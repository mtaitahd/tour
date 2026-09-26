<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Public "Pages" listing (/pages) — the single index of every published
 * standalone page (About, Contact, Terms, Privacy, etc.), built and styled to
 * match the /tours listing.
 *
 * NOTE: the public show() for /pages/{slug} still lives in
 * App\Http\Controllers\Admin\PageController (see routes/web.php) — that is
 * pre-existing and left untouched here; this class only adds the listing that
 * was missing.
 */
class PageController extends Controller
{
    /**
     * Pages per page on the public listing. Server-side pagination only —
     * the query is paginated at the database level, never on a loaded collection.
     */
    public const PAGES_PER_PAGE = 12;

    public function index(Request $request)
    {
        // Same guard rails as TourController::index(): a bad query string must
        // never loop, so strip the offending top-level parameters and redirect
        // instead of falling back to the default "back" redirect, which would
        // re-send the browser to the very same invalid URL.
        $validator = Validator::make($request->query(), [
            'page'   => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $offending = collect(array_keys($validator->failed()))
                ->map(fn ($key) => explode('.', $key)[0])
                ->unique()
                ->all();

            return redirect()->to($request->fullUrlWithoutQuery($offending), 302);
        }

        // Non-numeric/zero/negative ?page= values get a clean URL without the
        // parameter (the paginator would otherwise silently clamp them to 1).
        $rawPage = $request->query('page');
        if ($rawPage !== null && $rawPage !== ''
            && (filter_var($rawPage, FILTER_VALIDATE_INT) === false || (int) $rawPage < 1)) {
            return redirect()->to($request->fullUrlWithoutQuery(['page']), 302);
        }

        $query = Page::where('status', 'published')
                     ->with('heroImage')
                     ->orderBy('order')
                     ->orderBy('title');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('slug', 'like', $term)
                  ->orWhere('content', 'like', $term);
            });
        }

        $pages = $query->paginate(self::PAGES_PER_PAGE)->withQueryString();

        return view('frontend.pages.index', [
            'pages'  => $pages,
            'search' => $search,
        ]);
    }
}
