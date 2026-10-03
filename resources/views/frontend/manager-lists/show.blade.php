@extends('frontend.layouts.app')

@php
    use Illuminate\Support\Str;
    $listHeading = $managerList->caption ?: $managerList->title;
    $fallbackImage = asset('assets/images/safari-hero.jpg');
    $isTourList = $managerList->content_type === 'tours';
    $selectedCategorySlugs = collect((array) request('categories'))->filter()->values();
    $selectedCountryCodes = collect((array) request('countries'))->filter()->values();
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
        @if($isTourList)
            <button type="button" class="sfb-mobile-filter-toggle" data-manager-open-filters aria-controls="managerListFilters" aria-expanded="false"><i class="isax isax-filter" aria-hidden="true"></i> Filter Tours</button>
            <div class="manager-list-drawer-backdrop" data-manager-close-filters hidden></div>
        @endif
        <div class="sfb-layout manager-list-layout{{ $isTourList ? ' manager-tour-layout' : ' manager-page-layout' }}">
            @if($isTourList)
                <aside class="sfb-sidebar" id="managerListFilters" aria-label="Tour filters" data-manager-filter-drawer>
                    <div class="sfb-sidebar__mobile-head"><strong>Filter Tours</strong><button type="button" data-manager-close-filters aria-label="Close filters">&times;</button></div>
                    <form method="GET" action="{{ request()->url() }}" class="sfb-filter-form">
                        <section class="sfb-safari-panel">
                            <h2>Find a Tour</h2>
                            <label class="manager-search-label" for="manager-tour-search">Search tours</label>
                            <input id="manager-tour-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search this tour list">
                            <button type="submit" class="sfb-show-tours">Show {{ number_format($items->total()) }} Tours</button>
                        </section>
                        <section class="sfb-filter-section" aria-labelledby="manager-duration-title">
                            <h3 id="manager-duration-title">Tour Length</h3>
                            <div class="sfb-number-pair">
                                <label><span>Min days</span><input type="number" name="duration_min" min="1" max="{{ $facets['durationMax'] ?? 28 }}" value="{{ request('duration_min') }}" placeholder="Any"></label>
                                <label><span>Max days</span><input type="number" name="duration_max" min="1" max="{{ $facets['durationMax'] ?? 28 }}" value="{{ request('duration_max') }}" placeholder="Any"></label>
                            </div>
                        </section>
                        <section class="sfb-filter-section" aria-labelledby="manager-price-title">
                            <h3 id="manager-price-title">Price Range</h3>
                            <div class="sfb-number-pair">
                                <label><span>Min price</span><input type="number" name="price_min" min="0" value="{{ request('price_min') }}" placeholder="{{ number_format($facets['priceMin'] ?? 0) }}"></label>
                                <label><span>Max price</span><input type="number" name="price_max" min="0" value="{{ request('price_max') }}" placeholder="{{ number_format($facets['priceMax'] ?? 5000) }}"></label>
                            </div>
                        </section>
                        @if(($facets['categories'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-type-title">
                                <h3 id="manager-type-title">Tour Type</h3>
                                <div class="sfb-check-list">
                                    @foreach($facets['categories'] as $category)
                                        <label><input type="checkbox" name="categories[]" value="{{ $category->slug }}" {{ $selectedCategorySlugs->contains($category->slug) ? 'checked' : '' }}><span>{{ $category->name }}</span></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        <section class="sfb-filter-section" aria-labelledby="manager-accommodation-title">
                            <h3 id="manager-accommodation-title">Accommodation</h3>
                            <div class="sfb-check-list">
                                @foreach(['camping' => 'Camping', 'lodge' => 'Lodge & Tented Camp'] as $value => $label)
                                    <label><input type="checkbox" name="accommodation[]" value="{{ $value }}" {{ in_array($value, (array) request('accommodation'), true) ? 'checked' : '' }}><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </section>
                        @if(($facets['countries'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-countries-title">
                                <h3 id="manager-countries-title">Countries</h3>
                                <div class="sfb-check-list sfb-check-list--scroll">
                                    @foreach($facets['countries'] as $country)
                                        <label><input type="checkbox" name="countries[]" value="{{ $country->code }}" {{ $selectedCountryCodes->contains($country->code) ? 'checked' : '' }}><span>{{ $country->name }}</span><em>{{ $country->count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if(($facets['parks'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-parks-title">
                                <h3 id="manager-parks-title">Parks and Reserves</h3>
                                <div class="sfb-check-list sfb-check-list--scroll">
                                    @foreach($facets['parks'] as $park)
                                        <label><input type="checkbox" name="parks[]" value="{{ $park->id }}" {{ in_array($park->id, array_map('intval', (array) request('parks')), true) ? 'checked' : '' }}><span>{{ $park->name }}</span><em>{{ $park->tours_count }}</em></label>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if(($facets['startingPoints'] ?? collect())->isNotEmpty())
                            <section class="sfb-filter-section" aria-labelledby="manager-start-title">
                                <h3 id="manager-start-title">Starting Point</h3>
                                <select name="starting_point" class="form-select form-select-sm"><option value="">Any starting point</option>@foreach($facets['startingPoints'] as $point)<option value="{{ $point }}" {{ request('starting_point') === $point ? 'selected' : '' }}>{{ $point }}</option>@endforeach</select>
                            </section>
                        @endif
                        <section class="sfb-filter-section" aria-labelledby="manager-level-title">
                            <h3 id="manager-level-title">Luxury Level</h3>
                            <div class="sfb-check-list">
                                @foreach(['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury'] as $value => $label)
                                    <label><input type="checkbox" name="luxury[]" value="{{ $value }}" {{ in_array($value, (array) request('luxury'), true) ? 'checked' : '' }}><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                        </section>
                        <div class="sfb-filter-actions"><button type="submit">Apply Filters</button><a href="{{ request()->url() }}">Clear All Filters</a></div>
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
                                @php($category = ($facets['categories'] ?? collect())->firstWhere('slug', $slug))
                                @if($category)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('categories', $slug) }}">{{ $category->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @if(request()->filled('duration_min') || request()->filled('duration_max'))<a class="sfb-selected-chip" href="{{ $removeQueryParam('duration_min') }}">{{ request('duration_min', 'Any') }}–{{ request('duration_max', 'Any') }} days <b>&times;</b></a>@endif
                            @if(request()->filled('price_min') || request()->filled('price_max'))<a class="sfb-selected-chip" href="{{ $removeQueryParam('price_min') }}">Price range <b>&times;</b></a>@endif
                            @foreach($selectedCountryCodes as $code)
                                @php($country = ($facets['countries'] ?? collect())->firstWhere('code', $code))
                                @if($country)
                                    <a class="sfb-selected-chip sfb-selected-chip--blue" href="{{ $removeQueryParam('countries', $code) }}">{{ $country->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            @foreach((array) request('parks') as $parkId)
                                @php($park = ($facets['parks'] ?? collect())->firstWhere('id', (int) $parkId))
                                @if($park)
                                    <a class="sfb-selected-chip" href="{{ $removeQueryParam('parks', $parkId) }}">{{ $park->name }} <b>&times;</b></a>
                                @endif
                            @endforeach
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="{{ request()->url() }}">Clear All Filters</a>
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
                                    <div class="sfb-tour-card__meta-grid"><div><span>Type</span><strong>Information</strong></div><div><span>Reading time</span><strong>{{ $item->readingMinutes() }} min</strong></div><div><span>Length</span><strong>{{ number_format(str_word_count($plainText)) }} words</strong></div><div><span>Updated</span><strong>{{ $item->updated_at?->format('j M Y') ?: 'Recently' }}</strong></div></div>
                                    <div class="sfb-tour-card__footer"><div class="sfb-tour-card__rating"><strong>Read online</strong><span>Helpful travel information</span></div><div class="sfb-tour-card__price"><span>Access</span><strong>Free</strong></div></div>
                                    <a href="{{ route('page.show', $item->slug) }}" class="sfb-tour-card__cta">Read More</a>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="sfb-empty-results"><h2>{{ $isTourList && request()->query() ? 'No tours found matching your filters.' : 'No ' . ($isTourList ? 'tours' : 'pages') . ' are available in this listing yet.' }}</h2><p>{{ $isTourList && request()->query() ? 'Try clearing one or more filters to see more tours.' : 'Please check back soon.' }}</p>@if($isTourList && request()->query())<a href="{{ request()->url() }}">Clear All Filters</a>@endif</div>
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
</div>
<style>
.manager-list-layout{grid-template-columns:minmax(0,1fr)}.manager-tour-layout{grid-template-columns:320px minmax(0,1fr)!important;align-items:start}.manager-page-layout{grid-template-columns:minmax(0,1fr)!important}.manager-list-introduction{margin-top:14px;line-height:1.75}.manager-list-faq{margin:42px 0 20px}.manager-list-faq .sfb-faq{margin:0}.sfb-pagination{margin-top:28px}.manager-search-label{display:block;font-size:13px;margin:8px 0}.manager-list-layout .sfb-safari-panel input[type=search]{width:100%;border:1px solid #d9e0eb;border-radius:8px;padding:12px;background:#fff}.manager-list-drawer-backdrop{position:fixed;inset:0;background:rgba(18,29,48,.45);z-index:1040}.manager-list-drawer-backdrop[hidden]{display:none}@media(max-width:991px){.manager-tour-layout{grid-template-columns:minmax(0,1fr)!important}.manager-tour-layout .sfb-sidebar{position:fixed;top:0;left:0;bottom:0;width:min(390px,90vw);overflow-y:auto;z-index:1050;transform:translateX(-105%);transition:transform .25s ease}.manager-tour-layout .sfb-sidebar.is-open{transform:translateX(0)}body.manager-filter-lock{overflow:hidden}}
</style>
@endsection

@section('extra-scripts')
    @if($isTourList)
    <script>
    (function(){var drawer=document.querySelector('[data-manager-filter-drawer]'),backdrop=document.querySelector('.manager-list-drawer-backdrop'),open=document.querySelector('[data-manager-open-filters]');function close(){if(!drawer||!backdrop)return;drawer.classList.remove('is-open');backdrop.hidden=true;document.body.classList.remove('manager-filter-lock');if(open)open.setAttribute('aria-expanded','false')}if(open&&drawer&&backdrop){open.addEventListener('click',function(){drawer.classList.add('is-open');backdrop.hidden=false;document.body.classList.add('manager-filter-lock');open.setAttribute('aria-expanded','true')});document.querySelectorAll('[data-manager-close-filters]').forEach(function(el){el.addEventListener('click',close)});document.addEventListener('keydown',function(e){if(e.key==='Escape')close()})}})();
    </script>
    @endif
    @if($faqs->isNotEmpty()) @include('frontend.partials.faq-script') @endif
@endsection
