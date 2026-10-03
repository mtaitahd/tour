@extends('frontend.layouts.app')

@php
    use Illuminate\Support\Str;
    use App\Models\Setting;
    $listHeading = $managerList->caption ?: $managerList->title;
    $fallbackImage = asset('assets/images/safari-hero.jpg');
    $operatorName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $operatorLogo = Setting::logoUrlOrDefault();
    $isTourList = $managerList->content_type === 'tours';
    $isPageList = $managerList->content_type === 'pages';
    $selectedCategorySlugs = collect((array) request('categories'))->filter()->values();
    $selectedCountryCodes = collect((array) request('countries'))->filter()->values();
    $categoryFacetMap = ($facets['categories'] ?? collect())->keyBy('slug');
    $countryFacetMap = ($facets['countries'] ?? collect())->keyBy('code');
    $parkFacetMap = ($facets['parks'] ?? collect())->keyBy('id');
    $startDateIso = request('when', '');
    $startTimestamp = $startDateIso ? strtotime($startDateIso) : false;
    $startDateDisplay = $startTimestamp ? date('j M Y', $startTimestamp) : '';
    $adults = max(1, (int) request('adults', 2));
    $children = max(0, (int) request('children', 0));
    $travellersTotal = $adults + $children;
    $travellersText = $adults . ' ' . Str::plural('Adult', $adults) . ($children ? ', ' . $children . ' ' . Str::plural('Child', $children) : '');
    $durationData = ($facets['durationCounts'] ?? collect())->toArray();
    $durationUpper = max(7, min(28, (int) max(array_keys($durationData ?: [14 => 1]))));
    $durationMaxCount = max(array_values($durationData ?: [1]));
    $priceSliderMin = max(0, (int) ($facets['priceMin'] ?? 0));
    $priceSliderMax = max($priceSliderMin + 1, (int) ($facets['priceMax'] ?? 5000));
    $durationMinValue = request('duration_min');
    $durationMaxValue = request('duration_max');
    $priceMinValue = request('price_min');
    $priceMaxValue = request('price_max');
    $readFilters = $facets['readFilters'] ?? [];
    $updatedFilters = $facets['updatedFilters'] ?? [];
    $readLabels = $facets['readLabels'] ?? [];
    $updatedLabels = $facets['updatedLabels'] ?? [];
    $readCounts = $facets['readCounts'] ?? [];
    $updatedCounts = $facets['updatedCounts'] ?? [];
    $pageSearch = $facets['search'] ?? '';
    $pageSortLabels = ['alpha' => 'Title (A to Z)', 'featured' => 'Featured order', 'recent' => 'Recently updated'];
    $pageActiveSort = $facets['sort'] ?? 'alpha';
    $readOptions = array_filter($readCounts, fn ($count, $key) => $count > 0 || in_array($key, $readFilters, true), ARRAY_FILTER_USE_BOTH);
    $updatedOptions = array_filter($updatedCounts, fn ($count, $key) => $count > 0 || in_array($key, $updatedFilters, true), ARRAY_FILTER_USE_BOTH);
    $hasPageFilters = $pageSearch !== '' || $readFilters !== [] || $updatedFilters !== [] || $pageActiveSort !== 'alpha';
    $removePageFilter = function (?string $key = null, ?string $value = null) {
        $query = request()->query();
        unset($query['page']);
        if ($key !== null) {
            if ($value !== null && isset($query[$key]) && is_array($query[$key])) {
                $query[$key] = array_values(array_filter($query[$key], fn ($item) => (string) $item !== $value));
                if ($query[$key] === []) unset($query[$key]);
            } else {
                unset($query[$key]);
            }
        }
        return request()->url() . ($query ? '?' . http_build_query($query) : '');
    };
    $pageChips = [];
    if ($pageSearch !== '') $pageChips[] = ['label' => '“' . $pageSearch . '”', 'url' => $removePageFilter('search')];
    foreach ($readFilters as $value) $pageChips[] = ['label' => $readLabels[$value] ?? $value, 'url' => $removePageFilter('read', $value)];
    foreach ($updatedFilters as $value) $pageChips[] = ['label' => $updatedLabels[$value] ?? $value, 'url' => $removePageFilter('updated', $value)];
    if ($pageActiveSort !== 'alpha') $pageChips[] = ['label' => 'Sorted by ' . ($pageSortLabels[$pageActiveSort] ?? $pageActiveSort), 'url' => $removePageFilter('sort')];
    $removeQueryParam = function (string $key, $value = null) {
        $query = request()->query();
        unset($query['page']);
        if ($value !== null && isset($query[$key]) && is_array($query[$key])) {
            $query[$key] = array_values(array_filter($query[$key], fn ($item) => (string) $item !== (string) $value));
            if (!$query[$key]) unset($query[$key]);
        } else {
            unset($query[$key]);
        }
        return request()->url() . ($query ? '?' . http_build_query($query) : '');
    };
@endphp

@section('page-content')
<div class="sfb-listing-page">
    <div class="sfb-breadcrumb"><div class="sfb-container"><a href="{{ route('home') }}">Home</a><span>/</span><span>{{ $managerList->title }}</span></div></div>
    <div class="sfb-container sfb-main-wrap">
        @if($previewMode ?? false)
            <div class="alert alert-info" role="status"><strong>Preview mode:</strong> This listing is visible only to authorized admins. <a href="{{ $managerList->content_type === 'pages' ? route('admin.manager-lists.pages') : route('admin.manager-lists.tours') }}">Back to Manager Lists</a></div>
        @endif
        @if($isTourList || $isPageList)
            <button type="button" class="sfb-mobile-filter-toggle" data-manager-open-filters aria-controls="managerListFilters" aria-expanded="false"><i class="isax isax-filter" aria-hidden="true"></i> {{ $isTourList ? 'Filter Tours' : 'Filter Pages' }}</button>
            <div class="manager-list-drawer-backdrop" data-manager-close-filters hidden></div>
        @endif
        <div class="sfb-layout manager-list-layout{{ $isTourList ? ' manager-tour-layout' : ' manager-page-layout' }}">
            @if($isTourList)
                <aside class="sfb-sidebar" id="managerListFilters" aria-label="Tour filters" data-manager-filter-drawer>
                    <div class="sfb-sidebar__mobile-head"><strong>Filter Tours</strong><button type="button" data-manager-close-filters aria-label="Close filters">&times;</button></div>
                    <form method="GET" action="{{ request()->url() }}" class="sfb-filter-form" data-sfb-filter-form>
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        <section class="sfb-safari-panel" aria-labelledby="your-safari-title">
                            <h2 id="your-safari-title">Your Safari</h2>
                            <div class="sfb-safari-control">
                                <i class="isax isax-location5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <div class="sfb-whereto" data-sfb-wt>
                                    <div class="sfb-safari-field sfb-safari-field--button sfb-whereto__field{{ !empty($facets['headerDestination']) ? ' has-value' : '' }}" data-sfb-wt-field>
                                        <span class="sfb-safari-field__label"></span>
                                        <input type="text" class="sfb-safari-field__input sfb-whereto__input" placeholder="Where To" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="sfbWheretoListbox" aria-autocomplete="list" data-sfb-wt-input value="{{ $facets['headerDestination']->name ?? '' }}">
                                        <span class="sfb-whereto__affix">
                                            <svg class="sfb-whereto__search" data-sfb-wt-icon{{ !empty($facets['headerDestination']) ? ' hidden' : '' }} width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                                            <button type="button" class="sfb-whereto__remove" data-sfb-wt-remove aria-label="Remove destination"{{ !empty($facets['headerDestination']) ? '' : ' hidden' }}><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
                                        </span>
                                    </div>
                                    <input type="hidden" name="destination" data-sfb-wt-value value="{{ request('destination') }}">
                                </div>
                                <a class="sfb-add-link" href="#filter-countries">+ Add country, park or highlight</a>
                            </div>
                            <div class="sfb-safari-control">
                                <i class="isax isax-calendar-15 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="date" data-safpop-target="sfb-start-date-value" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar"><span class="sfb-safari-field__label"></span><input type="text" class="sfb-safari-field__input" value="{{ $startDateDisplay }}" placeholder="Start Date" readonly data-safpop-display aria-label="Start Date"><i class="isax isax-arrow-right-3" aria-hidden="true"></i></button>
                                <input type="hidden" name="when" id="sfb-start-date-value" value="{{ $startDateIso }}">
                            </div>
                            <div class="sfb-safari-control">
                                <i class="isax isax-profile-2user5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="trav" data-trav-total="sfb-travellers-total" data-trav-adults="sfb-travellers-adults" data-trav-children="sfb-travellers-children" aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover"><span class="sfb-safari-field__label"></span><input type="text" class="sfb-safari-field__input" value="{{ $travellersText }}" readonly data-safpop-display aria-label="Travelers"><span class="sfb-safari-field__remove" aria-hidden="true">&times;</span></button>
                                <input type="hidden" name="travellers" id="sfb-travellers-total" value="{{ $travellersTotal }}"><input type="hidden" name="adults" id="sfb-travellers-adults" value="{{ $adults }}"><input type="hidden" name="children" id="sfb-travellers-children" value="{{ $children }}">
                            </div>
                            <button type="submit" class="sfb-show-tours" data-sfb-show-tours data-total="{{ $facets['totalListTours'] ?? $items->total() }}">Show <b data-sfb-show-count>{{ number_format($facets['totalListTours'] ?? $items->total()) }}</b> Tours</button>
                        </section>
                        <section class="sfb-filter-section" aria-labelledby="manager-duration-title">
                            <h3 id="manager-duration-title">Tour Length</h3>
                            <div class="sfb-histogram" aria-hidden="true">
                                @for($day = 1; $day <= $durationUpper; $day++)
                                    @php $height = $durationMaxCount ? max(8, round((($durationData[$day] ?? 0) / $durationMaxCount) * 70)) : 8; @endphp
                                    <span style="height: {{ $height }}%"></span>
                                @endfor
                            </div>
                            <div class="sfb-range-stack">
                                <label><span>Min Days</span><input type="range" min="1" max="{{ $durationUpper }}" value="{{ $durationMinValue ?: 1 }}" data-sfb-range="duration_min"></label>
                                <label><span>Max Days</span><input type="range" min="1" max="{{ $durationUpper }}" value="{{ $durationMaxValue ?: $durationUpper }}" data-sfb-range="duration_max"></label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="duration_min" min="1" max="{{ $durationUpper }}" placeholder="Min" value="{{ $durationMinValue }}" data-sfb-auto>
                                <input type="number" name="duration_max" min="1" max="{{ $durationUpper }}" placeholder="Max" value="{{ $durationMaxValue }}" data-sfb-auto>
                            </div>
                        </section>
                        <section class="sfb-filter-section" aria-labelledby="manager-price-title">
                            <h3 id="manager-price-title">Price Range</h3>
                            <div class="sfb-range-stack">
                                <label><span>Min Price</span><input type="range" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" step="50" value="{{ $priceMinValue ?: $priceSliderMin }}" data-sfb-range="price_min"></label>
                                <label><span>Max Price</span><input type="range" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" step="50" value="{{ $priceMaxValue ?: $priceSliderMax }}" data-sfb-range="price_max"></label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="price_min" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" placeholder="{{ number_format($priceSliderMin) }}" value="{{ $priceMinValue }}" data-sfb-auto>
                                <input type="number" name="price_max" min="{{ $priceSliderMin }}" max="{{ $priceSliderMax }}" placeholder="{{ number_format($priceSliderMax) }}" value="{{ $priceMaxValue }}" data-sfb-auto>
                            </div>
                        </section>
                        @if(($facets['categories'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-type-title">
                                <h3 id="manager-type-title">Tour Type</h3>
                                <div class="sfb-check-list">
                                    @foreach($facets['categories'] as $category)
                                        <label><input type="checkbox" name="categories[]" value="{{ $category->slug }}" {{ $selectedCategorySlugs->contains($category->slug) ? 'checked' : '' }} data-sfb-auto><span>{{ $category->name }}</span></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        <section class="sfb-filter-section" aria-labelledby="manager-accommodation-title">
                            <h3 id="manager-accommodation-title">Accommodation</h3>
                            <div class="sfb-check-list">
                                @foreach(['camping' => 'Camping', 'lodge' => 'Lodge & Tented Camp'] as $value => $label)
                                    <label><input type="checkbox" name="accommodation[]" value="{{ $value }}" {{ in_array($value, (array) request('accommodation'), true) ? 'checked' : '' }} data-sfb-auto><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </section>
                        @if(($facets['countries'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-countries-title" id="filter-countries">
                                <h3 id="manager-countries-title">Countries</h3>
                                <div class="sfb-check-list sfb-check-list--scroll">
                                    @foreach($facets['countries'] as $country)
                                        <label><input type="checkbox" name="countries[]" value="{{ $country->code }}" {{ $selectedCountryCodes->contains($country->code) ? 'checked' : '' }} data-sfb-auto><span>{{ $country->name }}</span><em>{{ $country->count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if(($facets['parks'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-parks-title">
                                <h3 id="manager-parks-title">Parks and Reserves</h3>
                                <div class="sfb-check-list sfb-check-list--scroll">
                                    @foreach($facets['parks'] as $park)
                                        <label><input type="checkbox" name="parks[]" value="{{ $park->id }}" {{ in_array($park->id, array_map('intval', (array) request('parks')), true) ? 'checked' : '' }} data-sfb-auto><span>{{ $park->name }}</span><em>{{ $park->tours_count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if(($facets['startingPoints'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-start-title">
                                <h3 id="manager-start-title">Starting Point</h3>
                                <select name="starting_point" class="form-select form-select-sm" data-sfb-auto><option value="">Any starting point</option>@foreach($facets['startingPoints'] as $point)<option value="{{ $point }}" {{ request('starting_point') === $point ? 'selected' : '' }}>{{ $point }}</option>@endforeach</select>
                            </section>
                        @endif
                        @if(($facets['activities'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-activities-title">
                                <h3 id="manager-activities-title">Activities</h3>
                                <div class="sfb-check-list sfb-check-list--scroll">
                                    @foreach($facets['activities'] as $activity)
                                        <label><input type="checkbox" name="activities[]" value="{{ $activity->id }}" {{ in_array($activity->id, array_map('intval', (array) request('activities')), true) ? 'checked' : '' }} data-sfb-auto><span>{{ $activity->name }}</span></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        <section class="sfb-filter-section" aria-labelledby="manager-level-title">
                            <h3 id="manager-level-title">Luxury Level</h3>
                            <div class="sfb-check-list">
                                @foreach(['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury'] as $value => $label)
                                    <label><input type="checkbox" name="luxury[]" value="{{ $value }}" {{ in_array($value, (array) request('luxury'), true) ? 'checked' : '' }} data-sfb-auto><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </section>
                        <div class="sfb-filter-actions"><button type="submit">Apply Filters</button><a href="{{ request()->url() }}">Clear All Filters</a></div>
                    </form>
                </aside>
            @elseif($isPageList)
                <aside class="sfb-sidebar sfb-sidebar--pages" id="managerListFilters" aria-label="Page filters" data-manager-filter-drawer>
                    <div class="sfb-sidebar__mobile-head"><strong>Filter Pages</strong><button type="button" data-manager-close-filters aria-label="Close filters">&times;</button></div>
                    <form method="GET" action="{{ request()->url() }}" class="sfb-filter-form" data-sfb-filter-form>
                        <section class="sfb-safari-panel" aria-labelledby="manager-page-search-title">
                            <h2 id="manager-page-search-title">Search Pages</h2>
                            <div class="sfb-safari-control">
                                <i class="isax isax-search-1 sfb-safari-control__icon" aria-hidden="true"></i>
                                <div class="sfb-pages-search"><input type="search" name="search" class="sfb-pages-search__input" placeholder="Search pages, e.g. travel guide" value="{{ $pageSearch }}" autocomplete="off" aria-label="Search pages"></div>
                            </div>
                            <button type="submit" class="sfb-show-tours">Search Pages</button>
                            <p class="sfb-pages-filters__hint" aria-live="polite"><strong>{{ number_format($items->total()) }}</strong> {{ Str::plural('page', $items->total()) }} {{ $hasPageFilters ? 'match your filters' : 'in this list' }}</p>
                        </section>
                        @if($readOptions)
                            <section class="sfb-filter-section" aria-labelledby="manager-page-reading-title">
                                <h3 id="manager-page-reading-title">Reading time</h3>
                                <div class="sfb-check-list">
                                    @foreach($readOptions as $value => $count)
                                        <label><input type="checkbox" name="read[]" value="{{ $value }}" {{ in_array($value, $readFilters, true) ? 'checked' : '' }} data-sfb-auto><span>{{ $readLabels[$value] }}</span><em>{{ $count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if($updatedOptions)
                            <section class="sfb-filter-section" aria-labelledby="manager-page-updated-title">
                                <h3 id="manager-page-updated-title">Last updated</h3>
                                <div class="sfb-check-list">
                                    @foreach($updatedOptions as $value => $count)
                                        <label><input type="checkbox" name="updated[]" value="{{ $value }}" {{ in_array($value, $updatedFilters, true) ? 'checked' : '' }} data-sfb-auto><span>{{ $updatedLabels[$value] }}</span><em>{{ $count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        <section class="sfb-filter-section" aria-labelledby="manager-page-sort-title">
                            <h3 id="manager-page-sort-title">Sort results by</h3>
                            <div class="sfb-check-list">
                                @foreach($pageSortLabels as $value => $label)
                                    <label><input type="radio" name="sort" value="{{ $value }}" {{ $pageActiveSort === $value ? 'checked' : '' }} data-sfb-auto><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </section>
                        <div class="sfb-filter-actions"><button type="submit">Apply Filters</button><a href="{{ request()->url() }}">Clear all filters</a></div>
                    </form>
                </aside>
            @endif
            <main class="sfb-results" id="sfb-results-start" aria-label="{{ $managerList->title }}">
                <header class="sfb-results-header">
                    <h1>{{ $listHeading }}</h1>
                    @if($managerList->introduction)<div class="manager-list-introduction">{!! $managerList->introduction !!}</div>@endif
                </header>
                @if($isTourList)
                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!request()->query()) <span class="sfb-selected-chip sfb-selected-chip--muted">All tours in this list</span>
                        @else
                            @if(request('search'))<a class="sfb-selected-chip" href="{{ $removeQueryParam('search') }}">{{ request('search') }} <b>&times;</b></a>@endif
                            @foreach($selectedCategorySlugs as $slug)
                                @if($categoryFacetMap->has($slug))
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('categories', $slug) }}">{{ $categoryFacetMap->get($slug)->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @if(request()->filled('duration_min') || request()->filled('duration_max'))<a class="sfb-selected-chip" href="{{ $removeQueryParam('duration_min') }}">{{ request('duration_min', 'Any') }}–{{ request('duration_max', 'Any') }} days <b>&times;</b></a>@endif
                            @if(request()->filled('price_min') || request()->filled('price_max'))<a class="sfb-selected-chip" href="{{ $removeQueryParam('price_min') }}">Price range <b>&times;</b></a>@endif
                            @foreach($selectedCountryCodes as $code)
                                @if($countryFacetMap->has($code))
                                    <a class="sfb-selected-chip sfb-selected-chip--blue" href="{{ $removeQueryParam('countries', $code) }}">{{ $countryFacetMap->get($code)->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @foreach((array) request('parks') as $parkId)
                                @if($parkFacetMap->has((int) $parkId))
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('parks', $parkId) }}">{{ $parkFacetMap->get((int) $parkId)->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ request()->url() }}">Clear All Filters</a>
                        @endif
                    </div>
                @elseif($isPageList)
                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        @if(!$hasPageFilters)
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All pages in this list</span>
                        @else
                            @foreach($pageChips as $chip)
                                <a class="sfb-selected-chip" href="{{ $chip['url'] }}">{{ $chip['label'] }} <b>&times;</b></a>
                            @endforeach
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ request()->url() }}">Clear all</a>
                        @endif
                    </div>
                @endif
                <div class="sfb-results-info"><strong>{{ $items->firstItem() ?: 0 }}&ndash;{{ $items->lastItem() ?: 0 }} of {{ number_format($items->total()) }}</strong><span>{{ Str::plural($isTourList ? 'tour' : 'page', $items->total()) }}</span></div>
                <div class="sfb-tour-grid">
                    @forelse($items as $item)
                        @if($managerList->content_type === 'tours')
                            @php
                                $destinationsText = $item->destinations->sortBy('pivot.order')->take(4)->pluck('name')->implode(', ');
                                $typeText = $item->categories->take(2)->pluck('name')->implode(', ');
                                $durationText = $item->duration_days ? $item->duration_days . ' ' . Str::plural('day', $item->duration_days) : 'Flexible duration';
                            @endphp
                            <article class="sfb-tour-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('tour.show', $item->slug) }}" aria-label="View {{ $item->cardTitle() }}"></a>
                                <div class="sfb-tour-card__image-wrap"><img src="{{ $item->cardImageUrl('medium') }}" alt="{{ $item->cardTitle() }}" loading="lazy"><div class="sfb-tour-card__gradient" aria-hidden="true"></div><h2>{{ $item->cardTitle() }}</h2></div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid"><div><span>Duration</span><strong>{{ $durationText }}</strong></div><div><span>Destination</span><strong>{{ $destinationsText ?: 'East Africa' }}</strong></div><div><span>Travel style</span><strong>{{ Str::title(str_replace('_', ' ', (string) $item->tour_level)) ?: 'Tailor-made' }}</strong></div><div><span>Tour type</span><strong>{{ $typeText ?: 'Safari adventure' }}</strong></div></div>
                                    <div class="sfb-tour-card__footer"><div class="sfb-tour-card__rating"><strong>Plan your trip</strong><span>Expertly arranged</span></div><div class="sfb-tour-card__price"><strong>View details</strong></div></div>
                                    <a href="{{ route('tour.show', $item->slug) }}" class="sfb-tour-card__cta">View Tour</a>
                                </div>
                            </article>
                        @else
                            @php
                                $heroImage = $item->hasHeroImage() ? ($item->heroUrl('medium') ?: $item->heroUrl() ?: $fallbackImage) : $fallbackImage;
                                $plainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) $item->content)));
                            @endphp
                            <article class="sfb-tour-card sfb-page-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('page.show', $item->slug) }}" aria-label="Read {{ $item->title }}"></a>
                                <div class="sfb-tour-card__image-wrap"><img src="{{ $heroImage }}" alt="{{ $item->title }}" loading="lazy"><div class="sfb-tour-card__gradient" aria-hidden="true"></div><h2>{{ $item->title }}</h2></div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid"><div><span>Type</span><strong>Information</strong></div><div><span>Reading time</span><strong>{{ $item->readingMinutes() }} {{ Str::plural('min', $item->readingMinutes()) }}</strong></div><div><span>Length</span><strong>{{ number_format(str_word_count($plainText)) }} {{ Str::plural('word', str_word_count($plainText)) }}</strong></div><div><span>Last updated</span><strong>{{ $item->updated_at?->format('j M Y') ?: 'Recently' }}</strong></div></div>
                                    <div class="sfb-tour-card__operator"><img src="{{ $operatorLogo }}" alt="" loading="lazy"><div><span>Published by</span><strong>{{ $operatorName }}</strong></div></div>
                                    <div class="sfb-tour-card__footer"><div class="sfb-tour-card__rating"><strong>Read online</strong><span>No booking required</span></div><div class="sfb-tour-card__price"><span>Access</span><strong>Free</strong><em>any time</em></div></div>
                                    <a href="{{ route('page.show', $item->slug) }}" class="sfb-tour-card__cta">Read More</a>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="sfb-empty-results"><h2>{{ ($isTourList && request()->query()) || ($isPageList && $hasPageFilters) ? 'No ' . ($isTourList ? 'tours' : 'pages') . ' found matching your filters.' : 'No ' . ($isTourList ? 'tours' : 'pages') . ' are available in this listing yet.' }}</h2><p>{{ ($isTourList && request()->query()) || ($isPageList && $hasPageFilters) ? 'Try clearing one or more filters to see more results.' : 'Please check back soon.' }}</p>@if(($isTourList && request()->query()) || ($isPageList && $hasPageFilters))<a href="{{ request()->url() }}">Clear All Filters</a>@endif</div>
                    @endforelse
                </div>
                @if($items->hasPages())<div class="sfb-pagination">{{ $items->links() }}</div>@endif
            </main>
        </div>
    </div>
    @if($faqs->isNotEmpty())
        <section class="tours-faq-section manager-list-faq">
            <div class="sfb-container">@include('frontend.partials.faq-section', ['faqs' => $faqs, 'faqExpert' => $faqExpert, 'faqSubject' => $managerList->title])</div>
        </section>
        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($faq) => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif
    @if($isTourList)
        @include('frontend.partials.safari-popovers')
        @include('frontend.partials.whereto-popover')
    @endif
</div>
<style>
.manager-tour-layout,.manager-page-layout{align-items:flex-start}.manager-list-introduction{margin-top:14px;line-height:1.75;color:#343a40!important;font-family:inherit!important}.manager-list-introduction *{color:#343a40!important;font-family:inherit!important}.manager-list-introduction :is(code,pre,kbd,samp){background:transparent!important;white-space:normal;font-size:inherit}.manager-page-layout .manager-list-introduction,.manager-page-layout .manager-list-introduction :where(*){color:#343a40!important;font-family:inherit!important}.manager-list-faq{margin:42px 0 20px}.manager-list-faq .sfb-faq{margin:0}.sfb-pagination{margin-top:28px}.manager-list-drawer-backdrop{position:fixed;inset:0;background:rgba(18,29,48,.45);z-index:1040}.manager-list-drawer-backdrop[hidden]{display:none}@media(max-width:991.98px){.manager-tour-layout,.manager-page-layout{display:block}.manager-tour-layout .sfb-sidebar,.manager-page-layout .sfb-sidebar{position:fixed;top:0;left:0;bottom:0;width:min(430px,90vw);max-height:100vh;overflow-y:auto;z-index:1050;transform:translateX(-105%);transition:transform .25s ease}.manager-tour-layout .sfb-sidebar.is-open,.manager-page-layout .sfb-sidebar.is-open{transform:translateX(0)}body.manager-filter-lock{overflow:hidden}}
</style>
@endsection

@section('extra-scripts')
    @if($isTourList || $isPageList)
    <script>
    (function(){'use strict';var form=document.querySelector('[data-sfb-filter-form]'),drawer=document.querySelector('[data-manager-filter-drawer]'),backdrop=document.querySelector('.manager-list-drawer-backdrop'),open=document.querySelector('[data-manager-open-filters]'),timer;function submitSoon(){if(!form)return;window.clearTimeout(timer);timer=window.setTimeout(function(){if(form.requestSubmit)form.requestSubmit();else form.submit()},250)}if(form){form.querySelectorAll('[data-sfb-auto]').forEach(function(field){field.addEventListener('change',submitSoon)});form.querySelectorAll('[data-sfb-range]').forEach(function(range){var target=form.querySelector('[name="'+range.dataset.sfbRange+'"]');if(!target)return;range.addEventListener('input',function(){target.value=range.value});range.addEventListener('change',submitSoon)})}var calendar=document.getElementById('startDateCalendar');if(calendar)calendar.addEventListener('click',function(event){if(event.target.closest('.calendar-day[data-iso]'))window.setTimeout(submitSoon,50)});var travellersDone=document.getElementById('tpDone');if(travellersDone)travellersDone.addEventListener('click',function(){window.setTimeout(submitSoon,50)});function close(){if(!drawer||!backdrop)return;drawer.classList.remove('is-open');backdrop.hidden=true;document.body.classList.remove('manager-filter-lock');if(open)open.setAttribute('aria-expanded','false')}if(open&&drawer&&backdrop){open.addEventListener('click',function(){drawer.classList.add('is-open');backdrop.hidden=false;document.body.classList.add('manager-filter-lock');open.setAttribute('aria-expanded','true')});document.querySelectorAll('[data-manager-close-filters]').forEach(function(el){el.addEventListener('click',close)});document.addEventListener('keydown',function(e){if(e.key==='Escape')close()})}})();
    </script>
    @endif
    @if($faqs->isNotEmpty()) @include('frontend.partials.faq-script') @endif
@endsection
