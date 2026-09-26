@extends('frontend.layouts.app')

{{--
    Public /pages listing — the index of every published standalone page,
    built from the same sfb- design system as the /tours listing
    (breadcrumb, results header, results count, card grid, pagination).

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

    $fallbackImage = asset('assets/images/safari-hero.jpg');
@endphp

@section('body-class', 'is-pages-listing is-tours-listing')

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

                <div class="sfb-page-grid">
                    @forelse($pages as $pageItem)
                        @php
                            $heroImage = $pageItem->heroUrl('medium') ?: $pageItem->heroUrl() ?: $fallbackImage;
                            $excerptSource = $pageItem->meta_description ?: strip_tags((string) $pageItem->content);
                            $excerpt = $excerptSource
                                ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($excerptSource))), 190)
                                : 'Read the full details of our ' . Str::lower($pageItem->title) . '.';
                            $updatedLabel = $pageItem->updated_at?->format('j M Y');
                        @endphp
                        <article class="sfb-page-card">
                            <a class="sfb-page-card__full-link" href="{{ route('page.show', $pageItem->slug) }}" aria-label="Read {{ $pageItem->title }}"></a>
                            <div class="sfb-page-card__image-wrap">
                                <img src="{{ $heroImage }}" alt="{{ $pageItem->title }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}" onerror="this.onerror=null;this.src='{{ $fallbackImage }}';">
                                <div class="sfb-page-card__gradient" aria-hidden="true"></div>
                                <h2>{{ $pageItem->title }}</h2>
                            </div>
                            <div class="sfb-page-card__body">
                                <p class="sfb-page-card__excerpt">{{ $excerpt }}</p>

                                <div class="sfb-page-card__footer">
                                    <div class="sfb-page-card__meta">
                                        <span>Last updated</span>
                                        <strong>{{ $updatedLabel ?? 'Recently' }}</strong>
                                    </div>
                                    <div class="sfb-page-card__status">
                                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                                        <span>Information</span>
                                    </div>
                                </div>

                                <a href="{{ route('page.show', $pageItem->slug) }}" class="sfb-page-card__cta">Read More</a>
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
@endsection
