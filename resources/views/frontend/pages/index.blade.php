@extends('frontend.layouts.app')

{{--
    Public /pages listing.

    Layout is the /tours listing layout, element for element:
        .sfb-listing-page > .sfb-breadcrumb > .sfb-container.sfb-main-wrap >
        .sfb-layout > aside.sfb-sidebar + main.sfb-results
    and .sfb-results then holds .sfb-results-header, .sfb-selected-filters,
    .sfb-results-info, .sfb-tour-grid > article.sfb-tour-card (the full
    __image-wrap / __body / __meta-grid / __operator / __footer / __cta anatomy),
    .sfb-empty-results and the shared pagination partial.

    Because the grid and cards are literally the tours markup, the two listings
    cannot drift apart visually. There is deliberately no bespoke
    .sfb-page-card CSS.

    The sidebar offers only filters that are meaningful for text pages: a
    keyword search and an A-Z jump. It deliberately does NOT offer the tours
    facets (length, price, country, park, accommodation) because the pages table
    has no such columns — the tours filters are hard-coded to tour fields, and
    faking them for pages would mean inventing database columns for every page.
    Nor is there a status filter: /pages is public, so a ?status=draft style
    filter would render unpublished pages to anyone who typed it.

    Expects from the controller:
        - $pages        LengthAwarePaginator of App\Models\Page
        - $search       current keyword, '' when empty
        - $letter       current A-Z letter, '' when empty
        - $letterCounts letter => number of matching pages
        - $alphabet     the offered letters
--}}

@php
    use App\Models\Page;
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $listingTitle = 'Help & Information';
    $introSource  = Setting::get('pages_listing_intro');
    // Default copy deliberately does not advertise Terms or Contact details:
    // those are site-information pages and are no longer part of this listing.
    // They are still one click away in the header and footer.
    $listingIntro = $introSource
        ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($introSource))), 540)
        : 'Browse everything Afro-Vertex has published on travelling with us — trip information, policies and practical guidance. Our About, Contact and Terms pages are a click away in the menu.';

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

    $hasSelectedFilters = $search !== '' || $letter !== '';

    // Drop one filter while keeping the others, and always reset to page 1 —
    // staying on page 4 of a set that just shrank shows an empty grid.
    $withoutParam = function (string $key) {
        return route('pages.index', array_diff_key(request()->except(['page']), [$key => true]));
    };
@endphp

@section('body-class', 'is-pages-listing is-tours-listing')

@section('title', $listingTitle . ' | Afro-Vertex Tours & Safaris')

@section('extra-head')
    <meta name="description" content="{{ Str::limit($listingIntro, 160) }}">
@endsection

@section('page-content')
    <div class="sfb-listing-page">
        <nav class="sfb-breadcrumb" aria-label="Breadcrumb">
            <div class="sfb-container">
                <a href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>{{ $listingTitle }}</span>
            </div>
        </nav>

        <div class="sfb-container sfb-main-wrap">
            <button type="button" class="sfb-filter-toggle" data-sfb-open-filters aria-expanded="false" aria-controls="sfbFilterDrawer">
                <i class="isax isax-filter" aria-hidden="true"></i>
                Filter Pages
            </button>

            <div class="sfb-drawer-backdrop" data-sfb-close-filters hidden></div>

            <div class="sfb-layout">
                <aside class="sfb-sidebar" id="sfbFilterDrawer" aria-label="Page filters" data-sfb-filter-drawer>
                    <div class="sfb-sidebar__mobile-head">
                        <strong>Filter Pages</strong>
                        <button type="button" data-sfb-close-filters aria-label="Close filters">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('pages.index') }}" class="sfb-filter-form" data-sfb-filter-form>
                        <section class="sfb-safari-panel" aria-labelledby="pages-search-title">
                            <h2 id="pages-search-title">Search Pages</h2>

                            <div class="sfb-safari-control">
                                <i class="isax isax-search-1 sfb-safari-control__icon" aria-hidden="true"></i>
                                <div class="sfb-safari-field sfb-safari-field--button{{ $search !== '' ? ' has-value' : '' }}">
                                    <input type="search"
                                           name="search"
                                           class="sfb-safari-field__input"
                                           placeholder="Search pages, e.g. refund policy"
                                           value="{{ $search }}"
                                           autocomplete="off"
                                           aria-label="Search pages">
                                </div>
                            </div>

                            @if($letter !== '')
                                {{-- Keep the active letter when the search box is
                                     submitted, otherwise typing a new keyword would
                                     silently drop the A-Z filter. --}}
                                <input type="hidden" name="letter" value="{{ $letter }}">
                            @endif

                            <button type="submit" class="sfb-show-tours">
                                Show <b>{{ number_format($pages->total()) }}</b> {{ Str::plural('Page', $pages->total()) }}
                            </button>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-letter">
                            <h3 id="filter-letter">Browse A-Z</h3>
                            <div class="sfb-check-list sfb-az-list" role="group" aria-label="Browse pages alphabetically">
                                @foreach($alphabet as $candidate)
                                    @php $count = $letterCounts[$candidate] ?? 0; @endphp
                                    @if($count > 0)
                                        <a class="sfb-az-link{{ $letter === $candidate ? ' is-active' : '' }}"
                                           href="{{ $letter === $candidate ? $withoutParam('letter') : route('pages.index', array_merge(request()->except(['page']), ['letter' => $candidate])) }}"
                                           @if($letter === $candidate) aria-current="true" @endif>
                                            <span>{{ $candidate }}</span>
                                            <em>{{ $count }}</em>
                                        </a>
                                    @else
                                        {{-- No page starts with this letter: rendered
                                             disabled rather than hidden, so the
                                             alphabet keeps its full A-Z shape. --}}
                                        <span class="sfb-az-link is-empty" aria-disabled="true">
                                            <span>{{ $candidate }}</span>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </section>

                        <div class="sfb-filter-actions">
                            <button type="submit">Apply Filters</button>
                            <a href="{{ route('pages.index') }}">Clear All Filters</a>
                        </div>
                    </form>
                </aside>

                <main class="sfb-results" id="sfb-results-start" aria-label="Site pages">
                    <header class="sfb-results-header">
                        <h1>{{ $listingTitle }}</h1>
                        <p>{{ $listingIntro }}</p>
                    </header>

                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!$hasSelectedFilters)
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All pages</span>
                        @else
                            @if($letter !== '')
                                <a class="sfb-selected-chip" href="{{ $withoutParam('letter') }}">{{ $letter }} <b>&times;</b></a>
                            @endif
                            @if($search !== '')
                                <a class="sfb-selected-chip" href="{{ $withoutParam('search') }}">&ldquo;{{ $search }}&rdquo; <b>&times;</b></a>
                            @endif
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
                                $plainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) $pageItem->content)));
                                $wordCount = str_word_count($plainText);
                                $readMinutes = max(1, (int) ceil($wordCount / 200));
                                $updatedLabel = $pageItem->updated_at?->format('j M Y');
                            @endphp
                            <article class="sfb-tour-card">
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
                                <h2>{{ $search !== '' ? 'No pages found matching "' . $search . '".' : ($letter !== '' ? 'No pages starting with "' . $letter . '".' : 'No pages have been published yet.') }}</h2>
                                <p>{{ $hasSelectedFilters ? 'Try a different keyword or letter, or browse the full list of pages.' : 'Please check back soon.' }}</p>
                                <a href="{{ route('pages.index') }}">Clear All Filters</a>
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
