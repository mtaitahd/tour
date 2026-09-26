<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Public "Pages" listing (/pages) — the index of published standalone content
 * pages (Privacy, Kilimanjaro Tours, etc.), built and styled to match the
 * /tours listing.
 *
 * The fixed site-information pages (About Us, Why Choose Us, Contact Us,
 * Terms and Conditions) are excluded from this index on purpose; they are
 * managed under Site Information and linked from the header/footer. Their
 * individual URLs still work.
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

    /**
     * Letters offered by the A–Z jump filter.
     *
     * A page whose title does not begin with a letter (or begins with a digit or
     * symbol) is reachable through the search box and the Clear action, but not
     * through the alphabet, since there is no letter to file it under.
     */
    public const A_Z_LETTERS = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    public function index(Request $request)
    {
        // Same guard rails as TourController::index(): a bad query string must
        // never loop, so strip the offending top-level parameters and redirect
        // instead of falling back to the default "back" redirect, which would
        // re-send the browser to the very same invalid URL.
        $validator = Validator::make($request->query(), [
            'page'   => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'letter' => ['nullable', 'string', 'size:1'],
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

        $search = trim((string) $request->query('search', ''));
        $letter = strtoupper(trim((string) $request->query('letter', '')));

        // Anything outside A–Z is not a letter we offer, so treat it as no
        // filter rather than building a LIKE clause that can never match.
        if (! in_array($letter, self::A_Z_LETTERS, true)) {
            $letter = '';
        }

        // Published-only, with no way to ask for drafts. A status filter here
        // would be a content leak: /pages is a public route, so ?status=draft
        // would render unpublished pages to anyone who typed it. Drafts are
        // managed in the admin, not browsed here.
        $query = Page::where('status', 'published')
                     ->with('heroImage');

        // The fixed site-information pages (About Us, Why Choose Us, Contact
        // Us, Terms and Conditions) are deliberately kept OUT of this listing.
        // They are chrome, not standalone content: they are managed on the
        // dedicated Site Information admin screen and are reached from the
        // header/footer, so /pages is reserved for the ordinary content pages.
        // The individual pages stay reachable by direct URL — only the index
        // omits them. Applied before the filters below so the A-Z counts are
        // scoped identically to the grid and cannot disagree with it.
        $query->whereNotIn('slug', Page::siteInfoSlugs());

        if ($letter !== '') {
            $query->where('title', 'like', $letter . '%');
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('slug', 'like', $term)
                  ->orWhere('content', 'like', $term);
            });
        }

        $query->orderBy('title');

        $pages = $query->paginate(self::PAGES_PER_PAGE)->withQueryString();

        // How many pages actually start with each letter, so the sidebar can
        // grey out letters that would return nothing instead of letting the
        // reader click into an empty result set. Counted on the same
        // search scope as the listing so the numbers cannot disagree with what
        // a click produces.
        $counts = (clone $query)
            ->reorder()
            ->selectRaw('UPPER(LEFT(title, 1)) AS letter, COUNT(*) AS total')
            ->groupByRaw('UPPER(LEFT(title, 1))')
            ->pluck('total', 'letter');

        $letterCounts = [];
        foreach (self::A_Z_LETTERS as $candidate) {
            $letterCounts[$candidate] = (int) ($counts[$candidate] ?? 0);
        }

        return view('frontend.pages.index', [
            'pages'        => $pages,
            'search'       => $search,
            'letter'       => $letter,
            'letterCounts' => $letterCounts,
            'alphabet'     => self::A_Z_LETTERS,
        ]);
    }
}
