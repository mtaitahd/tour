<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Public "Pages" listing (/pages) — the index of published standalone content
 * pages (Travel Information, Privacy, Kilimanjaro Tours, etc.), built and styled
 * to match the /tours listing.
 *
 * The fixed site-information pages (About Us, Why Choose Us, Contact Us,
 * Terms and Conditions) are excluded from this index on purpose; they are
 * managed under Site Information and linked from the header/footer. Their
 * individual URLs still work.
 *
 * SIDEBAR FILTERS — every one of these is a real predicate on a real column.
 * There is no page taxonomy in this schema (no type, no category, no tags, no
 * destination), so the facets are derived from the data the pages table
 * actually has, and each is backed by a facet count so the sidebar only ever
 * offers an option that would return something:
 *
 *   search   — keyword across title, slug and content body (pre-existing).
 *   read[]   — reading time, from the content length (see Page::estimateReadingMinutes).
 *   updated[]— recency of updated_at, which is the one thing travellers genuinely
 *              need to know about travel information: whether it is current.
 *   sort     — order / title / updated_at, three real columns.
 *
 * Deliberately absent: a status filter. /pages is public, so ?status=draft would
 * render unpublished pages to anyone who typed it — drafts belong in the admin.
 * The tours facets (duration, price, country, park, accommodation) are equally
 * absent because the pages table has none of those columns.
 *
 * There is also no A-Z jump filter. It was removed deliberately; the one
 * remaining trace is the redirect below that strips ?letter= from any bookmarked
 * URL so old links land on the unfiltered listing instead of a dead parameter.
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

    /** Sort modes the sidebar offers, mapped to the columns they order by. */
    public const SORTS = ['alpha', 'featured', 'recent'];

    /**
     * "Last updated" buckets. They are deliberately non-overlapping so that
     * ticking two boxes narrows the result set instead of silently ignoring
     * one of them.
     *
     * @var array<string, string>
     */
    public const UPDATED_BUCKETS = [
        'recent' => 'Updated in the last 3 months',
        'year'   => 'Updated earlier this year',
        'older'  => 'Updated a year ago or earlier',
    ];

    /** How far back "recent" reaches. */
    public const UPDATED_RECENT_MONTHS = 3;

    public function index(Request $request)
    {
        // The A-Z jump filter is gone. A bookmarked or indexed ?letter= URL is
        // still honoured as a link — it just drops the parameter — rather than
        // 404ing or leaving a dead filter in the query string.
        if (array_key_exists('letter', $request->query())) {
            return redirect()->to($request->fullUrlWithoutQuery('letter'), 302);
        }

        // Same guard rails as TourController::index(): a bad query string must
        // never loop, so strip the offending top-level parameters and redirect
        // instead of falling back to the default "back" redirect, which would
        // re-send the browser to the very same invalid URL.
        //
        // The two checkbox groups are normalised to arrays first: unchecked
        // groups legitimately arrive as an empty array, and a hand-typed
        // ?read=short should be read as one selection rather than rejected as a
        // malformed type. Note Request::query() returns a plain array when called
        // without a key, so this is array syntax throughout.
        $normalised = $request->query();
        foreach (['read', 'updated'] as $key) {
            if (array_key_exists($key, $normalised)) {
                $normalised[$key] = Arr::wrap($normalised[$key]);
            }
        }

        $validator = Validator::make($normalised, [
            'page'    => ['nullable', 'integer', 'min:1'],
            'search'  => ['nullable', 'string', 'max:255'],
            'read'    => ['nullable', 'array'],
            'read.*'  => ['string', 'in:short,medium,long'],
            'updated' => ['nullable', 'array'],
            'updated.*' => ['string', 'in:recent,year,older'],
            'sort'    => ['nullable', 'string', 'in:' . implode(',', self::SORTS)],
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

        $search  = trim((string) $request->query('search', ''));
        $sort    = (string) $request->query('sort', '');
        $read    = self::cleanSelection($request->query('read'), array_keys(Page::readingBucketLabels()));
        $updated = self::cleanSelection($request->query('updated'), array_keys(self::UPDATED_BUCKETS));

        // An unrecognised sort is treated as "no sort chosen" rather than as a
        // broken LIKE clause, so the listing always renders in a known order.
        if (! in_array($sort, self::SORTS, true)) {
            $sort = '';
        }

        $pages = $this->applySort($this->baseQuery($search, $read, $updated), $sort)
            ->paginate(self::PAGES_PER_PAGE)
            ->withQueryString();

        // Facet counts. Each group is counted on the scope that includes every
        // OTHER filter but not its own, which is what makes the numbers the
        // reader would actually get by ticking that box — counting on the fully
        // filtered set instead would show 0 for every unselected option and
        // quietly hide half the sidebar.
        $readCounts = $this->readingCounts($search, $updated);
        $updatedCounts = $this->updatedCounts($search, $read);

        return view('frontend.pages.index', [
            'pages'          => $pages,
            'search'         => $search,
            'readFilters'    => $read,
            'updatedFilters' => $updated,
            'sort'           => $sort,
            'readLabels'     => Page::readingBucketLabels(),
            'updatedLabels'  => self::UPDATED_BUCKETS,
            'readCounts'     => $readCounts,
            'updatedCounts'  => $updatedCounts,
        ]);
    }

    /**
     * The shared listing scope: published pages, minus the site-information
     * chrome, narrowed by the keyword and whichever facet groups are supplied.
     *
     * Passing an empty array for a group is how the facet counts for that same
     * group exclude its own selection, so this method is the only place the
     * where-clauses are written.
     *
     * @param  array<int, string> $read
     * @param  array<int, string> $updated
     */
    private function baseQuery(string $search, array $read, array $updated): Builder
    {
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
        // omits them. Applied before the filters below so the facet counts are
        // scoped identically to the grid and cannot disagree with it.
        $query->whereNotIn('slug', Page::siteInfoSlugs());

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('slug', 'like', $term)
                  ->orWhere('content', 'like', $term);
            });
        }

        // Tick two boxes in one group and the result widens (OR) — the standard
        // marketplace behaviour. Separate groups are ANDed by virtue of being
        // separate where() calls.
        if ($read !== []) {
            $query->where(function ($q) use ($read) {
                foreach ($read as $bucket) {
                    [$min, $max] = Page::readingBucketSql($bucket);
                    $length = Page::readingLengthSql();

                    $q->orWhere(function ($bucketQuery) use ($length, $min, $max) {
                        $bucketQuery->whereRaw("{$length} >= ?", [$min]);

                        if ($max !== null) {
                            $bucketQuery->whereRaw("{$length} <= ?", [$max]);
                        }
                    });
                }
            });
        }

        if ($updated !== []) {
            [$recentFrom, $yearFrom] = $this->updatedBoundaries();

            $query->where(function ($q) use ($updated, $recentFrom, $yearFrom) {
                foreach ($updated as $bucket) {
                    if ($bucket === 'recent') {
                        $q->orWhere('updated_at', '>=', $recentFrom);
                    } elseif ($bucket === 'year') {
                        // "Earlier this year" runs from 1 January up to the start
                        // of the recent window, so the two buckets partition the
                        // year instead of overlapping. Overlapping checkboxes
                        // would silently ignore the second one ticked.
                        $q->orWhere(function ($year) use ($recentFrom, $yearFrom) {
                            $year->where('updated_at', '>=', $yearFrom)
                                 ->where('updated_at', '<', $recentFrom);
                        });
                    } else {
                        $q->orWhere('updated_at', '<', $yearFrom);
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Cut-offs for the recency buckets. Recomputed per request rather than
     * cached, so "updated in the last 3 months" cannot go stale.
     *
     * @return array{0: Carbon, 1: Carbon} [recent window start, 1 January]
     */
    private function updatedBoundaries(): array
    {
        return [
            Carbon::now()->subMonths(self::UPDATED_RECENT_MONTHS)->startOfDay(),
            Carbon::now()->startOfYear(),
        ];
    }

    /**
     * @param  array<int, string> $read
     * @return array<string, int> bucket => matching page count
     */
    private function readingCounts(string $search, array $updated): array
    {
        $length = Page::readingLengthSql();
        $shortMax = Page::READING_SHORT_MAX_MINUTES * Page::READING_CHARS_PER_MINUTE;
        $mediumMax = Page::READING_MEDIUM_MAX_MINUTES * Page::READING_CHARS_PER_MINUTE;

        // Same CASE as Page::readingBucketFor(), expressed in SQL. Kept adjacent
        // to that method's constants so the badge and the bucket cannot drift.
        $bucket = $this->caseExpression([
            "WHEN {$length} <= {$shortMax} THEN 'short'",
            "WHEN {$length} <= {$mediumMax} THEN 'medium'",
        ], 'long');

        $counts = $this->baseQuery($search, [], $updated)
            ->reorder()
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS total")
            ->groupByRaw($bucket)
            ->pluck('total', 'bucket');

        return $this->fillCounts(array_keys(Page::readingBucketLabels()), $counts);
    }

    /**
     * @param  array<int, string> $read
     * @return array<string, int> bucket => matching page count
     */
    private function updatedCounts(string $search, array $read): array
    {
        [$recentFrom, $yearFrom] = $this->updatedBoundaries();

        $bucket = $this->caseExpression([
            "WHEN updated_at >= '{$this->sqlDate($recentFrom)}' THEN 'recent'",
            "WHEN updated_at >= '{$this->sqlDate($yearFrom)}' THEN 'year'",
        ], 'older');

        $counts = $this->baseQuery($search, $read, [])
            ->reorder()
            ->selectRaw("{$bucket} AS bucket, COUNT(*) AS total")
            ->groupByRaw($bucket)
            ->pluck('total', 'bucket');

        return $this->fillCounts(array_keys(self::UPDATED_BUCKETS), $counts);
    }

    /**
     * Assemble a `CASE <when...> ELSE '<elseValue>' END` fragment.
     *
     * The boundaries are inlined rather than bound as placeholders because the
     * same expression is repeated in GROUP BY, and a placeholder cannot be fed
     * two sets of bindings. They come from Carbon, not from the request, so
     * they can only ever be digits, dashes, colons and spaces.
     *
     * The ELSE arm is what makes the counts add up to the result count, so it
     * is written out by this method rather than passed in pre-formatted. An
     * earlier version took an already-quoted literal and appended it verbatim,
     * which produced `... THEN 'medium' 'long' END`: MySQL accepts a searched
     * CASE with no ELSE, silently dropped the stray literal, and every row that
     * matched no WHEN came back with a NULL bucket. Those rows then vanished
     * from the facet counts entirely instead of landing in the last bucket.
     *
     * @param  array<int, string> $whenClauses  clause text without the WHEN keyword
     * @param  string             $elseValue    bucket name, from this controller's
     *                                           own catalogue and never the request
     */
    private function caseExpression(array $whenClauses, string $elseValue): string
    {
        $sql = 'CASE';

        foreach ($whenClauses as $clause) {
            $sql .= ' ' . $clause;
        }

        return $sql . " ELSE '" . $elseValue . "' END";
    }

    /**
     * Format a Carbon boundary the way the connection writes DATETIME values,
     * so the inline comparison in caseExpression() matches the column type.
     */
    private function sqlDate(Carbon $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Zero-fill a count map so every offered key has an entry. A missing key
     * means "no pages matched", which the view renders as an absent option
     * rather than as a checkbox that leads to an empty page.
     *
     * @param  array<int, string>    $keys
     * @param  \Illuminate\Support\Collection<string, mixed> $counts
     * @return array<string, int>
     */
    private function fillCounts(array $keys, $counts): array
    {
        $filled = [];

        foreach ($keys as $key) {
            $filled[$key] = (int) ($counts[$key] ?? 0);
        }

        return $filled;
    }

    /**
     * @param  array<int, string> $read
     * @param  array<int, string> $updated
     */
    private function applySort(Builder $query, string $sort): Builder
    {
        // Title is the secondary key everywhere, so the order is total and two
        // pages never swap places between requests.
        return match ($sort) {
            'featured' => $query->orderBy('order')->orderBy('title'),
            'recent'   => $query->orderByDesc('updated_at')->orderBy('title'),
            // 'alpha' and "no preference" both fall back to today's behaviour.
            default    => $query->orderBy('title'),
        };
    }

    /**
     * Keep only recognised, de-duplicated selections and put them in the
     * catalogue's own order so the URL and the selected-filter chips read the
     * same way every time.
     *
     * @param  array<int, string> $allowed
     * @return array<int, string>
     */
    private static function cleanSelection(mixed $value, array $allowed): array
    {
        $selected = collect(Arr::wrap($value))
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter(fn ($item) => in_array($item, $allowed, true))
            ->unique()
            ->all();

        // Catalogue order, not request order.
        return array_values(array_filter($allowed, fn (string $key) => in_array($key, $selected, true)));
    }
}
