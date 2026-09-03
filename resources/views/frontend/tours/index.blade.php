@extends('frontend.layouts.app')

@php
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $selectedCountryCodes = collect((array) request('countries'))->filter()->values();
    $selectedCountryNames = $countries->whereIn('code', $selectedCountryCodes->all())->pluck('name')->values();
    $selectedCategorySlugs = collect((array) request('categories'))->filter()->values();
    $selectedCategoryNames = $categories->whereIn('slug', $selectedCategorySlugs->all())->pluck('name')->values();

    if (request('category')) {
        $queryCategory = $categories->firstWhere('slug', request('category'));
        if ($queryCategory && !$selectedCategoryNames->contains($queryCategory->name)) {
            $selectedCategoryNames->push($queryCategory->name);
        }
    }

    $pageSubject = $headerDestination?->name
        ?? ($selectedCountryNames->count() === 1 ? $selectedCountryNames->first() : null)
        ?? $activeCategory?->name
        ?? ($selectedCategoryNames->count() === 1 ? $selectedCategoryNames->first() : null)
        ?? 'African';

    $mainTitle = $pageSubject !== 'African'
        ? $pageSubject . ' Safari Tours & Holidays'
        : 'Our Best All Tours & Safaris Packages';
    $introSource = $headerDestination?->description
        ?? $activeCategory?->description
        ?? Setting::get('tours_listing_intro');
    $introText = $introSource
        ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($introSource))), 540)
        : 'Compare handcrafted safari tours, wildlife holidays, mountain adventures and beach escapes across East Africa. Use the filters to narrow the route, travel style, comfort level and price that fit your trip.';

    $startDateIso = request('when', '');
    $startTimestamp = $startDateIso ? strtotime($startDateIso) : false;
    $startDateDisplay = $startTimestamp ? date('j M Y', $startTimestamp) : '';

    $adults = max(1, (int) request('adults', 2));
    $children = max(0, (int) request('children', 0));
    $travellersTotal = $adults + $children;
    $travellersText = $adults . ' ' . Str::plural('Adult', $adults) . ($children ? ', ' . $children . ' ' . Str::plural('Child', $children) : '');

    $ratingDisplay = $avgRating ? number_format($avgRating, 1) : '4.8';
    $reviewDisplay = $reviewCount ?: $tours->total();
    $operatorName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $operatorLogo = Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp');

    $durationData = $durationCounts->toArray();
    $durationUpper = max(array_keys($durationData ?: [14 => 1]));
    $durationUpper = max(7, min($durationUpper, 28));
    $durationMaxCount = max(array_values($durationData ?: [1]));
    $durationMinValue = request('duration_min');
    $durationMaxValue = request('duration_max');
    $priceMinValue = request('price_min');
    $priceMaxValue = request('price_max');
    $priceSliderMin = max(0, (int) $priceMin);
    $priceSliderMax = max($priceSliderMin + 1, (int) $priceMax);

    $parkDestinations = $allDestinations->filter(function ($destination) {
        $type = strtolower((string) $destination->type);
        return $destination->tours_count > 0 && !in_array($type, ['country', 'region', 'continent'], true);
    })->values();
    if ($parkDestinations->isEmpty()) {
        $parkDestinations = $allDestinations->where('tours_count', '>', 0)->values();
    }

    $removeQueryParam = function (string $key, $value = null) {
        $query = request()->query();
        unset($query['page']);

        if ($value !== null && isset($query[$key]) && is_array($query[$key])) {
            $query[$key] = array_values(array_filter($query[$key], fn ($item) => (string) $item !== (string) $value));
            if (empty($query[$key])) {
                unset($query[$key]);
            }
        } else {
            unset($query[$key]);
        }

        return route('tours.index', $query);
    };

    $hasSelectedFilters = request()->filled('destination')
        || request()->filled('duration_min')
        || request()->filled('duration_max')
        || request()->filled('price_min')
        || request()->filled('price_max')
        || request()->filled('level')
        || request()->filled('rating')
        || request()->filled('category')
        || request()->filled('categories')
        || request()->filled('activities')
        || request()->filled('accommodation')
        || request()->filled('luxury')
        || request()->filled('countries')
        || request()->filled('parks')
        || $activeCategory;
@endphp

@section('body-class', 'is-tours-listing')

@section('title', $mainTitle . ' | Afro-Vertex Tours & Safaris')

@section('extra-head')
    <meta name="description" content="{{ Str::limit($introText, 160) }}">
@endsection

@section('page-content')
    <div class="sfb-listing-page">
        <nav class="sfb-breadcrumb" aria-label="Breadcrumb">
            <div class="sfb-container">
                <a href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">&rsaquo;</span>
                <a href="{{ route('tours.index') }}">All Tours</a>
                @if($pageSubject !== 'African')
                    <span aria-hidden="true">&rsaquo;</span>
                    <span>{{ $pageSubject }} Tours</span>
                @endif
            </div>
        </nav>

        <div class="sfb-container sfb-main-wrap">
            <button type="button" class="sfb-mobile-filter-toggle" data-sfb-open-filters aria-controls="sfbFilterDrawer" aria-expanded="false">
                <i class="isax isax-filter" aria-hidden="true"></i>
                Filter Tours
            </button>

            <div class="sfb-drawer-backdrop" data-sfb-close-filters hidden></div>

            <div class="sfb-layout">
                <aside class="sfb-sidebar" id="sfbFilterDrawer" aria-label="Tour filters" data-sfb-filter-drawer>
                    <div class="sfb-sidebar__mobile-head">
                        <strong>Filter Tours</strong>
                        <button type="button" data-sfb-close-filters aria-label="Close filters">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('tours.index') }}" class="sfb-filter-form" data-sfb-filter-form>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <section class="sfb-safari-panel" aria-labelledby="your-safari-title">
                            <h2 id="your-safari-title">Your Safari</h2>

                            <div class="sfb-safari-control">
                                <i class="isax isax-location5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <div class="sfb-whereto" data-sfb-wt>
                                    <div class="sfb-safari-field sfb-safari-field--button sfb-whereto__field{{ $headerDestination ? ' has-value' : '' }}" data-sfb-wt-field>
                                        <span class="sfb-safari-field__label">Where To</span>
                                        <input type="text"
                                               class="sfb-safari-field__input sfb-whereto__input"
                                               placeholder="Where To"
                                               autocomplete="off"
                                               role="combobox"
                                               aria-expanded="false"
                                               aria-controls="sfbWheretoListbox"
                                               aria-autocomplete="list"
                                               data-sfb-wt-input
                                               value="{{ $headerDestination?->name }}">
                                        <span class="sfb-whereto__affix">
                                            <svg class="sfb-whereto__search" data-sfb-wt-icon{{ $headerDestination ? ' hidden' : '' }} width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                                            <button type="button" class="sfb-whereto__remove" data-sfb-wt-remove aria-label="Remove destination"{{ $headerDestination ? '' : ' hidden' }}>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                    <input type="hidden" name="destination" data-sfb-wt-value value="{{ request('destination') }}">
                                </div>
                                <a class="sfb-add-link" href="#filter-countries">+ Add country, park or highlight</a>
                            </div>

                            <div class="sfb-safari-control">
                                <i class="isax isax-calendar-15 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="date" data-safpop-target="sfb-start-date-value" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar">
                                    <span class="sfb-safari-field__label">Start Date</span>
                                    <input type="text" class="sfb-safari-field__input" value="{{ $startDateDisplay }}" placeholder="Start Date" readonly data-safpop-display aria-label="Start Date">
                                    <i class="isax isax-arrow-right-3" aria-hidden="true"></i>
                                </button>
                                <input type="hidden" name="when" id="sfb-start-date-value" value="{{ $startDateIso }}">
                            </div>

                            <div class="sfb-safari-control">
                                <i class="isax isax-profile-2user5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="trav" data-trav-total="sfb-travellers-total" data-trav-adults="sfb-travellers-adults" data-trav-children="sfb-travellers-children" aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover">
                                    <span class="sfb-safari-field__label">Travelers</span>
                                    <input type="text" class="sfb-safari-field__input" value="{{ $travellersText }}" readonly data-safpop-display aria-label="Travelers">
                                    <span class="sfb-safari-field__remove" aria-hidden="true">&times;</span>
                                </button>
                                <input type="hidden" name="travellers" id="sfb-travellers-total" value="{{ $travellersTotal }}">
                                <input type="hidden" name="adults" id="sfb-travellers-adults" value="{{ $adults }}">
                                <input type="hidden" name="children" id="sfb-travellers-children" value="{{ $children }}">
                            </div>

                            <button type="submit" class="sfb-show-tours" data-sfb-show-tours data-total="{{ $totalPublishedTours }}">
                                Show <b data-sfb-show-count>{{ number_format($totalPublishedTours) }}</b> Tours
                            </button>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-length">
                            <h3 id="filter-length">Tour Length</h3>
                            <div class="sfb-histogram" aria-hidden="true">
                                @for($day = 1; $day <= $durationUpper; $day++)
                                    @php
                                        $count = (int) ($durationData[$day] ?? 0);
                                        $height = $durationMaxCount ? max(8, round(($count / $durationMaxCount) * 70)) : 8;
                                    @endphp
                                    <span style="height: {{ $height }}%"></span>
                                @endfor
                            </div>
                            <div class="sfb-range-stack">
                                <label>
                                    <span>Min Days</span>
                                    <input type="range" min="1" max="{{ $durationUpper }}" value="{{ $durationMinValue ?: 1 }}" data-sfb-range="duration_min">
                                </label>
                                <label>
                                    <span>Max Days</span>
                                    <input type="range" min="1" max="{{ $durationUpper }}" value="{{ $durationMaxValue ?: $durationUpper }}" data-sfb-range="duration_max">
                                </label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="duration_min" min="1" max="{{ $durationUpper }}" placeholder="Min" value="{{ $durationMinValue }}" data-sfb-auto>
                                <input type="number" name="duration_max" min="1" max="{{ $durationUpper }}" placeholder="Max" value="{{ $durationMaxValue }}" data-sfb-auto>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-price">
                            <h3 id="filter-price">Price Range</h3>
                            <div class="sfb-range-stack">
                                <label>
                                    <span>Min Price</span>
                                    <input type="range" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" step="50" value="{{ $priceMinValue ?: $priceSliderMin }}" data-sfb-range="price_min">
                                </label>
                                <label>
                                    <span>Max Price</span>
                                    <input type="range" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" step="50" value="{{ $priceMaxValue ?: $priceSliderMax }}" data-sfb-range="price_max">
                                </label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="price_min" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" placeholder="{{ number_format($priceSliderMin) }}" value="{{ $priceMinValue }}" data-sfb-auto>
                                <input type="number" name="price_max" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" placeholder="{{ number_format($priceSliderMax) }}" value="{{ $priceMaxValue }}" data-sfb-auto>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-type">
                            <h3 id="filter-type">Tour Type</h3>
                            <div class="sfb-check-list">
                                @foreach($categories as $category)
                                    <label>
                                        <input type="checkbox" name="categories[]" value="{{ $category->slug }}" {{ $selectedCategorySlugs->contains($category->slug) || request('category') === $category->slug || ($activeCategory?->slug === $category->slug) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-accommodation">
                            <h3 id="filter-accommodation">Accommodation</h3>
                            <div class="sfb-check-list">
                                @foreach(['camping' => 'Camping', 'lodge' => 'Lodge & Tented Camp'] as $value => $label)
                                    <label>
                                        <input type="checkbox" name="accommodation[]" value="{{ $value }}" {{ in_array($value, (array) request('accommodation'), true) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-countries-title" id="filter-countries">
                            <h3 id="filter-countries-title">Countries</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                @foreach($countries->filter(fn ($country) => $country->code) as $country)
                                    <label>
                                        <input type="checkbox" name="countries[]" value="{{ $country->code }}" {{ $selectedCountryCodes->contains($country->code) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $country->name }}</span>
                                        <em>{{ $country->count }}</em>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-parks">
                            <h3 id="filter-parks">Parks and Reserves</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                @foreach($parkDestinations as $parkDestination)
                                    <label>
                                        <input type="checkbox" name="parks[]" value="{{ $parkDestination->id }}" {{ in_array($parkDestination->id, array_map('intval', (array) request('parks')), true) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $parkDestination->name }}</span>
                                        <em>{{ $parkDestination->tours_count }}</em>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        @if($activities->isNotEmpty())
                        <section class="sfb-filter-section" aria-labelledby="filter-activities">
                            <h3 id="filter-activities">Activities</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                @foreach($activities as $activity)
                                    <label>
                                        <input type="checkbox" name="activities[]" value="{{ $activity->id }}" {{ in_array($activity->id, array_map('intval', (array) request('activities')), true) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $activity->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                        @endif

                        <section class="sfb-filter-section" aria-labelledby="filter-luxury">
                            <h3 id="filter-luxury">Luxury Level</h3>
                            <div class="sfb-check-list">
                                @foreach(['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury'] as $value => $label)
                                    <label>
                                        <input type="checkbox" name="luxury[]" value="{{ $value }}" {{ in_array($value, (array) request('luxury'), true) ? 'checked' : '' }} data-sfb-auto>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <div class="sfb-filter-actions">
                            <button type="submit">Apply Filters</button>
                            <a href="{{ route('tours.index') }}">Clear All Filters</a>
                        </div>
                    </form>
                </aside>

                <main class="sfb-results" id="sfb-results-start" aria-label="Safari tour results">
                    <header class="sfb-results-header">
                        <h1>{{ $mainTitle }}</h1>
                        <div class="sfb-rating-line">
                            <span class="sfb-stars" aria-label="5 star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                            <strong>{{ $ratingDisplay }} /5</strong>
                            <a href="#reviews">{{ number_format($reviewDisplay) }} reviews</a>
                        </div>
                        <p>{{ $introText }}</p>
                    </header>

                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!$hasSelectedFilters)
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All safaris</span>
                        @else
                            @if($headerDestination)
                                <a class="sfb-selected-chip sfb-selected-chip--blue" href="{{ $removeQueryParam('destination') }}">{{ $headerDestination->name }} <b>&times;</b></a>
                            @endif
                            @if($activeCategory && !request('category'))
                                <a class="sfb-selected-chip" href="{{ route('tours.index', request()->except(['page'])) }}">{{ $activeCategory->name }} <b>&times;</b></a>
                            @endif
                            @if(request('category'))
                                @php
                                    $queryCategory = $categories->firstWhere('slug', request('category'));
                                @endphp
                                @if($queryCategory)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('category') }}">{{ $queryCategory->name }} <b>&times;</b></a>
                                @endif
                            @endif
                            @foreach($selectedCategorySlugs as $slug)
                                @php
                                    $category = $categories->firstWhere('slug', $slug);
                                @endphp
                                @if($category)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('categories', $slug) }}">{{ $category->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @if(request()->filled('duration_min') || request()->filled('duration_max'))
                                <a class="sfb-selected-chip" href="{{ route('tours.index', array_diff_key(request()->except(['page']), ['duration_min' => true, 'duration_max' => true])) }}">{{ request('duration_min', '1') }}-{{ request('duration_max', $durationUpper) }} days <b>&times;</b></a>
                            @endif
                            @if(request()->filled('price_min') || request()->filled('price_max'))
                                <a class="sfb-selected-chip" href="{{ route('tours.index', array_diff_key(request()->except(['page']), ['price_min' => true, 'price_max' => true])) }}">${{ number_format((int) request('price_min', $priceSliderMin)) }}-${{ number_format((int) request('price_max', $priceSliderMax)) }} <b>&times;</b></a>
                            @endif
                            @foreach($selectedCountryCodes as $code)
                                @php
                                    $country = $countries->firstWhere('code', $code);
                                @endphp
                                @if($country)
                                    <a class="sfb-selected-chip sfb-selected-chip--blue" href="{{ $removeQueryParam('countries', $code) }}">{{ $country->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @foreach((array) request('parks') as $parkId)
                                @php
                                    $park = $allDestinations->firstWhere('id', (int) $parkId);
                                @endphp
                                @if($park)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('parks', $parkId) }}">{{ $park->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @foreach((array) request('activities') as $activityId)
                                @php
                                    $activity = $activities->firstWhere('id', (int) $activityId);
                                @endphp
                                @if($activity)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('activities', $activityId) }}">{{ $activity->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @foreach((array) request('accommodation') as $style)
                                <a class="sfb-selected-chip" href="{{ $removeQueryParam('accommodation', $style) }}">{{ $style === 'camping' ? 'Camping' : 'Lodge & Tented Camp' }} <b>&times;</b></a>
                            @endforeach
                            @foreach((array) request('luxury') as $luxury)
                                <a class="sfb-selected-chip" href="{{ $removeQueryParam('luxury', $luxury) }}">{{ ['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury'][$luxury] ?? ucfirst($luxury) }} <b>&times;</b></a>
                            @endforeach
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ route('tours.index') }}">Clear All Filters</a>
                        @endif
                    </div>

                    <div class="sfb-results-info">
                        <strong>{{ $tours->firstItem() ?: 0 }}&ndash;{{ $tours->lastItem() ?: 0 }} of {{ number_format($tours->total()) }}</strong>
                        <span>Rankings are based on performance, relevance and payment. <a href="#sfb-ranking-note">Learn more</a></span>
                    </div>

                    <div class="sfb-tour-grid">
                        @forelse($tours as $SingleTour)
                            @php
                                $tourDestinations = $SingleTour->destinations->sortBy('pivot.order')->values();
                                $tourDestinationText = $tourDestinations->take(4)->pluck('name')->implode(', ');
                                $extraDestinations = max(0, $tourDestinations->count() - 4);
                                $tourLevel = trim(str_replace('_', ' ', (string) $SingleTour->tour_level));
                                $tourType = $SingleTour->categories->take(2)->pluck('name')->implode(', ');
                                $cardRating = $tourRatings->get($SingleTour->id);
                                $cardRatingValue = $cardRating ? number_format((float) $cardRating->avg_rating, 1) : null;
                                $cardReviewCount = $cardRating ? (int) $cardRating->review_count : 0;
                                $durationText = $SingleTour->duration_days
                                    ? $SingleTour->duration_days . ' ' . Str::plural('day', $SingleTour->duration_days) . ($SingleTour->duration_nights ? ' / ' . $SingleTour->duration_nights . ' ' . Str::plural('night', $SingleTour->duration_nights) : '')
                                    : 'Flexible duration';
                            @endphp
                            <article class="sfb-tour-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('tour.show', $SingleTour->slug) }}" aria-label="View {{ $SingleTour->cardTitle() }}"></a>
                                <div class="sfb-tour-card__image-wrap">
                                    <img src="{{ $SingleTour->cardImageUrl('medium') }}" alt="{{ $SingleTour->cardTitle() }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}">
                                    <div class="sfb-tour-card__gradient" aria-hidden="true"></div>
                                    <button type="button" class="sfb-tour-card__heart" data-sfb-wishlist aria-label="Save tour">
                                        <i class="bi bi-heart" aria-hidden="true"></i>
                                    </button>
                                    <h2>{{ $SingleTour->cardTitle() }}</h2>
                                </div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid">
                                        <div>
                                            <span>Duration</span>
                                            <strong>{{ $durationText }}</strong>
                                        </div>
                                        <div>
                                            <span>Destination</span>
                                            <strong>{{ $tourDestinationText ?: 'East Africa' }}{{ $extraDestinations ? ' +' . $extraDestinations : '' }}</strong>
                                        </div>
                                        <div>
                                            <span>Accommodation</span>
                                            <strong>{{ $tourLevel ? Str::title($tourLevel) : 'Tailor-made comfort' }}</strong>
                                        </div>
                                        <div>
                                            <span>Tour Type</span>
                                            <strong>{{ $tourType ?: ($SingleTour->is_group_departure ? 'Group departure' : 'Private safari') }}</strong>
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
                                        <div class="sfb-tour-card__rating">
                                            <span class="sfb-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                            @if($cardRatingValue)
                                                <strong>{{ $cardRatingValue }}</strong>
                                                <span>{{ $cardReviewCount }} {{ Str::plural('review', $cardReviewCount) }}</span>
                                            @else
                                                <strong>New</strong>
                                                <span>No reviews yet</span>
                                            @endif
                                        </div>
                                        <div class="sfb-tour-card__price">
                                            @php $cardPrice = $fromPrices[$SingleTour->id] ?? null; @endphp
                                            @if($cardPrice)
                                                <span>From</span>
                                                <strong>${{ number_format($cardPrice['amount'], 0) }}</strong>
                                                <em>pp</em>
                                            @else
                                                <strong>Request a Quote</strong>
                                            @endif
                                        </div>
                                    </div>

                                    <a href="{{ route('tour.show', $SingleTour->slug) }}" class="sfb-tour-card__cta">View Tour</a>
                                </div>
                            </article>
                        @empty
                            <div class="sfb-empty-results">
                                <h2>No tours found matching your filters.</h2>
                                <p>Try clearing one or more filters to see more safari options.</p>
                                <a href="{{ route('tours.index') }}">Clear All Filters</a>
                            </div>
                        @endforelse
                    </div>

                    @include('frontend.tours.partials.pagination', ['paginator' => $tours])

                    <p class="sfb-ranking-note" id="sfb-ranking-note">Ranking signals combine tour relevance, destination match, current availability, editorial ordering and promotional placement.</p>

                    @if($activeCategory)
                        @foreach($activeCategory->sectionsBelowGrid as $section)
                            <section class="sfb-category-section">
                                @if($section->image_id)
                                    <img src="{{ $section->imageUrl('medium') }}" alt="{{ $section->title }}" loading="lazy">
                                @endif
                                <div>
                                    @if($section->title)
                                        <h2>{{ $section->title }}</h2>
                                    @endif
                                    @if($section->content)
                                        {!! $section->content !!}
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    @endif
                </main>
            </div>
        </div>
    </div>

                    <section class="tours-faq-section">
            <div class="sfb-container">
                @include('frontend.partials.faq-section', ['faqSubject' => $pageSubject])
            </div>
        </section>

@include('frontend.partials.safari-popovers')
    @include('frontend.partials.whereto-popover')
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

                form.querySelectorAll('[data-sfb-range]').forEach(function (range) {
                    var target = form.querySelector('[name="' + range.dataset.sfbRange + '"]');
                    if (!target) return;

                    range.addEventListener('input', function () {
                        target.value = range.value;
                    });

                    range.addEventListener('change', submitSoon);
                });
            }

            var calendar = document.getElementById('startDateCalendar');
            if (calendar) {
                calendar.addEventListener('click', function (event) {
                    if (event.target.closest('.calendar-day[data-iso]')) {
                        window.setTimeout(submitSoon, 50);
                    }
                });
            }

            var travellersDone = document.getElementById('tpDone');
            if (travellersDone) {
                travellersDone.addEventListener('click', function () {
                    window.setTimeout(submitSoon, 50);
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

            document.querySelectorAll('[data-sfb-wishlist]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    button.classList.toggle('is-active');
                    var icon = button.querySelector('i');
                    if (icon) icon.className = button.classList.contains('is-active') ? 'bi bi-heart-fill' : 'bi bi-heart';
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeDrawer();
            });

            /* ── Pagination: loading state + smooth scroll to results ── */
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

            /* ── FAQ: click-scroll, scrollspy, mobile accordion ──────── */
            var faqSection = document.querySelector('[data-sfb-faq]');
            if (faqSection) {
                var faqLinks = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-link]'));
                var faqItems = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-item]'));
                var faqDesktop = window.matchMedia('(min-width: 992px)');
                var FAQ_OFFSET = 110;

                function setActive(index) {
                    var key = String(index);
                    faqItems.forEach(function (item) {
                        item.classList.toggle('is-active', item.dataset.sfbFaqItem === key);
                    });
                    faqLinks.forEach(function (link) {
                        link.classList.toggle('is-active', link.dataset.sfbFaqLink === key);
                    });
                }

                function scrollToItem(item, smooth) {
                    if (!item) return;
                    var top = item.getBoundingClientRect().top + window.scrollY - FAQ_OFFSET;
                    window.scrollTo({ top: Math.max(top, 0), behavior: smooth ? 'smooth' : 'auto' });
                }

                function openItem(item) {
                    item.classList.add('is-open');
                    item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'true');
                    var body = item.querySelector('.sfb-faq-item__body');
                    body.style.maxHeight = body.scrollHeight + 'px';
                }

                function closeItem(item) {
                    item.classList.remove('is-open');
                    item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'false');
                    item.querySelector('.sfb-faq-item__body').style.maxHeight = '';
                }

                function applyMode() {
                    if (faqDesktop.matches) {
                        faqItems.forEach(closeItem);
                    } else {
                        faqItems.forEach(function (item, i) {
                            if (i === 0) openItem(item); else closeItem(item);
                        });
                    }
                }

                faqItems.forEach(function (item) {
                    item.querySelector('.sfb-faq-item__head').addEventListener('click', function () {
                        var index = parseInt(item.dataset.sfbFaqItem, 10);
                        if (!faqDesktop.matches) {
                            var wasOpen = item.classList.contains('is-open');
                            faqItems.forEach(closeItem);
                            if (!wasOpen) openItem(item);
                        } else {
                            setActive(isNaN(index) ? 0 : index);
                        }
                    });
                });

                faqLinks.forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        var index = parseInt(link.dataset.sfbFaqLink, 10);
                        if (isNaN(index)) return;
                        setActive(index);
                        var target = faqItems[index];
                        if (faqDesktop.matches) {
                            scrollToItem(target, true);
                        } else {
                            faqItems.forEach(closeItem);
                            openItem(target);
                            requestAnimationFrame(function () { scrollToItem(target, true); });
                        }
                    });
                });

                var faqTicking = false;
                window.addEventListener('scroll', function () {
                    if (!faqDesktop.matches || faqTicking) return;
                    faqTicking = true;
                    requestAnimationFrame(function () {
                        faqTicking = false;
                        var current = 0;
                        for (var i = 0; i < faqItems.length; i++) {
                            if (faqItems[i].getBoundingClientRect().top - FAQ_OFFSET <= 140) current = i;
                        }
                        setActive(current);
                    });
                }, { passive: true });

                applyMode();
                var onModeChange = function () { applyMode(); };
                if (faqDesktop.addEventListener) faqDesktop.addEventListener('change', onModeChange);
                else faqDesktop.addListener(onModeChange);
            }
        })();
    </script>
@endsection
