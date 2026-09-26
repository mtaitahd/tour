@extends('frontend.layouts.app')

{{--
    Public /pages listing — the index of every published standalone page.

    Deliberately built from the EXACT same sfb- structure and class names as
    /tours (frontend/tours/index.blade.php) so both listings share one design
    system: .sfb-listing-page > .sfb-breadcrumb > .sfb-container.sfb-main-wrap >
    .sfb-layout > main.sfb-results, then .sfb-results-header, .sfb-selected-filters,
    .sfb-results-info, .sfb-tour-grid > article.sfb-tour-card (with the full
    __image-wrap / __body / __meta-grid / __operator / __footer / __cta anatomy),
    .sfb-empty-results, and the shared pagination partial.

    The only additions over /tours are the keyword search bar (pages have no
    filter sidebar) and the page-specific values inside the meta grid / footer.
    There is deliberately NO bespoke .sfb-page-card CSS — the cards are the
    tours cards, so the two listings cannot drift apart visually.

    Expects from the controller:
        - $pages   (LengthAwarePaginator of App\Models\Page)
        - $search  (current keyword, '' when empty)
--}}

@php
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $listingTitle = 'Help & Information';
    $introSource  = Setting::get('pages_listing_intro');
    $listingIntro = $introSource
        ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($introSource))), 540)
        : 'Find booking terms, refund and privacy policies, contact details and everything else you need to know before travelling with Afro-Vertex Tours & Safaris.';

    $operatorName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $operatorLogo = Setting::logoUrlOrDefault();

    // Pages with no hero image still get a real photo rather than a broken
    // <img>; Page::registerMediaCollections() supplies this same fallback.
    $fallbackImage = asset('assets/images/safari-hero.jpg');
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
            {{--
                .sfb-layout is kept (rather than dropping it) even though /pages has
                no filter sidebar: it is a flex row whose only child is
                .sfb-results (flex: 1 1 auto), so the results column spans the full
                container width while inheriting every /tours layout rule and
                breakpoint.
            --}}
            <div class="sfb-layout">
                <main class="sfb-results" id="sfb-results-start" aria-label="Site pages">
                    <header class="sfb-results-header">
                        <h1>{{ $listingTitle }}</h1>
                        <p>{{ $listingIntro }}</p>
                    </header>

                    <form method="GET" action="{{ route('pages.index') }}" class="sfb-page-search" role="search">
                        <label class="sfb-page-search__field">
                            <span class="visually-hidden">Search pages</span>
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Search pages, e.g. refund policy"
                                   autocomplete="off">
                        </label>
                        <button type="submit">Search</button>
                        @if($search !== '')
                            <a class="sfb-page-search__clear" href="{{ route('pages.index') }}">Clear</a>
                        @endif
                    </form>

                    @if($search !== '')
                        <div class="sfb-selected-filters" aria-label="Selected filters">
                            <span>Selected filters:</span>
                            <a class="sfb-selected-chip" href="{{ route('pages.index') }}">{{ $search }} <b>&times;</b></a>
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ route('pages.index') }}">Clear All Filters</a>
                        </div>
                    @endif

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
                                <h2>{{ $search !== '' ? 'No pages found matching "' . $search . '".' : 'No pages have been published yet.' }}</h2>
                                <p>{{ $search !== '' ? 'Try a different keyword, or browse the full list of pages.' : 'Please check back soon.' }}</p>
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
