@extends('frontend.layouts.app')
@section('page-content')

@php
/* ════════════════════════════════════════════════════════════════════
   TOUR DETAILS — shared helpers & derived flags.
   Everything below is driven by the current tour's real database data;
   nothing is hard-coded for a single departure.
   ════════════════════════════════════════════════════════════════════ */

// ── Tabs: hide tabs whose backing data does not exist ──
$hasDayByDay  = $itinerary->isNotEmpty();
$hasRates     = $hasTourPrice && $seasonPricing->isNotEmpty();
$hasInclExcl  = count($tour->inclusions ?? []) > 0 || count($tour->exclusions ?? []) > 0;
$hasGetting   = (bool) ($mapEmbedUrl || trim((string) $tour->starting_point) !== '' || trim((string) $tour->ending_point) !== '' || count($transportRows) > 0);

$tdTabs = [['id' => 'overview', 'label' => 'Overview']];
if ($hasDayByDay) { $tdTabs[] = ['id' => 'daybyday', 'label' => 'Day by Day']; }
if ($hasTourPrice) { $tdTabs[] = ['id' => 'pricing', 'label' => 'Pricing']; }
if ($hasInclExcl) { $tdTabs[] = ['id' => 'inclusions', 'label' => 'Inclusions']; }
if ($hasGetting)  { $tdTabs[] = ['id' => 'getting', 'label' => 'Getting There']; }
$tdTabs[] = ['id' => 'offeredby', 'label' => 'Offered By'];

// ── Star renderer (gold SVG, rounded rating) ──
$renderStars = function (?float $rating, string $sizeClass = '') {
    $value = max(0, min(5, (int) round((float) ($rating ?? 0))));
    $html  = '<span class="td-stars ' . $sizeClass . '" role="img" aria-label="' . number_format((float) ($rating ?? 0), 1) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<svg class="td-star' . ($i <= $value ? ' is-full' : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.83 6.1 6.67.57-5.06 4.48 1.51 6.55L12 16.85 6.05 20.3l1.51-6.55L2.5 9.27l6.67-.57z"/></svg>';
    }
    return $html . '</span>';
};

// ── Feature / transport icons (red outline SVG set) ──
$iconSvg = function (string $key) {
    $paths = [
        'bed'      => '<path d="M3 18v-8m0 4h18m0 4v-6a2 2 0 0 0-2-2H11v4"/><circle cx="7" cy="10" r="2"/><path d="M3 6h18"/>',
        'group'    => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.4"/><path d="M15.5 14.6c2.9.4 5 2.7 5 5.4"/>',
        'private'  => '<path d="M12 3l2.4 5 5.6.8-4 3.9 1 5.5-5-2.6-5 2.6 1-5.5-4-3.9L9.6 8z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>',
        'activity' => '<circle cx="13" cy="5" r="1.6"/><path d="M10 21l2.5-6L9 12l1-4 5 1 3 3m-8 0l-2 5"/>',
        'vehicle'  => '<path d="M4 16v-4l2-5h12l2 5v4M4 16h16M4 16v2m16-2v2"/><circle cx="8" cy="18" r="1.6"/><circle cx="16" cy="18" r="1.6"/>',
        'plane'    => '<path d="M10.5 13.5L3 11l1.5-2 6 1 5-6 2 1-3.5 6.5 6 2-1.5 2.5-6.5-.5-2 4H8z"/>',
        'camera'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><circle cx="12" cy="13" r="4"/><path d="M8 7l1.5-3h5L16 7"/>',
    ];
    $d = $paths[$key] ?? $paths['activity'];

    return '<svg viewBox="0 0 24 24" fill="none" stroke="#c13d31" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};

$levelLabels = ['budget' => 'Budget', 'mid_range' => 'Mid-range', 'midrange' => 'Mid-range', 'luxury' => 'Luxury'];
$tourLevelLabel = $levelLabels[strtolower((string) $tour->tour_level)] ?? ucfirst((string) $tour->tour_level);

$videoUrl = (string) $tour->video_url;
if (Str::contains($videoUrl, 'youtu.be')) {
    $videoUrl = 'https://www.youtube.com/embed/' . Str::before(Str::afterLast($videoUrl, '/'), '?');
} elseif (Str::contains($videoUrl, 'watch?v=')) {
    $videoUrl = 'https://www.youtube.com/embed/' . Str::before(Str::after($videoUrl, 'v='), '&');
}

/* ── Gallery registry consumed by the custom lightbox.
      Each group is a list of {u: url, c: caption}; thumbs reference
      data-group/data-index so every clickable thumbnail opens the exact
      image the user clicked. ── */
$tdGallery = [];

foreach ($galleryImages as $media) {
    $tdGallery['tour'][] = [
        'u' => $media->getUrl(),
        'c' => $tour->cardTitle(),
    ];
}

$accommodationPhotoData = []; // dayNumber => ['thumbs'=>[{thumb,caption,group,index}], 'count'=>N]
foreach ($accommodationRows as $row) {
    $photos = [];
    foreach ($row['tiers'] as $tier) {
        foreach (($tier['photos'] ?? []) as $fullUrl) {
            $photos[] = ['u' => $fullUrl, 'c' => trim($tier['type'] . ' · ' . $tier['name'], ' ·')];
        }
    }
    if (count($photos) > 0) {
        $tdGallery['acc-day-' . $row['day']] = $photos;
        $accommodationPhotoData[$row['day']] = $photos;
    }
}

$dayPhotoData = [];
foreach ($itinerary as $index => $day) {
    $images = $tour->dayImages($index);
    if ($images->isEmpty()) continue;
    $photos = [];
    foreach ($images as $image) {
        $photos[] = ['u' => $image->getUrl(), 'c' => 'Day ' . ($index + 1) . ' · ' . ($day['title'] ?? '')];
    }
    $tdGallery['day-' . ($index + 1)] = $photos;
    $dayPhotoData[$index + 1] = $photos;
}

$accommodationNotice = null;
if ($accommodationRows->isNotEmpty() && !$accommodationRows->first()['hasStay'] && trim((string) $tour->starting_point) !== '') {
    $accommodationNotice = 'Your adventure begins in ' . e($tour->starting_point) . ' — no accommodation is included on Day 1 until the safari starts. Pre-tour nights can be arranged on request.';
}
@endphp

<div class="td-page">

    {{-- ══ 1. BREADCRUMB ═══════════════════════════════════════════ --}}
    <div class="td-breadcrumb">
        <nav aria-label="Breadcrumb">
            <ol class="td-breadcrumb__list">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <li><a href="{{ route('tours.index') }}">All Tours</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                @if($countryName)
                    <li><a href="{{ route('tours.index', ['destination' => optional($tour->destinations->first())->slug]) }}">{{ $countryName }} Tours</a></li>
                    <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                @endif
                <li><a href="{{ $operator['link'] }}">Tour Operator</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <li class="td-breadcrumb__current" aria-current="page">{{ $tour->cardTitle() }}</li>
            </ol>
        </nav>
    </div>

    {{-- ══ 2. HERO ═════════════════════════════════════════════════ --}}
    <section class="td-hero">
        <img src="{{ $heroUrl }}" alt="{{ $tour->cardTitle() }} hero photo" class="td-hero__img">
        <div class="td-hero__shade" aria-hidden="true"></div>

        <button type="button" class="td-wishlist" data-sfb-wishlist-tour="{{ $tour->id }}"
                aria-label="Save this tour to your wishlist">
            <svg class="td-wishlist__heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.9-10-9.5C.4 8 2 4.5 5.5 4.2 7.6 4 9.3 5 12 7.6 14.7 5 16.4 4 18.5 4.2 22 4.5 23.6 8 22 11.5 19.5 16.1 12 21 12 21z"/></svg>
            <span class="td-wishlist__count" data-sfb-wish-count hidden>0</span>
        </button>

        <div class="td-hero__content">
            <div class="td-container">
                <h1 class="td-hero__title">{{ $tour->cardTitle() }}</h1>
                <p class="td-hero__offered">Offered By: {{ $operator['name'] }}</p>
                <div class="td-hero__rating">
                    {!! $renderStars($displayRating) !!}
                    @if($displayRating)
                        <span class="td-hero__score">{{ number_format((float) $displayRating, 1) }}</span>
                    @endif
                    @if($displayReviews)
                        <a href="#td-reviews-card" class="td-hero__reviews td-js-scroll" data-target="td-reviews-card">{{ $displayReviews }} review{{ $displayReviews === 1 ? '' : 's' }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══ 3. STICKY TABS ══════════════════════════════════════════ --}}
    <div class="td-tabsbar" id="td-tabsbar">
        <div class="td-container td-tabsbar__inner">
            <nav class="td-tabs" aria-label="Tour sections">
                @foreach($tdTabs as $tab)
                    <a href="#td-{{ $tab['id'] }}"
                       class="td-tab{{ $loop->first ? ' is-active' : '' }}"
                       data-td-tab="{{ $tab['id'] }}">{{ $tab['label'] }}</a>
                @endforeach
            </nav>
            <button type="button" class="td-btn td-btn--quote td-tabsbar__cta td-js-quote-jump">
                Get a Free Quote <span aria-hidden="true">›</span>
            </button>
        </div>
    </div>

    {{-- ══ 4. MAIN TWO-COLUMN LAYOUT ═══════════════════════════════ --}}
    <div class="td-container td-layout">

        {{-- ─────────── LEFT COLUMN ─────────── --}}
        <div class="td-main">

            {{-- 5. OVERVIEW --}}
            <section class="td-section" id="td-overview" data-td-section="overview">
                <div class="td-overview">
                    {!! $tour->overview !!}
                </div>
            </section>

            {{-- 6. ROUTE --}}
            @if(count($routeMapDays) > 0 || count($routePoints) > 2 || $mapEmbedUrl)
                <section class="td-section td-white-card" id="td-route">
                    <h2 class="td-heading">Route<span class="td-heading__line" aria-hidden="true"></span></h2>

                    <div class="td-route">
                        <div class="td-route__mapwrap">
                            @if(count($routeMapDays) > 0)
                                <div id="td-leaflet-route" style="width:100%;height:420px;position:relative;z-index:0;" data-route='@json($routeMapDays)'></div>
                            @elseif($mapEmbedUrl)
                                <iframe src="{{ $mapEmbedUrl }}" title="{{ $countryName }} route map"
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                        allowfullscreen></iframe>
                            @else
                                <div class="td-routemap" aria-hidden="true">
                                    @foreach($routePoints as $point)
                                        <div class="td-routemap__node td-routemap__node--{{ $point['marker'] }}">
                                            <span class="td-routemap__dot"></span>
                                            <span class="td-routemap__label">{{ $point['place'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="td-route__list-wrap">
                            <p class="td-route__country">
                                @if($countryFlag)<span class="td-flag" aria-hidden="true">{{ $countryFlag }}</span>@endif
                                <strong>{{ $countryName }}</strong>
                            </p>
                            <ul class="td-route__list">
                                @foreach($routePoints as $point)
                                    <li class="td-route__item td-route__item--{{ $point['marker'] }}">
                                        <span class="td-route__dot" aria-hidden="true"></span>
                                        <span class="td-route__text">
                                            <strong>{{ $point['label'] }}</strong>@if($point['days']) <em>({{ $point['days'] }})</em>@endif — {{ $point['place'] }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            @endif

            {{-- 7. TOUR FEATURES --}}
            @if(count($features))
                <section class="td-section td-white-card">
                    <h2 class="td-heading">Tour Features<span class="td-heading__line" aria-hidden="true"></span></h2>
                    <div class="td-features">
                        @foreach($features as $feature)
                            <div class="td-feature">
                                <span class="td-feature__icon">{!! $iconSvg($feature['icon']) !!}</span>
                                <span class="td-feature__body">
                                    <strong>{{ $feature['title'] }}</strong>
                                    <span>{{ $feature['text'] }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 8. ACTIVITIES & TRANSPORTATION --}}
            @if(count($transportRows) || $safariCarImages->isNotEmpty())
                <section class="td-section td-white-card">
                    <h2 class="td-heading">Activities &amp; Transportation<span class="td-heading__line" aria-hidden="true"></span></h2>
                    <ul class="td-transport">
                        @foreach($transportRows as $row)
                            <li>
                                <span class="td-transport__icon">{!! $iconSvg($row['icon']) !!}</span>
                                <span>{{ $row['label'] }} <strong>{{ $row['value'] }}</strong></span>
                            </li>
                        @endforeach
                        @if($safariCarImages->isNotEmpty())
                            <li>
                                <span class="td-transport__icon">{!! $iconSvg('vehicle') !!}</span>
                                <span>Game-drive vehicle <em>with pop-up roof for optimal wildlife viewing</em></span>
                            </li>
                        @endif
                    </ul>
                </section>
            @endif

            {{-- 9. ACCOMMODATION & MEALS --}}
            @if($accommodationRows->isNotEmpty())
                <section class="td-section td-white-card" id="td-accommodation">
                    <h2 class="td-heading">Accommodation &amp; Meals<span class="td-heading__line" aria-hidden="true"></span></h2>

                    @if($accommodationNotice)
                        <p class="td-note">{!! $accommodationNotice !!}</p>
                    @endif

                    <div class="td-acc-table" role="table" aria-label="Day by day accommodation and meals">
                        <div class="td-acc-row td-acc-row--head" role="row">
                            <span role="columnheader">Day</span>
                            <span role="columnheader">Accommodation</span>
                            <span role="columnheader">Meals</span>
                            <span role="columnheader">Photos</span>
                        </div>

                        @foreach($accommodationRows as $row)
                            <div class="td-acc-row{{ $loop->odd ? ' is-alt' : '' }}" role="row">
                                <span class="td-acc-day" role="cell">
                                    @if(!$row['hasStay'] && $loop->last)
                                        End of tour<br><small>Day {{ $row['day'] }}</small>
                                    @else
                                        Day {{ $row['day'] }}
                                    @endif
                                </span>

                                <span class="td-acc-stay" role="cell">
                                    @if($row['hasStay'])
                                        @foreach($row['tiers'] as $tier)
                                            <span class="td-acc-tier">
                                                <em class="td-acc-badge td-acc-badge--{{ $tier['tier_key'] }}">{{ $tier['type'] }}</em>
                                                @if($tier['url'])
                                                    <a href="{{ $tier['url'] }}" target="_blank" rel="noopener">{{ $tier['name'] }}</a>
                                                @else
                                                    <strong>{{ $tier['name'] }}</strong>
                                                @endif
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="td-acc-none">(No accommodation)</span>
                                    @endif
                                </span>

                                <span class="td-acc-meals" role="cell">{{ $row['meals'] !== '' ? $row['meals'] : '—' }}</span>

                                <span class="td-acc-thumbs" role="cell">
                                    @php $photos = $accommodationPhotoData[$row['day']] ?? []; @endphp
                                    @if(count($photos))
                                        @foreach(array_slice($photos, 0, 3) as $pi => $photo)
                                            <button type="button" class="td-thumb td-js-lightbox"
                                                    data-group="acc-day-{{ $row['day'] }}" data-index="{{ $pi }}"
                                                    aria-label="Open photo: {{ $photo['c'] }}">
                                                <img src="{{ $photo['u'] }}" alt="{{ $photo['c'] }}" loading="lazy">
                                            </button>
                                        @endforeach
                                        @if(count($photos) > 3)
                                            <button type="button" class="td-thumb td-thumb--more td-js-lightbox"
                                                    data-group="acc-day-{{ $row['day'] }}" data-index="3"
                                                    aria-label="Open all {{ count($photos) }} photos">
                                                +{{ count($photos) - 3 }}<span>Photos</span>
                                            </button>
                                        @endif
                                    @else
                                        <span class="td-acc-nophotos">—</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 16. INTERESTED IN THIS TOUR --}}
            <section class="td-interested">
                <h2>Interested in This Tour?</h2>
                <p>Tell us your travel dates and group size — you will receive a personalised proposal directly from {{ $operator['name'] }}, normally within 24 hours.</p>
                <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                    Get a Free Quote <span aria-hidden="true">›</span>
                </button>
                <ul class="td-trustpoints">
                    <li>Best price guarantee</li>
                    <li>No booking fees — request is free</li>
                    @if($operator['email'])
                        <li>Or <a href="mailto:{{ $operator['email'] }}?subject={{ rawurlencode('Tour enquiry: ' . $tour->cardTitle()) }}">contact the operator directly</a></li>
                    @endif
                </ul>
            </section>

        </div><!-- /td-main -->

        {{-- ─────────── RIGHT SIDEBAR ─────────── --}}
        <aside class="td-sidebar">

            {{-- 11. QUOTE CARD --}}
            <div class="td-quote-card" id="td-quote-card">
                @if($priceFrom)
                    <p class="td-quote-price">
                        From <strong>${{ number_format((float) $priceFrom) }}</strong>
                        <span>pp ({{ $priceCurrency }})</span>
                    </p>
                @elseif($isPriceOnRequest)
                    <p class="td-quote-price td-quote-price--request">
                        <strong>Price on Request</strong>
                        <span>{{ $tour->pricing_source === 'none' ? 'Request a Package Price' : 'Prices vary by season — request a quote' }}</span>
                    </p>
                @endif
                @if($hasInclExcl)
                    <a href="#td-inclusions" class="td-quote-incl td-js-scroll" data-target="td-inclusions">What is included in this tour</a>
                @endif

                <h2 class="td-quote-title">Request a Quote</h2>

                <div class="td-quote-fields">
                    <button type="button" class="td-safari-field" data-safpop="date"
                            data-safpop-target="tdStartDate" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar">
                        <span class="td-safari-field__label">Start Date</span>
                        <input type="text" placeholder="Start Date" readonly data-safpop-display aria-label="Start Date">
                        <i class="isax isax-calendar-15" aria-hidden="true"></i>
                    </button>
                    <input type="hidden" id="tdStartDate" value="">

                    <button type="button" class="td-safari-field" data-safpop="trav"
                            data-trav-total="tdTravTotal" data-trav-adults="tdTravAdults" data-trav-children="tdTravChildren"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover">
                        <span class="td-safari-field__label">Travelers</span>
                        <input type="text" placeholder="Travelers" readonly data-safpop-display aria-label="Travelers">
                        <i class="isax isax-profile-2user5" aria-hidden="true"></i>
                    </button>
                    <input type="hidden" id="tdTravTotal" value="2">
                    <input type="hidden" id="tdTravAdults" value="2">
                    <input type="hidden" id="tdTravChildren" value="0">

                    <p class="td-quote-error" id="tdQuoteError" hidden>Please choose a start date first.</p>

                    <button type="button" class="td-btn td-btn--quote td-btn--block td-js-quote-jump">
                        Get a Free Quote <span aria-hidden="true">›</span>
                    </button>
                </div>

                <ul class="td-checklist">
                    <li>Best price guarantee</li>
                    <li>Request sent directly to the operator</li>
                    <li>Option to contact the operator directly</li>
                </ul>
            </div>

            {{-- 12. OPERATOR CARD --}}
            <div class="td-side-card td-operator">
                <div class="td-operator__logo">
                    <img src="{{ $operator['logo'] }}" alt="{{ $operator['name'] }} logo" loading="lazy">
                </div>
                <p class="td-operator__kicker">Offered By</p>
                <h3 class="td-operator__name"><a href="{{ $operator['link'] }}">{{ $operator['name'] }}</a></h3>
                <div class="td-operator__rating">
                    {!! $renderStars($siteRatingAvg) !!}
                    @if($siteRatingAvg)
                        <span class="td-hero__score">{{ number_format((float) $siteRatingAvg, 1) }}</span>
                    @endif
                    <span class="td-muted">({{ $siteReviewCount }} review{{ $siteReviewCount === 1 ? '' : 's' }})</span>
                </div>
                <ul class="td-operator__meta">
                    @if($countryFlag)
                        <li><span class="td-flag" aria-hidden="true">{{ $countryFlag }}</span> {{ $countryName }}</li>
                    @endif
                    @if($operator['location'])
                        <li>{{ $operator['location'] }}</li>
                    @endif
                    @if($operator['founded'])
                        <li>Founded {{ $operator['founded'] }}</li>
                    @endif
                    @if($operator['employees'])
                        <li>{{ $operator['employees'] }} employees</li>
                    @endif
                </ul>
                <a class="td-morelink" href="{{ $operator['link'] }}">More About This Operator ›</a>
            </div>

            {{-- 13. CUSTOMER REVIEWS CARD --}}
            <div class="td-side-card td-reviews" id="td-reviews-card">
                <h3 class="td-side-title">Customer Reviews</h3>

                @if($reviews->isNotEmpty())
                    <div class="td-reviews__viewport" data-td-review-viewport>
                        @foreach($reviews as $review)
                            <article class="td-review{{ $loop->first ? ' is-active' : '' }}" data-td-review>
                                <header class="td-review__head">
                                    @if($review['avatar'])
                                        <img class="td-review__avatar" src="{{ $review['avatar'] }}" alt="Photo of {{ $review['name'] }}" loading="lazy">
                                    @else
                                        <span class="td-review__avatar td-review__avatar--initial" aria-hidden="true">{{ $review['initial'] }}</span>
                                    @endif
                                    <div>
                                        <strong>{{ $review['name'] }}</strong>
                                        <span class="td-review__loc">
                                            @if($review['flag'])<span class="td-flag" aria-hidden="true">{{ $review['flag'] }}</span>@endif
                                            {{ $review['location'] }}
                                        </span>
                                    </div>
                                </header>
                                <div class="td-review__stars">
                                    {!! $renderStars((float) $review['rating']) !!}
                                    <span class="td-hero__score">{{ $review['rating'] }}.0</span>
                                </div>
                                <p class="td-review__preview" data-td-preview>{{ Str::limit($review['content'], 160) }}</p>
                                <p class="td-review__full" data-td-full hidden>{{ $review['content'] }}</p>
                                <button type="button" class="td-review__toggle" data-td-toggle
                                        aria-expanded="false">Full Review</button>
                            </article>
                        @endforeach
                    </div>

                    <div class="td-reviews__nav">
                        <button type="button" class="td-arrow" data-td-review-prev aria-label="Previous review">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        </button>
                        <span class="td-reviews__counter"><b data-td-review-current>1</b> of {{ $reviews->count() }}</span>
                        <button type="button" class="td-arrow" data-td-review-next aria-label="Next review">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                @else
                    <p class="td-muted">No reviews yet for this tour.</p>
                @endif
            </div>

            {{-- 14. RELATED LINKS --}}
            @if($relatedLinks->isNotEmpty())
                <div class="td-side-card td-relatedlinks">
                    <h3 class="td-side-title">Related Links</h3>
                    <ul>
                        @foreach($relatedLinks as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }} ›</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 15. PHOTO CARD --}}
            @if($galleryImages->isNotEmpty() || $tour->hasHeroImage())
                @php $photoCardImage = $galleryImages->first()?->getUrl('medium-webp') ?: ($galleryImages->first()?->getUrl() ?: $heroUrl); @endphp
                <div class="td-side-card td-photo-card">
                    <button type="button" class="td-photo-card__imgbtn td-js-lightbox" data-group="tour" data-index="0"
                            aria-label="Open tour photo gallery">
                        <img src="{{ $photoCardImage }}" alt="{{ $tour->cardTitle() }} photo" loading="lazy">
                    </button>
                    <button type="button" class="td-morelink td-js-lightbox" data-group="tour" data-index="0">
                        Open Photos ({{ $galleryImages->count() ?: 1 }})
                    </button>
                </div>
            @endif

            {{-- 15. MAP CARD --}}
            @if($mapEmbedUrl)
                <div class="td-side-card td-map-card">
                    <div class="td-map-card__preview">
                        <iframe src="{{ $mapEmbedUrl }}" title="{{ $countryName }} map preview"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" tabindex="-1"></iframe>
                    </div>
                    <button type="button" class="td-morelink td-js-map-open" data-map-url="{{ $mapEmbedUrl }}">
                        {{ $countryName ?: 'Destination' }} Map ›
                    </button>
                </div>
            @endif

        </aside>
    </div><!-- /td-layout -->

    {{-- ══ DAY BY DAY (tab target) ══════════════════════════════════ --}}
    @if($hasDayByDay)
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-daybyday" data-td-section="daybyday">
                <h2 class="td-heading">Day by Day<span class="td-heading__line" aria-hidden="true"></span></h2>

                <ol class="td-days">
                    @foreach($itinerary as $index => $day)
                        @php
                            $dayPhotos = $dayPhotoData[$index + 1] ?? [];
                            $dayTiers = collect($day['accommodations'] ?? []);
                        @endphp
                        <li class="td-day{{ $loop->first ? ' is-open' : '' }}" data-td-day data-td-day-idx="{{ $index + 1 }}">
                            <button type="button" class="td-day__head" data-td-day-toggle
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                    aria-controls="td-day-body-{{ $index + 1 }}">
                                <span class="td-day__num">{{ $index + 1 }}</span>
                                <span class="td-day__titles">
                                    <strong>{{ $day['title'] ?? 'Day ' . ($index + 1) }}</strong>
                                    @if($dayTiers->isNotEmpty())
                                        <span>{{ $dayTiers->pluck('name')->implode(' / ') }}</span>
                                    @endif
                                </span>
                                <svg class="td-day__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>

                            <div class="td-day__body" id="td-day-body-{{ $index + 1 }}" data-td-day-body>
                                <div class="td-day__grid">
                                    <div class="td-day__desc rich-text-content">{!! $day['description'] ?? '' !!}</div>

                                    @if(count($dayPhotos))
                                        <div class="td-day__thumbs">
                                            @foreach(array_slice($dayPhotos, 0, 4) as $pi => $photo)
                                                <button type="button" class="td-thumb td-js-lightbox"
                                                        data-group="day-{{ $index + 1 }}" data-index="{{ $pi }}"
                                                        aria-label="Open photo: {{ $photo['c'] }}">
                                                    <img src="{{ $photo['u'] }}" alt="{{ $photo['c'] }}" loading="lazy">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <dl class="td-day__facts">
                                    @if(trim((string) ($day['meals'] ?? '')) !== '')
                                        <div><dt>Meals</dt><dd>{{ $day['meals'] }}</dd></div>
                                    @endif
                                    @if($dayTiers->isNotEmpty())
                                        <div><dt>Accommodation</dt><dd>{{ $dayTiers->pluck('name')->implode(' / ') }}</dd></div>
                                    @else
                                        <div><dt>Accommodation</dt><dd>(No accommodation)</dd></div>
                                    @endif
                                </dl>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    @endif

    {{-- ══ RATES (tab target) ═══════════════════════════════════════ --}}
    @if($hasTourPrice || $isPriceOnRequest)
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-pricing" data-td-section="pricing">
                <h2 class="td-heading">Pricing<span class="td-heading__line" aria-hidden="true"></span></h2>

                {{-- Pricing overview cards --}}
                <div class="td-pricing-hero">
                    <div class="td-pricing-hero__card">
                        <span class="td-pricing-hero__kicker">Starting From</span>
                        @if($priceFrom)
                            <span class="td-pricing-hero__amount">${{ number_format((float) $priceFrom, 0) }}</span>
                        @else
                            <span class="td-pricing-hero__amount td-pricing-hero__amount--request">On Request</span>
                        @endif
                        <span class="td-pricing-hero__unit">per person ({{ $priceCurrency }})</span>
                    </div>

                    <div class="td-pricing-hero__details">
                        @if($tour->duration_days)
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>{{ $tour->duration_days }} Days{{ $tour->duration_nights ? ' / ' . $tour->duration_nights . ' Nights' : '' }}</span>
                            </div>
                        @endif
                        @if($tour->tour_level)
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l2.4 5 5.6.8-4 3.9 1 5.5-5-2.6-5 2.6 1-5.5-4-3.9L9.6 8z"/></svg>
                                <span>{{ $tourLevelLabel }} Level</span>
                            </div>
                        @endif
                        @if($tour->is_group_departure)
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.4"/><path d="M15.5 14.6c2.9.4 5 2.7 5 5.4"/></svg>
                                <span>Group Departure</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Phase 3: new-system price matrix (level × season × group size) --}}
                @if($priceMatrix)
                    <div class="td-rates">
                        @foreach($priceMatrix['seasons'] as $seasonCode => $seasonLabel)
                            <div class="td-rate-block">
                                <h3 class="td-rate-season">{{ $seasonLabel }} — Price per Person</h3>
                                <div class="td-tablewrap">
                                    <table class="td-table">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ $priceMatrix['duration'] === 'single_day' ? 'Tour' : 'Package Level' }}</th>
                                                @foreach($priceMatrix['sizes'] as $size)
                                                    <th scope="col">{{ $size }} persons</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($priceMatrix['levels'] as $level)
                                                <tr>
                                                    <th scope="row"><span class="td-tierbadge td-tierbadge--{{ strtolower($level['key']) }}">{{ $level['name'] }}</span></th>
                                                    @foreach($priceMatrix['sizes'] as $size)
                                                        @php $cell = $priceMatrix['cells'][$seasonCode][$level['key']] ?? null; @endphp
                                                        <td>
                                                            @if($cell)
                                                                ${{ number_format((float) $cell['pp'][$size], 0) }}
                                                                <small>pp · group ${{ number_format((float) $cell['total'][$size], 0) }}</small>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                        <p class="td-footnote">Automatic prices are shown for groups of 2, 4 or 6 travelers. Other group sizes or missing combinations are quoted on request — use the quote form below to request a custom price.</p>
                    </div>
                @endif

                {{-- Preserved legacy base-rates table (legacy tours display unchanged) --}}
                @if($seasonPricing->isNotEmpty() && !$priceMatrix)
                    <div class="td-rates">
                        <div class="td-rate-block">
                            <h3 class="td-rate-season">Package Base Rates</h3>
                            <div class="td-tablewrap">
                                <table class="td-table">
                                    <thead>
                                        <tr><th scope="col">Type of tour</th><th scope="col">2 persons</th><th scope="col">4 persons</th><th scope="col">6 persons</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($seasonPricing as $tier)
                                            <tr>
                                                <th scope="row"><span class="td-tierbadge td-tierbadge--{{ strtolower($tier['season']) }}">{{ ucfirst(strtolower($tier['season'])) }}</span></th>
                                                @foreach(['price_2p', 'price_4p', 'price_6p'] as $priceKey)
                                                    <td>${{ number_format((float) $tier[$priceKey], 0) }} <small>USD*</small></td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="td-footnote">Final rates may vary between High Season and Low Wet Season. Select your travel date or request a quotation for the applicable seasonal rate.</p>
                    </div>
                @endif

                {{-- Phase 3: no configured price → request a package price --}}
                @if($isPriceOnRequest)
                    <div class="td-rates">
                        <div class="td-rate-block">
                            <h3 class="td-rate-season">This tour's package price is not listed</h3>
                            <p>Request a Package Price using the quote form below and we'll prepare a tailored price for your travel dates and group size.</p>
                        </div>
                    </div>
                @endif

                {{-- What's included summary --}}
                @if($hasInclExcl)
                    @php $incPreview = collect($tour->inclusions ?? []); @endphp
                    <div class="td-pricing-incl">
                        <p>This tour includes: {{ $incPreview->take(4)->implode(', ') }}{{ $incPreview->count() > 4 ? ' and more' : '' }}.</p>
                        <a href="#td-inclusions" class="td-pricing-incl__link td-js-scroll" data-target="td-inclusions">See full inclusions ›</a>
                    </div>
                @endif

                {{-- CTA --}}
                <div class="td-pricing-cta">
                    <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                        Get a Free Quote <span aria-hidden="true">›</span>
                    </button>
                    <p>Personalised proposal within 24 hours — no booking fees.</p>
                </div>
            </section>
        </div>
    <?php endif; /* /hasTourPrice */ ?>

    {{-- ══ INCLUSIONS (tab target) ══════════════════════════════════ --}}
    @if($hasInclExcl)
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-inclusions" data-td-section="inclusions">
                <h2 class="td-heading">What is Included?<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class=" td-inc-grid">
                    @if(count($tour->inclusions ?? []))
                        <div>
                            <h3 class="td-inc-title td-inc-title--in">Included</h3>
                            <ul class="td-inc-list">
                                @foreach($tour->inclusions as $inc)
                                    <li>
                                        <svg class="td-mark td-mark--yes" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span>{{ $inc }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(count($tour->exclusions ?? []))
                        <div>
                            <h3 class="td-inc-title td-inc-title--ex">Excluded</h3>
                            <ul class="td-inc-list">
                                @foreach($tour->exclusions as $exc)
                                    <li>
                                        <svg class="td-mark td-mark--no" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>
                                        <span>{{ $exc }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- ══ GETTING THERE (tab target) ═══════════════════════════════ --}}
    @if($hasGetting)
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-getting" data-td-section="getting">
                <h2 class="td-heading">Getting There<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class="td-getting">
                    <ul class="td-getting__facts">
                        @if(trim((string) $tour->starting_point) !== '')
                            <li><strong>Starting point</strong><span>{{ $tour->starting_point }}</span></li>
                        @endif
                        @if(trim((string) $tour->ending_point) !== '')
                            <li><strong>End point</strong><span>{{ $tour->ending_point }}</span></li>
                        @endif
                        @foreach($transportRows as $row)
                            <li><strong>{{ rtrim($row['label'], ':') }}</strong><span>{{ $row['value'] }}</span></li>
                        @endforeach
                        @if($safariCarImages->isNotEmpty())
                            <li><strong>Transportation</strong><span>Game-drive vehicle</span></li>
                        @endif
                    </ul>
                    @if($mapEmbedUrl)
                        <div class="td-getting__map">
                            <iframe src="{{ $mapEmbedUrl }}" title="Meeting point map" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- ══ OFFERED BY (tab target) ══════════════════════════════════ --}}
    <div class="td-container">
        <section class="td-section td-white-card td-panel" id="td-offeredby" data-td-section="offeredby">
            <h2 class="td-heading">Offered By<span class="td-heading__line" aria-hidden="true"></span></h2>

            <div class="td-offered">
                <div class="td-offered__id">
                    <img class="td-offered__logo" src="{{ $operator['logo'] }}" alt="{{ $operator['name'] }} logo" loading="lazy">
                    <div>
                        <h3>{{ $operator['name'] }}</h3>
                        <div class="td-operator__rating">
                            {!! $renderStars($siteRatingAvg) !!}
                            @if($siteRatingAvg)
                                <span class="td-hero__score">{{ number_format((float) $siteRatingAvg, 1) }}</span>
                            @endif
                            <span class="td-muted">({{ $siteReviewCount }} review{{ $siteReviewCount === 1 ? '' : 's' }})</span>
                        </div>
                        <ul class="td-operator__meta">
                            @if($countryFlag)
                                <li><span class="td-flag" aria-hidden="true">{{ $countryFlag }}</span> {{ $countryName }}</li>
                            @endif
                            @if($operator['founded'])<li>Founded {{ $operator['founded'] }}</li>@endif
                            @if($operator['employees'])<li>{{ $operator['employees'] }} employees</li>@endif
                        </ul>
                        <div class="td-offered__actions">
                            <button type="button" class="td-btn td-btn--quote td-js-quote-jump">Get a Free Quote ›</button>
                            @if($operator['phone'])
                                <a class="td-btn td-btn--ghost" href="tel:{{ preg_replace('/\s+/', '', $operator['phone']) }}">Call {{ $operator['phone'] }}</a>
                            @endif
                            @if($operator['email'])
                                <a class="td-btn td-btn--ghost" href="mailto:{{ $operator['email'] }}">Email</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($relatedTours->isNotEmpty())
                    <h4 class="td-offered__subtitle">More tours from this operator</h4>
                    <div class="td-offered__tours">
                        @foreach($relatedTours->take(3) as $other)
                            <a class="td-minicard" href="{{ route('tour.show', $other->slug) }}">
                                <img src="{{ $other->cardImageUrl('thumb-webp') }}" alt="{{ $other->cardTitle() }}" loading="lazy">
                                <span>
                                    <strong>{{ $other->cardTitle() }}</strong>
                                    @php $miniPrice = $relatedFromPrices[$other->id] ?? null; @endphp
                                    <em>{{ $other->duration_days }} days · @if($miniPrice)${{ number_format($miniPrice['amount'], 0) }} pp@else price on request @endif</em>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- ══ QUOTE FORM (existing booking process preserved) ══════════ --}}
    <div class="td-container">
        <section class="td-section td-white-card" id="td-quote">
            <h2 class="td-heading">Request a Free Quote<span class="td-heading__line" aria-hidden="true"></span></h2>

            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any() && old('tour_package_id') == $tour->id)
                <div class="alert alert-danger" role="alert">
                    <strong>Please check the following:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('inquiries.store') }}" method="POST" id="tour-inquiry-form" class="td-form">
                @csrf
                <input type="hidden" name="tour_package_id" value="{{ $tour->id }}">

                <div class="td-form__grid">
                    <div class="td-form__field">
                        <label class="form-label fw-medium">Who are you travelling with?</label>
                        <div class="td-radio-row">
                            @foreach(['Honeymoon', 'Couple', 'Family', 'Group of friends', 'Solo', 'Other'] as $companionOption)
                                <label class="td-radio"><input type="radio" name="companions" value="{{ $companionOption }}"> {{ $companionOption }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdFormTravelDate">When do you want to travel?</label>
                        <input type="date" id="tdFormTravelDate" name="travel_date" class="form-control form-control-lg" value="{{ old('travel_date') }}">
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Accommodation preference</label>
                        <div class="td-radio-row">
                            @foreach(['SILVER' => 'Silver', 'GOLD' => 'Gold', 'PLATINUM' => 'Platinum'] as $accValue => $accLabel)
                                <label class="td-radio"><input type="radio" name="accommodation" value="{{ $accValue }}"> {{ $accLabel }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Type of Room</label>
                        <div class="td-radio-row">
                            @foreach(['Single', 'Double', 'Twin', 'Triple', 'Family', 'Suite'] as $roomOption)
                                <label class="td-radio"><input type="radio" name="room_type" value="{{ $roomOption }}"> {{ $roomOption }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Type of Beds</label>
                        <div class="td-radio-row">
                            @foreach(['King' => 'King', 'Queen' => 'Queen', 'Twin' => 'Twin Beds', 'Double' => 'Double Bed', 'Any' => 'Any'] as $bedValue => $bedLabel)
                                <label class="td-radio"><input type="radio" name="bed_type" value="{{ $bedValue }}"> {{ $bedLabel }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Budget range per person (USD)</label>
                        <div class="td-form__two">
                            <input type="number" name="budget_min" class="form-control form-control-lg" placeholder="Minimum" min="0" required value="{{ old('budget_min') }}">
                            <input type="number" name="budget_max" class="form-control form-control-lg" placeholder="Maximum" min="0" required value="{{ old('budget_max') }}">
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Travellers' Age</label>
                        <div class="td-form__two">
                            <select name="adult_age" class="form-control form-control-lg" aria-label="Adult age range">
                                <option value="">Adults (18+)</option>
                                <option value="18-30">18–30</option>
                                <option value="31-50">31–50</option>
                                <option value="51+">51+</option>
                            </select>
                            <select name="children_age" class="form-control form-control-lg" aria-label="Children age range">
                                <option value="">Children (0–17)</option>
                                <option value="0-5">0–5</option>
                                <option value="6-12">6–12</option>
                                <option value="13-17">13–17</option>
                            </select>
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">How many people are travelling?</label>
                        <div class="td-form__two">
                            <input type="number" id="tdFormAdults" name="adults" class="form-control form-control-lg" min="1" value="{{ old('adults', 2) }}" aria-label="Adults">
                            <input type="number" id="tdFormChildren" name="children" class="form-control form-control-lg" min="0" value="{{ old('children', 0) }}" aria-label="Children">
                        </div>
                    </div>

                    @if($priceMatrix && count($priceMatrix['levels']) > 1)
                        <div class="td-form__field">
                            <label class="form-label fw-medium" for="tdFormLevel">Package Level</label>
                            <select id="tdFormLevel" name="package_level" class="form-control form-control-lg">
                                @foreach($priceMatrix['levels'] as $lvl)
                                    <option value="{{ $lvl['key'] }}" @selected(old('package_level', $defaultLevel ?? $priceMatrix['levels'][0]['key']) === $lvl['key'])>{{ $lvl['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="td-form__field td-form__field--wide">
                        <label class="form-label fw-medium" for="tdFormMessage">Anything else you'd like to share with us?</label>
                        <textarea id="tdFormMessage" name="message" class="form-control form-control-lg" rows="4">{{ old('message') }}</textarea>
                    </div>
                </div>

                {{-- Phase 3: live server-side price line (see price-lookup script) --}}
                <div class="td-quote-lookup" id="tdQuoteLookup" hidden></div>

                <div class="td-form__contact">
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdFirstName">First Name *</label>
                        <input type="text" id="tdFirstName" name="first_name" class="form-control form-control-lg" required value="{{ old('first_name') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdLastName">Last Name *</label>
                        <input type="text" id="tdLastName" name="last_name" class="form-control form-control-lg" required value="{{ old('last_name') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdEmail">Email Address *</label>
                        <input type="email" id="tdEmail" name="email" class="form-control form-control-lg" required value="{{ old('email') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdCountry">Country *</label>
                        <select id="tdCountry" name="country" class="form-control form-control-lg" required>
                            <option value="">Select Country</option>
                            @foreach(['Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo','Costa Rica','Croatia','Cuba','Cyprus','Czechia','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini','Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada','Guatemala','Guinea','Guyana','Haiti','Honduras','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati','Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius','Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar','Namibia','Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan','Palau','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan','Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe'] as $countryOption)
                                <option value="{{ $countryOption }}"{{ old('country') === $countryOption ? ' selected' : '' }}>{{ $countryOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdPhone">Phone Number *</label>
                        <input type="tel" id="tdPhone" name="phone" class="form-control form-control-lg" required value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="mt-3">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    @error('g-recaptcha-response')
                        <div class="text-warning mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="td-btn td-btn--quote td-btn--xl mt-4">Submit Travel Proposal</button>
            </form>
        </section>
    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    {{-- ══ FAQ ══ --}}
    @include('frontend.partials.faq-section', ['faqSubject' => $faqSubject])

    {{-- ══ 18. RELATED TOURS ════════════════════════════════════════ --}}
    @if($relatedTours->isNotEmpty())
        <section class="td-related" aria-labelledby="td-related-title">
            <div class="td-container">
                <h2 class="td-heading td-heading--light" id="td-related-title">Related Tours<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class="td-related__grid">
                    @foreach($relatedTours as $relatedTour)
                        <article class="td-tour-card">
                            <a class="td-tour-card__media" href="{{ route('tour.show', $relatedTour->slug) }}">
                                <img src="{{ $relatedTour->cardImageUrl('medium-webp') }}" alt="{{ $relatedTour->cardTitle() }}" loading="lazy">
                                <span class="td-tour-card__shade" aria-hidden="true"></span>
                                <h3>{{ $relatedTour->cardTitle() }}</h3>
                            </a>

                            <button type="button" class="td-wishlist td-wishlist--card" data-sfb-wishlist-tour="{{ $relatedTour->id }}"
                                    aria-label="Save {{ $relatedTour->cardTitle() }} to your wishlist">
                                <svg class="td-wishlist__heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.9-10-9.5C.4 8 2 4.5 5.5 4.2 7.6 4 9.3 5 12 7.6 14.7 5 16.4 4 18.5 4.2 22 4.5 23.6 8 22 11.5 19.5 16.1 12 21 12 21z"/></svg>
                                <span class="td-wishlist__count" data-sfb-wish-count hidden>0</span>
                            </button>

                            <div class="td-tour-card__body">
                                @php $cardPrice = $relatedFromPrices[$relatedTour->id] ?? null; @endphp
                                <p class="td-tour-card__price">
                                    @if($cardPrice)
                                        <strong>${{ number_format($cardPrice['amount'], 0) }}</strong>
                                        <span>pp {{ $cardPrice['currency'] }}</span>
                                    @else
                                        <strong>Quote Request</strong>
                                        <span>price on request</span>
                                    @endif
                                </p>
                                <ul class="td-tour-card__meta">
                                    @if($countryName)<li>{{ $countryFlag }} {{ $countryName }}</li>@endif
                                    @if($relatedTour->duration_days)<li>{{ $relatedTour->duration_days }} days</li>@endif
                                    @if($relatedTour->categories->first())<li>{{ $relatedTour->categories->first()->name }}</li>@endif
                                    @if($tourLevelLabel = ($levelLabels[strtolower((string) $relatedTour->tour_level)] ?? null))<li>{{ $tourLevelLabel }}</li>@endif
                                    @php $places = $relatedTour->destinations->take(2)->pluck('name'); @endphp
                                    @if($places->isNotEmpty())<li>Places: {{ $places->implode(', ') }}</li>@endif
                                </ul>
                                <div class="td-tour-card__op">
                                    <img src="{{ $operator['logo'] }}" alt="" loading="lazy">
                                    <span>{{ $operator['name'] }}</span>
                                    {!! $renderStars($siteRatingAvg, 'td-stars--sm') !!}
                                    @if($siteRatingAvg)
                                        <span class="td-hero__score">{{ number_format((float) $siteRatingAvg, 1) }}</span>
                                        <span class="td-muted">({{ $siteReviewCount }})</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ══ 10. LIGHTBOX (shared: accommodation galleries, tour gallery, map) ══ --}}
    <div class="td-lightbox" id="tdLightbox" role="dialog" aria-modal="true" aria-label="Image gallery" hidden>
        <header class="td-lightbox__head">
            <span class="td-lightbox__title" data-lb-title></span>
            <span class="td-lightbox__counter" data-lb-counter></span>
            <button type="button" class="td-lightbox__close" data-lb-close aria-label="Close gallery">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>
            </button>
        </header>

        <button type="button" class="td-lightbox__arrow td-lightbox__arrow--prev" data-lb-prev aria-label="Previous image">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
        </button>

        <figure class="td-lightbox__stage" data-lb-stage>
            <img src="" alt="" data-lb-img>
            <figcaption data-lb-caption></figcaption>
        </figure>

        <div class="td-lightbox__map" data-lb-map hidden>
            <iframe src="" title="Enlarged map" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <button type="button" class="td-lightbox__arrow td-lightbox__arrow--next" data-lb-next aria-label="Next image">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
        </button>
    </div>

    <script>window.TD_GALLERY = @json($tdGallery);</script>

    {{-- Shared SafariBookings-style calendar + travellers popovers --}}
    @include('frontend.partials.safari-popovers')

    {{-- Page behaviour: sticky tabs, scrollspy, accordion, carousel, lightbox, wishlist --}}
    <script>
    (function () {
        'use strict';

        var HEADER_OFFSET = 96; // refined below after measuring the real header

        /* Measure the actual sticky height of the site header */
        var headerEl = document.querySelector('.main-header');
        var tabsbar = document.getElementById('td-tabsbar');
        function measureHeader() {
            var h = 0;
            document.querySelectorAll('.main-header > *').forEach(function (el) {
                var cs = getComputedStyle(el);
                if (cs.position !== 'sticky' && cs.position !== 'fixed') return;
                h += el.offsetHeight;
            });
            if (!h && headerEl) {
                // Fallback: distance from viewport top to the nav bottom while scrolled
                var rect = headerEl.getBoundingClientRect();
                h = Math.max(rect.bottom, 72);
            }
            HEADER_OFFSET = Math.round(h) + 58; // header + tab bar
            document.documentElement.style.setProperty('--td-sticky-top', Math.round(h) + 'px');
        }
        measureHeader();
        window.addEventListener('resize', measureHeader);

        /* ── Smooth scrolling for tabs, hero link, quote jumps ───── */
        function scrollToId(id) {
            var el = document.getElementById(id);
            if (!el) return;
            var top = el.getBoundingClientRect().top + window.scrollY - HEADER_OFFSET - 14;
            window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
        }

        document.querySelectorAll('.td-js-scroll').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                scrollToId(link.dataset.target);
            });
        });

        document.querySelectorAll('[data-td-tab]').forEach(function (tab) {
            tab.addEventListener('click', function (event) {
                event.preventDefault();
                var tabId = tab.getAttribute('data-td-tab');
                activateTab(tabId);
                scrollToId('td-' + tabId);
            });
        });

        function activateTab(id) {
            document.querySelectorAll('.td-tab').forEach(function (t) {
                t.classList.toggle('is-active', t.getAttribute('data-td-tab') === id);
            });
        }

        /* ── Sticky tab bar: add shadow state once stuck ─────────── */
        if (tabsbar) {
            var sentinel = document.createElement('div');
            sentinel.style.cssText = 'position:absolute;width:1px;height:1px;';
            tabsbar.parentNode.insertBefore(sentinel, tabsbar);
            var observer = new IntersectionObserver(function (entries) {
                tabsbar.classList.toggle('is-stuck', !entries[0].isIntersecting);
            }, { threshold: 0 });
            observer.observe(sentinel);
        }

        /* ── Scrollspy: highlight the visible section's tab ──────── */
        var spySections = Array.prototype.slice.call(document.querySelectorAll('[data-td-section]'));
        var spyTick = false;
        function runSpy() {
            spyTick = false;
            var line = window.scrollY + HEADER_OFFSET + 40;
            var current = spySections[0];
            spySections.forEach(function (section) {
                if (section.offsetTop <= line) current = section;
            });
            if (current) activateTab(current.dataset.tdTdSection);
        }
        window.addEventListener('scroll', function () {
            if (!spyTick) { spyTick = true; window.requestAnimationFrame(runSpy); }
        }, { passive: true });
        runSpy();

        /* ── Quote jump: validate sidebar fields, prefill, scroll ── */
        document.querySelectorAll('.td-js-quote-jump').forEach(function (button) {
            button.addEventListener('click', function () {
                var dateInput = document.getElementById('tdStartDate');
                var errBox = document.getElementById('tdQuoteError');

                if (errBox) errBox.hidden = true;

                var iso = dateInput ? dateInput.value.trim() : '';
                if (!iso) {
                    if (errBox) errBox.hidden = false;
                    var trigger = document.querySelector('[data-safpop="date"]');
                    if (trigger) trigger.click(); // open the calendar
                    return;
                }

                var adults = parseInt((document.getElementById('tdTravAdults') || {}).value, 10) || 2;
                var children = parseInt((document.getElementById('tdTravChildren') || {}).value, 10) || 0;

                var formDate = document.getElementById('tdFormTravelDate');
                var formAdults = document.getElementById('tdFormAdults');
                var formChildren = document.getElementById('tdFormChildren');
                var message = document.getElementById('tdFormMessage');

                if (formDate) formDate.value = iso;
                if (formAdults) formAdults.value = String(adults);
                if (formChildren) formChildren.value = String(children);
                if (message && !message.value.trim()) {
                    message.value = 'Hello! We are interested in "' + document.title.split('|')[0].trim()
                        + '" starting ' + iso + ' for ' + (adults + children) + ' traveler(s).';
                }
                if (window.TD_LOOKUP_REFRESH) window.TD_LOOKUP_REFRESH();

                scrollToId('td-quote');
                var firstName = document.getElementById('tdFirstName');
                window.setTimeout(function () { if (firstName) firstName.focus({ preventScroll: true }); }, 650);
            });
        });

        /* Session flash: land the user on the quote form after reload */
        @if(session('success') || ($errors->any() && old('tour_package_id') == $tour->id))
            window.setTimeout(function () { scrollToId('td-quote'); }, 150);
        @endif

        /* ── Day-by-day accordion ────────────────────────────────── */
        document.querySelectorAll('[data-td-day-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var item = toggle.closest('[data-td-day]');
                var body = item.querySelector('[data-td-day-body]');
                var isOpen = item.classList.contains('is-open');
                item.classList.toggle('is-open', !isOpen);
                toggle.setAttribute('aria-expanded', String(!isOpen));
                if (!isOpen) body.style.maxHeight = body.scrollHeight + 'px';
                else body.style.maxHeight = '';
            });
        });
        document.querySelectorAll('[data-td-day].is-open [data-td-day-body]').forEach(function (body) {
            body.style.maxHeight = body.scrollHeight + 'px';
        });

        /* ── Reviews carousel ────────────────────────────────────── */
        var reviewViewport = document.querySelector('[data-td-review-viewport]');
        if (reviewViewport) {
            var slides = Array.prototype.slice.call(reviewViewport.querySelectorAll('[data-td-review]'));
            var currentOut = document.querySelector('[data-td-review-current]');
            var idx = 0;

            function paintReviews() {
                slides.forEach(function (slide, i) {
                    slide.classList.toggle('is-active', i === idx);
                });
                if (currentOut) currentOut.textContent = String(idx + 1);
            }
            function move(step) {
                idx = (idx + step + slides.length) % slides.length;
                paintReviews();
            }

            var prevBtn = document.querySelector('[data-td-review-prev]');
            var nextBtn = document.querySelector('[data-td-review-next]');
            if (prevBtn) prevBtn.addEventListener('click', function () { move(-1); });
            if (nextBtn) nextBtn.addEventListener('click', function () { move(1); });

            slides.forEach(function (slide) {
                var toggle = slide.querySelector('[data-td-toggle]');
                if (!toggle) return;
                toggle.addEventListener('click', function () {
                    var expanded = toggle.getAttribute('aria-expanded') === 'true';
                    slide.querySelector('[data-td-preview]').hidden = !expanded;
                    slide.querySelector('[data-td-full]').hidden = expanded;
                    toggle.setAttribute('aria-expanded', String(!expanded));
                    toggle.textContent = expanded ? 'Full Review' : 'Show less';
                });
            });

            paintReviews();
        }

        /* ── Wishlist (localStorage persisted + counter bubbles) ─── */
        var WISH_KEY = 'sfbWishTours';
        function readWish() {
            try { return JSON.parse(localStorage.getItem(WISH_KEY) || '[]'); } catch (e) { return []; }
        }
        function writeWish(list) {
            try { localStorage.setItem(WISH_KEY, JSON.stringify(list)); } catch (e) {}
        }
        function paintWishlist() {
            var saved = readWish();
            document.querySelectorAll('[data-sfb-wishlist-tour]').forEach(function (button) {
                var id = String(button.dataset.sfbWishlistTour);
                var isSaved = saved.indexOf(id) !== -1;
                button.classList.toggle('is-active', isSaved);
                var bubble = button.querySelector('[data-sfb-wish-count]');
                if (bubble) {
                    bubble.hidden = saved.length === 0;
                    bubble.textContent = String(saved.length);
                }
            });
        }
        document.querySelectorAll('[data-sfb-wishlist-tour]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                var id = String(button.dataset.sfbWishlistTour);
                var saved = readWish();
                var pos = saved.indexOf(id);
                if (pos === -1) saved.push(id); else saved.splice(pos, 1);
                writeWish(saved);
                paintWishlist();
            });
        });
        paintWishlist();

        /* ── Lightbox ────────────────────────────────────────────── */
        var GALLERY = window.TD_GALLERY || {};
        var lb = document.getElementById('tdLightbox');
        if (lb) {
            var lbImg = lb.querySelector('[data-lb-img]');
            var lbCaption = lb.querySelector('[data-lb-caption]');
            var lbTitle = lb.querySelector('[data-lb-title]');
            var lbCounter = lb.querySelector('[data-lb-counter]');
            var lbStage = lb.querySelector('[data-lb-stage]');
            var lbMap = lb.querySelector('[data-lb-map]');
            var lbPrev = lb.querySelector('[data-lb-prev]');
            var lbNext = lb.querySelector('[data-lb-next]');
            var groupItems = [];
            var groupIndex = 0;
            var lastFocus = null;

            function paintLightbox() {
                if (lbMap.hidden) {
                    var item = groupItems[groupIndex] || { u: '', c: '' };
                    lbImg.src = item.u;
                    lbImg.alt = item.c || '';
                    lbCaption.textContent = item.c || '';
                    lbCounter.textContent = groupItems.length > 1
                        ? (groupIndex + 1) + ' / ' + groupItems.length : '';
                }
            }
            function openLightbox(group, index) {
                lastFocus = document.activeElement;
                groupItems = GALLERY[group] || [];
                if (!groupItems.length) return;
                groupIndex = Math.max(0, Math.min(index || 0, groupItems.length - 1));
                lbMap.hidden = true;
                lbStage.hidden = false;
                lbPrev.style.display = '';
                lbNext.style.display = '';
                lbTitle.textContent = (groupItems[0] && groupItems[0].c ? groupItems[0].c.split('·')[0] : '') || 'Gallery';
                paintLightbox();
                lb.hidden = false;
                document.body.classList.add('td-lock');
                (lb.querySelector('[data-lb-close]')).focus();
            }
            function openMap(url) {
                lastFocus = document.activeElement;
                lbStage.hidden = true;
                lbMap.hidden = false;
                lbMap.querySelector('iframe').src = url;
                lbPrev.style.display = 'none';
                lbNext.style.display = 'none';
                lbCounter.textContent = '';
                lbTitle.textContent = 'Map';
                lb.hidden = false;
                document.body.classList.add('td-lock');
                (lb.querySelector('[data-lb-close]')).focus();
            }
            function closeLightbox() {
                if (lb.hidden) return;
                lb.hidden = true;
                lbMap.querySelector('iframe').src = '';
                document.body.classList.remove('td-lock');
                if (lastFocus) { try { lastFocus.focus(); } catch (e) {} }
            }
            function step(stepDir) {
                if (!groupItems.length) return;
                groupIndex = (groupIndex + stepDir + groupItems.length) % groupItems.length;
                paintLightbox();
            }

            document.querySelectorAll('.td-js-lightbox').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    openLightbox(trigger.dataset.group, parseInt(trigger.dataset.index, 10) || 0);
                });
            });
            document.querySelectorAll('.td-js-map-open').forEach(function (trigger) {
                trigger.addEventListener('click', function () { openMap(trigger.dataset.mapUrl); });
            });

            lb.addEventListener('click', function (event) {
                if (event.target === lb) closeLightbox(); // click outside gallery
            });
            var closeBtn = lb.querySelector('[data-lb-close]');
            if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
            if (lbPrev) lbPrev.addEventListener('click', function () { step(-1); });
            if (lbNext) lbNext.addEventListener('click', function () { step(1); });

            document.addEventListener('keydown', function (event) {
                if (lb.hidden) return;
                if (event.key === 'Escape') closeLightbox();
                else if (event.key === 'ArrowLeft') step(-1);
                else if (event.key === 'ArrowRight') step(1);
            });
        }
    })();
    </script>

    {{-- Phase 3: live server-side price lookup — amounts always come back from
         the server, never computed in the browser. --}}
    <script>
    (function () {
        'use strict';
        var LOOKUP_URL = @json($priceLookupUrl);
        var box = document.getElementById('tdQuoteLookup');
        if (!LOOKUP_URL || !box) return;

        var timer = null;

        function paint(data) {
            if (!data) return;
            while (box.firstChild) box.removeChild(box.firstChild);
            box.classList.remove('td-quote-lookup--request');
            var strong;

            if (data.request_type === 'automatic') {
                box.appendChild(document.createTextNode('Estimated price for your group: '));
                strong = document.createElement('strong');
                strong.textContent = '$' + data.price_pp + ' pp';
                box.appendChild(strong);
                box.appendChild(document.createTextNode(' · group of ' + data.group_size + ': $' + data.group_total));
                var small = document.createElement('small');
                small.textContent = ' (' + data.season_label + ' · ' + data.level_name + ')';
                box.appendChild(small);
            } else if (data.request_type === 'custom') {
                box.classList.add('td-quote-lookup--request');
                strong = document.createElement('strong');
                strong.textContent = 'Custom price on request';
                box.appendChild(strong);
                box.appendChild(document.createTextNode(' — automatic prices cover groups of 2, 4 or 6 (' + data.season_label + '). We\'ll prepare a quote for your group of ' + data.group_size + '.'));
            } else {
                box.classList.add('td-quote-lookup--request');
                strong = document.createElement('strong');
                strong.textContent = 'Package price on request';
                box.appendChild(strong);
                box.appendChild(document.createTextNode(' — submit the form and we\'ll prepare a tailored quote.'));
            }

            box.hidden = false;
        }

        function refresh() {
            var adults = parseInt((document.getElementById('tdFormAdults') || {}).value, 10);
            var children = parseInt((document.getElementById('tdFormChildren') || {}).value, 10);
            var group = (isNaN(adults) ? 1 : Math.max(1, adults)) + (isNaN(children) ? 0 : children);
            var date = (document.getElementById('tdFormTravelDate') || {}).value || '';
            var level = (document.getElementById('tdFormLevel') || {}).value || '';

            var params = new URLSearchParams({ group_size: String(group) });
            if (date) params.set('date', date);
            if (level) params.set('level_key', level);

            fetch(LOOKUP_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(paint)
                .catch(function () {});
        }

        function schedule() {
            window.clearTimeout(timer);
            timer = window.setTimeout(refresh, 300);
        }

        ['tdFormTravelDate', 'tdFormAdults', 'tdFormChildren', 'tdFormLevel'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('change', schedule);
            el.addEventListener('input', schedule);
        });

        // Re-run after the quote-jump button copies the sidebar selections in.
        window.TD_LOOKUP_REFRESH = refresh;
        refresh();
    })();
    </script>

    {{-- ── Leaflet route map ────────────────────────────────────────────── --}}
    @if(count($routeMapDays) > 0)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        (function () {
            var el = document.getElementById('td-leaflet-route');
            if (!el || typeof L === 'undefined') return;
            var days = JSON.parse(el.getAttribute('data-route') || '[]');
            if (!days.length) return;

            var map = L.map(el, { scrollWheelZoom: false }).setView([-6.3690, 34.8888], 6);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var bounds = L.latLngBounds();
            var markers = [];
            days.forEach(function (d) {
                var latlng = L.latLng(d.lat, d.lng);
                bounds.extend(latlng);
                var marker = L.marker(latlng).addTo(map)
                    .bindPopup('<strong>Day ' + d.day + '</strong><br>' + d.title + (d.location_name ? '<br><em>' + d.location_name + '</em>' : ''));
                marker._tdDay = d.day;
                markers.push(marker);
            });

            if (days.length === 1) {
                map.setView(bounds.getCenter(), 12);
            } else {
                map.fitBounds(bounds, { padding: [40, 40] });
            }

            if (markers.length >= 2) {
                var coords = markers.map(function (m) { return m.getLatLng(); });
                L.polyline(coords, { color: '#c13d31', weight: 3, opacity: 0.8, dashArray: '8 6' }).addTo(map);
            }

            /* ── Itinerary ↔ map interaction ──────────────────────────── */
            document.querySelectorAll('[data-td-day-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var item = toggle.closest('[data-td-day]');
                    if (!item) return;
                    var idx = parseInt(item.getAttribute('data-td-day-idx'), 10);
                    if (!idx) return;
                    var m = markers.find(function (mk) { return mk._tdDay === idx; });
                    if (m && item.classList.contains('is-open')) {
                        setTimeout(function () {
                            map.flyTo(m.getLatLng(), Math.max(map.getZoom(), 11), { duration: 0.6 });
                            m.openPopup();
                        }, 200);
                    }
                });
            });

            markers.forEach(function (m) {
                m.on('click', function () {
                    var idx = m._tdDay;
                    document.querySelectorAll('[data-td-day]').forEach(function (dayEl) {
                        if (parseInt(dayEl.getAttribute('data-td-day-idx'), 10) !== idx) return;
                        var btn = dayEl.querySelector('[data-td-day-toggle]');
                        var body = dayEl.querySelector('[data-td-day-body]');
                        if (!btn || !body) return;
                        if (!dayEl.classList.contains('is-open')) {
                            dayEl.classList.add('is-open');
                            btn.setAttribute('aria-expanded', 'true');
                            body.style.maxHeight = body.scrollHeight + 'px';
                        }
                        setTimeout(function () {
                            dayEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 150);
                    });
                });
            });
        })();
        </script>
    @endif

    {{-- FAQ accordion (only when the FAQ section is present) --}}
    @if(count($faqs ?? []))
        <script>
        (function () {
            var root = document.querySelector('[data-sfb-faq]');
            if (!root) return;
            var items = Array.prototype.slice.call(root.querySelectorAll('[data-sfb-faq-item]'));
            var links = Array.prototype.slice.call(root.querySelectorAll('[data-sfb-faq-link]'));

            function closeItems() {
                items.forEach(function (item) {
                    item.classList.remove('is-open');
                    var head = item.querySelector('.sfb-faq-item__head');
                    var body = item.querySelector('.sfb-faq-item__body');
                    if (head) head.setAttribute('aria-expanded', 'false');
                    if (body) body.style.maxHeight = '';
                });
            }

            function openItem(item) {
                item.classList.add('is-open');
                var head = item.querySelector('.sfb-faq-item__head');
                var body = item.querySelector('.sfb-faq-item__body');
                if (head) head.setAttribute('aria-expanded', 'true');
                if (body) body.style.maxHeight = body.scrollHeight + 'px';
            }

            items.forEach(function (item, i) {
                var head = item.querySelector('.sfb-faq-item__head');
                if (!head) return;
                head.addEventListener('click', function () {
                    var isOpen = item.classList.contains('is-open');
                    closeItems();
                    if (!isOpen) openItem(item);
                });
            });

            links.forEach(function (link, i) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeItems();
                    var target = items[i];
                    if (!target) return;
                    openItem(target);
                    setTimeout(function () {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 80);
                });
            });
        })();
        </script>
    @endif
@endsection
