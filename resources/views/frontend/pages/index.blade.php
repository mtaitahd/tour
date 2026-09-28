@extends('frontend.layouts.app')

{{--
    Public /pages listing — "Travel Information".

    Layout is the /tours listing layout, element for element:
        .sfb-listing-page > .sfb-breadcrumb > .sfb-container.sfb-main-wrap >
        .sfb-layout > aside.sfb-sidebar + main.sfb-results
    and .sfb-results then holds .sfb-results-header, .sfb-selected-filters,
    .sfb-results-info, .sfb-tour-grid > article.sfb-tour-card (the full
    __image-wrap / __body / __meta-grid / __operator / __footer / __cta anatomy),
    .sfb-empty-results and the shared pagination partial.

    Because the grid and cards are literally the tours markup, the two listings
    cannot drift apart visually. There is deliberately no bespoke
    .sfb-page-card layout CSS — only spacing refinements.

    THE SIDEBAR is a filter panel, not a directory. It used to hold a keyword
    search plus a "Browse A-Z" alphabet jump; the A-Z block is gone entirely
    (no alphabet, no initials, nothing in its place) and is replaced by filter
    groups, each of which is a real predicate on a real column and is backed by
    a live facet count:

        Reading time   — from the content length (Page::estimateReadingMinutes)
        Last updated   — from updated_at
        Sort by        — order / title / updated_at

    A group is only rendered when at least one of its options would actually
    return something, so the panel can never offer a decorative checkbox. The
    tours facets (duration, price, country, park, accommodation) are absent
    because the pages table has no such columns, and there is no status filter
    because /pages is public and ?status=draft would render drafts to anyone.

    The panel is one white card with hairline dividers between groups rather
    than a stack of separate cards, and it is sticky on desktop so it stays
    with the reader. Under 992px the same markup becomes a slide-in drawer
    behind the "Filter Pages" button (.sfb-mobile-filter-toggle), which is the
    class /tours already uses, so the two drawers behave identically.

    Expects from the controller:
        - $pages          LengthAwarePaginator of App\Models\Page
        - $search         current keyword, '' when empty
        - $readFilters    selected reading-time buckets
        - $updatedFilters selected recency buckets
        - $sort           selected sort mode, '' when untouched
        - $readLabels     bucket => label
        - $updatedLabels  bucket => label
        - $readCounts     bucket => pages that would match
        - $updatedCounts  bucket => pages that would match
--}}

@php
    use App\Models\Page;
    use App\Models\Setting;
    use App\Services\ListingTitles;
    use Illuminate\Support\Str;

    // Heading and intro are editable at Admin → Listing Titles; the shipped
    // values below are what renders when no override has been saved.
    $listingTitle = ListingTitles::title('pages_listing_title', 'Travel Information');
    $listingIntro = ListingTitles::intro('pages_listing_intro')
        ?? 'Explore useful travel information, destination guides, safari insights, policies and practical resources to help you plan your journey with Afro-Vertex Tours & Safaris.';

    $operatorName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $operatorLogo = Setting::logoUrlOrDefault();

    // Pages with no hero image still get a real photo rather than a broken
    // <img>. Routed through the same candidate list as
    // Page::registerMediaCollections() so the card and the Media Library
    // agree on the fallback instead of each hardcoding their own path.
    $fallbackImage = Page::firstExistingPublicAsset(
        'assets/images/safari-hero.jpg',
        'front-end/html/assets/images/safari-hero.jpg',
    );

    $sortLabels = [
        'alpha'    => 'Title (A to Z)',
        'featured' => 'Featured order',
        'recent'   => 'Recently updated',
    ];

    // A radio group always has something checked, so an untouched sort is
    // sent as the first entry rather than as an absent parameter.
    $activeSort = $sort !== '' ? $sort : array_key_first($sortLabels);

    // Only offer an option that would return something, unless it is already
    // ticked — an unticked option always has a non-zero count, and a ticked one
    // has to stay visible or there would be no way to untick it.
    $offerOption = fn (array $counts, array $selected): array => array_filter(
        $counts,
        fn (int $count, string $key): bool => $count > 0 || in_array($key, $selected, true),
        ARRAY_FILTER_USE_BOTH,
    );

    $readOptions    = $offerOption($readCounts, $readFilters);
    $updatedOptions = $offerOption($updatedCounts, $updatedFilters);

    $hasSelectedFilters = $search !== ''
        || $readFilters !== []
        || $updatedFilters !== []
        || $sort !== '';

    // Build a listing URL with one filter (or one value of a filter) removed,
    // always resetting to page 1 — staying on page 4 of a set that just shrank
    // shows an empty grid. Same helper shape the /tours listing uses.
    $filterUrl = function (?string $key = null, ?string $value = null) {
        $query = request()->query();
        unset($query['page']);

        if ($key !== null) {
            if ($value !== null && isset($query[$key]) && is_array($query[$key])) {
                $query[$key] = array_values(array_filter(
                    $query[$key],
                    fn ($item) => (string) $item !== $value
                ));

                if ($query[$key] === []) {
                    unset($query[$key]);
                }
            } else {
                unset($query[$key]);
            }
        }

        return route('pages.index', $query);
    };

    // The removable chips in the "Selected filters" row, built from exactly the
    // filters that are live, so a chip can never appear for something the query
    // is not actually filtering on.
    $activeChips = [];
    if ($search !== '') {
        $activeChips[] = ['label' => '"' . $search . '"', 'url' => $filterUrl('search')];
    }
    foreach ($readFilters as $value) {
        $activeChips[] = ['label' => $readLabels[$value], 'url' => $filterUrl('read', $value)];
    }
    foreach ($updatedFilters as $value) {
        $activeChips[] = ['label' => $updatedLabels[$value], 'url' => $filterUrl('updated', $value)];
    }
    if ($sort !== '') {
        $activeChips[] = ['label' => 'Sorted by ' . $sortLabels[$activeSort], 'url' => $filterUrl('sort')];
    }
@endphp

@section('body-class', 'is-pages-listing is-tours-listing')

{{-- No @section('title') / description here on purpose. The frontend layout
     takes its <title> and meta description from the $meta array built by the
     view composer in AppServiceProvider, and it does not yield a "title"
     section at all — emitting one would silently do nothing, and emitting a
     second <meta name="description"> would duplicate the one the layout prints
     using $meta['description']. The composer reads pages_listing_title from the
     same ListingTitles service as the H1 below, so the two stay in step. --}}

@section('page-content')
    <div class="sfb-listing-page is-pages-listing">
        <nav class="sfb-breadcrumb" aria-label="Breadcrumb">
            <div class="sfb-container">
                <a href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>{{ $listingTitle }}</span>
            </div>
        </nav>

        <div class="sfb-container sfb-main-wrap">
            {{-- Same class the /tours listing uses, so this button inherits
                 styling that already exists rather than needing its own. --}}
            <button type="button" class="sfb-mobile-filter-toggle" data-sfb-open-filters aria-expanded="false" aria-controls="sfbFilterDrawer">
                <i class="isax isax-filter" aria-hidden="true"></i>
                Filter Pages
            </button>

            <div class="sfb-drawer-backdrop" data-sfb-close-filters hidden></div>

            <div class="sfb-layout">
                <aside class="sfb-sidebar sfb-sidebar--pages" id="sfbFilterDrawer" aria-label="Page filters" data-sfb-filter-drawer>
                    <div class="sfb-sidebar__mobile-head">
                        <strong>Filter Pages</strong>
                        <button type="button" data-sfb-close-filters aria-label="Close filters">&times;</button>
                    </div>

                    {{-- A plain GET form: unchecked boxes simply do not submit,
                         so removing a filter needs no JavaScript. The
                         data-sfb-auto fields re-submit on change for the instant
                         feel, and the buttons below still work without it. --}}
                    <form method="GET" action="{{ route('pages.index') }}" class="sfb-pages-filters" data-sfb-filter-form>
                        <div class="sfb-pages-filters__panel">

                            <section class="sfb-pages-filters__section sfb-pages-filters__section--search" aria-labelledby="pages-search-title">
                                <h2 class="sfb-pages-filters__title" id="pages-search-title">Search Pages</h2>

                                <div class="sfb-pages-filters__search">
                                    <i class="isax isax-search-1" aria-hidden="true"></i>
                                    <input type="search"
                                           name="search"
                                           class="sfb-pages-filters__search-input"
                                           placeholder="Search pages, e.g. refund policy"
                                           value="{{ $search }}"
                                           autocomplete="off"
                                           aria-label="Search pages">
                                </div>

                                <button type="submit" class="sfb-pages-filters__submit">Search</button>

                                <p class="sfb-pages-filters__hint" aria-live="polite">
                                    <strong>{{ number_format($pages->total()) }}</strong>
                                    {{ Str::plural('page', $pages->total()) }} {{ $hasSelectedFilters ? 'match your filters' : 'available' }}
                                </p>
                            </section>

                            @if($readOptions)
                                <section class="sfb-pages-filters__section" aria-labelledby="filter-read">
                                    <h3 class="sfb-pages-filters__heading" id="filter-read">Reading time</h3>
                                    <ul class="sfb-pages-filters__options">
                                        @foreach($readOptions as $value => $count)
                                            <li>
                                                <label>
                                                    <input type="checkbox" name="read[]" value="{{ $value }}"
                                                           @checked(in_array($value, $readFilters, true)) data-sfb-auto>
                                                    <span class="sfb-pages-filters__mark" aria-hidden="true"></span>
                                                    <span class="sfb-pages-filters__label">{{ $readLabels[$value] }}</span>
                                                    <em>{{ $count }}</em>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif

                            @if($updatedOptions)
                                <section class="sfb-pages-filters__section" aria-labelledby="filter-updated">
                                    <h3 class="sfb-pages-filters__heading" id="filter-updated">Last updated</h3>
                                    <ul class="sfb-pages-filters__options">
                                        @foreach($updatedOptions as $value => $count)
                                            <li>
                                                <label>
                                                    <input type="checkbox" name="updated[]" value="{{ $value }}"
                                                           @checked(in_array($value, $updatedFilters, true)) data-sfb-auto>
                                                    <span class="sfb-pages-filters__mark" aria-hidden="true"></span>
                                                    <span class="sfb-pages-filters__label">{{ $updatedLabels[$value] }}</span>
                                                    <em>{{ $count }}</em>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif

                            <section class="sfb-pages-filters__section" aria-labelledby="filter-sort">
                                <h3 class="sfb-pages-filters__heading" id="filter-sort">Sort results by</h3>
                                <ul class="sfb-pages-filters__options sfb-pages-filters__options--radio">
                                    @foreach($sortLabels as $value => $label)
                                        <li>
                                            <label>
                                                <input type="radio" name="sort" value="{{ $value }}"
                                                       @checked($activeSort === $value) data-sfb-auto>
                                                <span class="sfb-pages-filters__mark" aria-hidden="true"></span>
                                                <span class="sfb-pages-filters__label">{{ $label }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>

                            <div class="sfb-pages-filters__actions">
                                <button type="submit">Apply Filters</button>
                                <a href="{{ route('pages.index') }}">Clear all filters</a>
                            </div>
                        </div>
                    </form>
                </aside>

                <main class="sfb-results" id="sfb-results-start" aria-label="{{ $listingTitle }}">
                    <header class="sfb-results-header">
                        <h1>{{ $listingTitle }}</h1>
                        <p>{{ $listingIntro }}</p>
                    </header>

                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!$hasSelectedFilters)
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All pages</span>
                        @else
                            @foreach($activeChips as $chip)
                                <a class="sfb-selected-chip" href="{{ $chip['url'] }}">{{ $chip['label'] }} <b>&times;</b></a>
                            @endforeach
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ route('pages.index') }}">Clear all</a>
                        @endif
                    </div>

                    <div class="sfb-results-info">
                        <strong>{{ $pages->firstItem() ?: 0 }}&ndash;{{ $pages->lastItem() ?: 0 }} of {{ number_format($pages->total()) }}</strong>
                        <span>{{ Str::plural('page', $pages->total()) }}</span>
                    </div>

                    <div class="sfb-tour-grid">
                        @forelse($pages as $pageItem)
                            @php
                                $heroImage = $pageItem->hasHeroImage()
                                    ? ($pageItem->heroUrl('medium') ?: $pageItem->heroUrl() ?: $fallbackImage)
                                    : $fallbackImage;
                                // Same estimate the sidebar's "Reading time"
                                // facet filters on, so a card can never end up in
                                // a bucket its own badge contradicts.
                                $readMinutes = $pageItem->readingMinutes();
                                $plainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) $pageItem->content)));
                                $wordCount = str_word_count($plainText);
                                $updatedLabel = $pageItem->updated_at?->format('j M Y');
                            @endphp
                            <article class="sfb-tour-card sfb-page-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('page.show', $pageItem->slug) }}" aria-label="Read {{ $pageItem->title }}"></a>
                                <div class="sfb-tour-card__image-wrap">
                                    <img src="{{ $heroImage }}" alt="{{ $pageItem->title }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}">
                                    <div class="sfb-tour-card__gradient" aria-hidden="true"></div>
                                    <h2>{{ $pageItem->title }}</h2>
                                </div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid">
                                        <div>
                                            <span>Type</span>
                                            <strong>Information</strong>
                                        </div>
                                        <div>
                                            <span>Reading time</span>
                                            <strong>{{ $readMinutes }} {{ Str::plural('min', $readMinutes) }}</strong>
                                        </div>
                                        <div>
                                            <span>Length</span>
                                            <strong>{{ number_format($wordCount) }} {{ Str::plural('word', $wordCount) }}</strong>
                                        </div>
                                        <div>
                                            <span>Last updated</span>
                                            <strong>{{ $updatedLabel ?: 'Recently' }}</strong>
                                        </div>
                                    </div>

                                    <div class="sfb-tour-card__operator">
                                        <img src="{{ $operatorLogo }}" alt="" loading="lazy">
                                        <div>
                                            <span>Operated by</span>
                                            <strong>{{ $operatorName }}</strong>
                                        </div>
                                    </div>

                                    <div class="sfb-tour-card__footer">
                                        {{-- Reuses the .sfb-tour-card__rating slot purely for its
                                             typography/alignment. No stars: a Terms or Privacy
                                             page has no rating, and showing five would be a lie. --}}
                                        <div class="sfb-tour-card__rating">
                                            <strong>Read online</strong>
                                            <span>No booking required</span>
                                        </div>
                                        <div class="sfb-tour-card__price">
                                            <span>Access</span>
                                            <strong>Free</strong>
                                            <em>any time</em>
                                        </div>
                                    </div>

                                    <a href="{{ route('page.show', $pageItem->slug) }}" class="sfb-tour-card__cta">Read More</a>
                                </div>
                            </article>
                        @empty
                            <div class="sfb-empty-results">
                                <h2>
                                    @if($search !== '')
                                        No pages found matching &ldquo;{{ $search }}&rdquo;.
                                    @elseif($readFilters !== [] || $updatedFilters !== [])
                                        No pages match the filters you picked.
                                    @else
                                        No pages have been published yet.
                                    @endif
                                </h2>
                                <p>{{ $hasSelectedFilters ? 'Try a different keyword, loosen a filter, or browse the full list of pages.' : 'Please check back soon.' }}</p>
                                @if($hasSelectedFilters)
                                    <a href="{{ route('pages.index') }}">Clear All Filters</a>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    @include('frontend.tours.partials.pagination', ['paginator' => $pages])
                </main>
            </div>
        </div>
    </div>
@endsection

@section('extra-scripts')
<script>
    (function () {
        'use strict';

        // Same drawer/auto-submit behaviour as the /tours filter sidebar, so the
        // mobile "Filter Pages" button and the instant-apply fields behave
        // identically on both listings. Kept local rather than extracted to a
        // partial because the tours copy is heavily specialised (ranges,
        // calendar, travellers popovers) and only the generic parts apply here.
        var form = document.querySelector('[data-sfb-filter-form]');
        var drawer = document.querySelector('[data-sfb-filter-drawer]');
        var backdrop = document.querySelector('[data-sfb-close-filters].sfb-drawer-backdrop');
        var openButton = document.querySelector('[data-sfb-open-filters]');
        var submitTimer = null;

        function submitSoon() {
            if (!form) return;
            window.clearTimeout(submitTimer);
            submitTimer = window.setTimeout(function () {
                if (form.requestSubmit) form.requestSubmit();
                else form.submit();
            }, 250);
        }

        if (form) {
            form.querySelectorAll('[data-sfb-auto]').forEach(function (field) {
                field.addEventListener('change', submitSoon);
            });
        }

        function openDrawer() {
            if (!drawer || !backdrop || !openButton) return;
            drawer.classList.add('is-open');
            backdrop.hidden = false;
            openButton.setAttribute('aria-expanded', 'true');
            document.body.classList.add('sfb-filter-lock');
        }

        function closeDrawer() {
            if (!drawer || !backdrop || !openButton) return;
            drawer.classList.remove('is-open');
            backdrop.hidden = true;
            openButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('sfb-filter-lock');
        }

        if (openButton) openButton.addEventListener('click', openDrawer);
        document.querySelectorAll('[data-sfb-close-filters]').forEach(function (button) {
            button.addEventListener('click', closeDrawer);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeDrawer();
        });
    })();
</script>
@endsection
