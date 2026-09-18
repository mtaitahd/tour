@extends('frontend.layouts.app')

@php
    use Illuminate\Support\Str;

    $countryNames = [
        'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
        'ZA' => 'South Africa', 'BW' => 'Botswana', 'ZW' => 'Zimbabwe', 'ZM' => 'Zambia',
        'CD' => 'DR Congo', 'BI' => 'Burundi', 'MZ' => 'Mozambique', 'SS' => 'South Sudan',
        'ET' => 'Ethiopia',
    ];

    $flagFor = function (?string $code): string {
        if (! $code || strlen($code) !== 2) { return ''; }
        $code = strtoupper($code);
        return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
    };

    $selectedCountry = request('country');
    $selectedType    = request('type');
    $selectedFeatured = request()->filled('featured');

    $countryName = $countryNames[$selectedCountry] ?? $selectedCountry ?? '';
    $typeLabel   = fn ($type) => $typeNames[$type] ?? Str::title(str_replace('_', ' ', (string) $type));

    if ($selectedCountry && isset($countryNames[$selectedCountry])) {
        $mainTitle = $countryNames[$selectedCountry] . ' Destinations';
    } elseif ($selectedType) {
        $mainTitle = $typeLabel($selectedType) . ' Destinations';
    } else {
        $mainTitle = 'Destinations in East Africa';
    }

    $introText = 'Explore the most stunning destinations in Tanzania, Kenya, Uganda and Rwanda — from Serengeti safaris and Ngorongoro craters to Kilimanjaro climbs, Zanzibar beaches and gorilla trekking in Bwindi.';

    $hasSelectedFilters = request()->filled('country')
        || request()->filled('type')
        || request()->filled('featured');
@endphp

@section('body-class', 'is-tours-listing')

@section('title', $mainTitle . ' | Afro-Vertex Tours & Safaris')

@section('extra-head')
    <meta name="description" content="{{ Str::limit($introText, 160) }}">
    <meta name="keywords" content="east africa destinations, serengeti, ngorongoro, zanzibar, kilimanjaro, bwindi, uganda gorillas, kenya safaris, rwanda tourism">
@endsection

@section('page-content')
    <div class="sfb-listing-page">
        <nav class="sfb-breadcrumb" aria-label="Breadcrumb">
            <div class="sfb-container">
                <a href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">&rsaquo;</span>
                <a href="{{ route('destinations.index') }}">Destinations</a>
                @if($countryName)
                    <span aria-hidden="true">&rsaquo;</span>
                    <span>{{ $countryName }}</span>
                @elseif($selectedType)
                    <span aria-hidden="true">&rsaquo;</span>
                    <span>{{ $typeLabel($selectedType) }}</span>
                @endif
            </div>
        </nav>

        <div class="sfb-container sfb-main-wrap">
            <button type="button" class="sfb-mobile-filter-toggle" data-sfb-open-filters aria-controls="sfbFilterDrawer" aria-expanded="false">
                <i class="isax isax-filter" aria-hidden="true"></i>
                Filter Destinations
            </button>

            <div class="sfb-drawer-backdrop" data-sfb-close-filters hidden></div>

            <div class="sfb-layout">
                <aside class="sfb-sidebar" id="sfbFilterDrawer" aria-label="Destination filters" data-sfb-filter-drawer>
                    <div class="sfb-sidebar__mobile-head">
                        <strong>Filter Destinations</strong>
                        <button type="button" data-sfb-close-filters aria-label="Close filters">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('destinations.index') }}" class="sfb-filter-form" data-sfb-filter-form>
                        <section class="sfb-filter-section" aria-labelledby="filter-dest-country">
                            <h3 id="filter-dest-country">Country</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                @foreach($countryCounts as $code => $count)
                                    @php $label = $countryNames[$code] ?? $code; @endphp
                                    <label>
                                        <input type="checkbox" name="country" value="{{ $code }}" {{ $selectedCountry === $code ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $label }}</span>
                                        <em>{{ $count }}</em>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-dest-type">
                            <h3 id="filter-dest-type">Type</h3>
                            <div class="sfb-check-list">
                                @foreach($typeCounts as $type => $count)
                                    <label>
                                        <input type="checkbox" name="type" value="{{ $type }}" {{ $selectedType === $type ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $typeLabel($type) }}</span>
                                        <em>{{ $count }}</em>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-dest-featured">
                            <h3 id="filter-dest-featured">Features</h3>
                            <div class="sfb-check-list">
                                <label>
                                    <input type="checkbox" name="featured" value="1" {{ $selectedFeatured ? 'checked' : '' }} data-sfb-auto>
                                    <span>Featured destinations only</span>
                                </label>
                            </div>
                        </section>

                        <div class="sfb-filter-actions">
                            <button type="submit">{{ number_format($destinationsTotal) }} Destinations</button>
                            <a href="{{ route('destinations.index') }}">Clear All Filters</a>
                        </div>
                    </form>
                </aside>

                <main class="sfb-results" id="sfb-results-start" aria-label="Destination results">
                    <header class="sfb-results-header">
                        <h1>{{ $mainTitle }}</h1>
                        <p>{{ $introText }}</p>
                    </header>

                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!$hasSelectedFilters)
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All destinations</span>
                        @else
                            @if($selectedCountry)
                                <a class="sfb-selected-chip sfb-selected-chip--blue" href="{{ route('destinations.index', request()->except(['country', 'page'])) }}">{{ $countryNames[$selectedCountry] ?? $selectedCountry }} <b>&times;</b></a>
                            @endif
                            @if($selectedType)
                                <a class="sfb-selected-chip" href="{{ route('destinations.index', request()->except(['type', 'page'])) }}">{{ $typeLabel($selectedType) }} <b>&times;</b></a>
                            @endif
                            @if($selectedFeatured)
                                <a class="sfb-selected-chip" href="{{ route('destinations.index', request()->except(['featured', 'page'])) }}">Featured only <b>&times;</b></a>
                            @endif
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ route('destinations.index') }}">Clear All Filters</a>
                        @endif
                    </div>

                    <div class="sfb-results-info">
                        <strong>{{ $destinations->firstItem() ?: 0 }}&ndash;{{ $destinations->lastItem() ?: 0 }} of {{ number_format($destinations->total()) }}</strong>
                    </div>

                    <div class="sfb-tour-grid">
                        @forelse($destinations as $SingleDestination)
                            @php
                                $destName = $SingleDestination->name;
                                $destCode = strtoupper((string) $SingleDestination->country_code);
                                $destFlag = $flagFor($destCode);
                                $destCountry = $countryNames[$destCode] ?? $destCode;
                                $destType = $SingleDestination->type ? $typeLabel($SingleDestination->type) : '';
                                $toursCount = $SingleDestination->tours()->where('status', 'published')->count();
                                $destImage = $SingleDestination->hasHeroImage()
                                    ? ($SingleDestination->heroUrl('medium') ?: $SingleDestination->heroUrl())
                                    : asset('front-end/html/assets/img/placeholder-destination.jpg');
                            @endphp
                            <article class="sfb-tour-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('destination.show', $SingleDestination->slug) }}" aria-label="View {{ $destName }}"></a>
                                <div class="sfb-tour-card__image-wrap">
                                    <img src="{{ $destImage }}" alt="{{ $destName }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}">
                                    <div class="sfb-tour-card__gradient" aria-hidden="true"></div>
                                    @if($SingleDestination->is_featured)
                                        <span class="sfb-dest-badge">Featured</span>
                                    @endif
                                    <h2>{{ $destName }}</h2>
                                </div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid">
                                        <div>
                                            <span>Country</span>
                                            <strong>{{ $destFlag ? $destFlag . ' ' : '' }}{{ $destCountry ?: 'East Africa' }}</strong>
                                        </div>
                                        <div>
                                            <span>Type</span>
                                            <strong>{{ $destType ?: 'Destination' }}</strong>
                                        </div>
                                        <div>
                                            <span>Available</span>
                                            <strong>{{ $toursCount }} {{ Str::plural('Tour', $toursCount) }}</strong>
                                        </div>
                                    </div>

                                    @if($SingleDestination->description)
                                        <p class="text-muted sfb-dest-description">
                                            {{ Str::limit(strip_tags($SingleDestination->description), 130) }}
                                        </p>
                                    @endif

                                    <a href="{{ route('destination.show', $SingleDestination->slug) }}" class="sfb-tour-card__cta">Explore Destination</a>
                                </div>
                            </article>
                        @empty
                            <div class="sfb-empty-results">
                                <h2>No destinations found matching your filters.</h2>
                                <p>Try clearing one or more filters to see more destinations.</p>
                                <a href="{{ route('destinations.index') }}">Clear All Filters</a>
                            </div>
                        @endforelse
                    </div>

                    @include('frontend.tours.partials.pagination', ['paginator' => $destinations])
                </main>
            </div>
        </div>
    </div>

    <style>
        .sfb-dest-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 2;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #fff;
            background: #0876a6;
            border-radius: 3px;
        }
        .sfb-dest-description {
            margin: 0 0 14px;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
@endsection

@section('extra-scripts')
    <script>
        (function () {
            'use strict';

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

            /* Pagination: loading state + smooth scroll back to results */
            var resultsArea = document.getElementById('sfb-results-start');
            var SCROLL_OFFSET = 90;

            function scrollToResults(behavior) {
                if (!resultsArea) return;
                var top = resultsArea.getBoundingClientRect().top + window.scrollY - SCROLL_OFFSET;
                window.scrollTo({ top: Math.max(top, 0), behavior: behavior || 'auto' });
            }

            function markLoading() {
                if (resultsArea) resultsArea.classList.add('is-loading');
                document.body.classList.add('sfb-page-transition');
            }

            function clearLoading() {
                if (resultsArea) resultsArea.classList.remove('is-loading');
                document.body.classList.remove('sfb-page-transition');
            }

            document.querySelectorAll('[data-sfb-page-link]').forEach(function (link) {
                link.addEventListener('click', function () {
                    try { sessionStorage.setItem('sfbPageLoading', '1'); } catch (e) {}
                    markLoading();
                    window.setTimeout(function () { scrollToResults('smooth'); }, 0);
                });
            });

            try {
                if (sessionStorage.getItem('sfbPageLoading') === '1') {
                    sessionStorage.removeItem('sfbPageLoading');
                    scrollToResults('auto');
                }
            } catch (e) {}

            clearLoading();
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) clearLoading();
            });
        })();
    </script>
@endsection