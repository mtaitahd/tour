@extends('frontend.layouts.app')
@section('page-content')

@php
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\GalleryImage;

$siteName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');

$heroUrl = ($page->heroUrl('large-webp') ?: $page->heroUrl())
    ?: asset('front-end/html/assets/img/placeholder-destination.jpg');

$galleryItems  = is_array($page->story_gallery) ? $page->story_gallery : [];
$galleryIds    = collect($galleryItems)->pluck('image_id')->filter()->values()->all();
$galleryImages = $galleryIds ? GalleryImage::whereIn('id', $galleryIds)->get()->keyBy('id') : collect();

$tdGallery = [];
foreach ($galleryItems as $item) {
    $img = $galleryImages[$item['image_id']] ?? null;
    if (! $img) { continue; }
    $tdGallery['page'][] = ['u' => $img->getUrl(), 'c' => $item['caption'] ?: $page->title];
}
if ($heroUrl) { $tdGallery['page'][] = ['u' => $heroUrl, 'c' => $page->title]; }

$operator = [
    'name'      => $siteName,
    'logo'      => Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp'),
    'link'      => Setting::get('faq_expert_link', route('home')),
    'location'  => Setting::get('footer_address', ''),
    'founded'   => Setting::get('operator_founded_year', ''),
    'employees' => Setting::get('operator_employees', ''),
    'phone'     => Setting::get('footer_phone', ''),
    'email'     => Setting::get('site_email', ''),
];

$reviews = Testimonial::published()->general()
    ->orderByDesc('is_featured')->orderBy('order')->orderByDesc('id')->get();
$siteReviewCount = Testimonial::published()->count();
$siteRatingAvg   = $siteReviewCount ? round((float) Testimonial::published()->avg('rating'), 1) : null;
$displayRating   = $siteRatingAvg;
$displayReviews  = $siteReviewCount;

$reviews = $reviews->map(function (Testimonial $testimonial) {
    $code = '';
    if (preg_match('/\(([A-Z]{2})\)/', (string) $testimonial->location, $m)) { $code = $m[1]; }
    $flagFor = function (string $cc): string {
        $cc = strtoupper($cc);
        return mb_chr(0x1F1E6 + ord($cc[0]) - 65) . mb_chr(0x1F1E6 + ord($cc[1]) - 65);
    };
    return [
        'name'     => $testimonial->name,
        'location' => $testimonial->location,
        'flag'     => $code ? $flagFor($code) : '',
        'rating'   => (int) $testimonial->rating,
        'content'  => $testimonial->content,
        'avatar'   => $testimonial->avatarUrl('thumb-webp') ?: $testimonial->avatarUrl(),
        'initial'  => strtoupper(mb_substr($testimonial->name, 0, 1)),
    ];
});

$renderStars = function (?float $rating, string $sizeClass = '') {
    $value = max(0, min(5, (int) round((float) ($rating ?? 0))));
    $html  = '<span class="td-stars ' . $sizeClass . '" role="img" aria-label="' . number_format((float) ($rating ?? 0), 1) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<svg class="td-star' . ($i <= $value ? ' is-full' : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.83 6.1 6.67.57-5.06 4.48 1.51 6.55L12 16.85 6.05 20.3l1.51-6.55L2.5 9.27l6.67-.57z"/></svg>';
    }
    return $html . '</span>';
};

$relatedPages = \App\Models\Page::where('status', 'published')
    ->where('id', '<>', $page->id)
    ->orderBy('order')
    ->orderBy('title')
    ->limit(6)
    ->get(['id', 'title', 'slug']);

$hasGallery = count($tdGallery['page'] ?? []) > 0;
$photoCardImage = $tdGallery['page'][0]['u'] ?? $heroUrl;

$tdTabs = [['id' => 'overview', 'label' => 'Overview']];
if ($hasGallery) { $tdTabs[] = ['id' => 'gallery', 'label' => 'Gallery']; }
$tdTabs[] = ['id' => 'offeredby', 'label' => 'Offered By'];
$tdTabs[] = ['id' => 'quote', 'label' => 'Plan Your Trip'];

$countryList = ['Afghanistan','Albania','Algeria','Argentina','Australia','Austria','Belgium','Botswana','Brazil','Canada','China','Denmark','Egypt','Finland','France','Germany','Ghana','Greece','India','Indonesia','Ireland','Italy','Japan','Kenya','Malawi','Malaysia','Mozambique','Namibia','Netherlands','New Zealand','Nigeria','Norway','Poland','Portugal','Rwanda','South Africa','Spain','Sweden','Switzerland','Tanzania','Thailand','Uganda','United Arab Emirates','United Kingdom','United States','Zambia','Zimbabwe'];
@endphp

@section('title', $page->meta_title ?? ($page->title . ' | ' . $siteName))

@section('extra-head')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
@endsection

<div class="td-page">

    <div class="td-breadcrumb">
        <nav aria-label="Breadcrumb">
            <ol class="td-breadcrumb__list">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">&rsaquo;</li>
                <li class="td-breadcrumb__current" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
    </div>

    <section class="td-hero">
        <img src="{{ $heroUrl }}" alt="{{ $page->title }} hero photo" class="td-hero__img">
        <div class="td-hero__shade" aria-hidden="true"></div>

        <div class="td-hero__content">
            <div class="td-container">
                <h1 class="td-hero__title">{{ $page->title }}</h1>
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

    <div class="td-tabsbar" id="td-tabsbar">
        <div class="td-container td-tabsbar__inner">
            <nav class="td-tabs" aria-label="Page sections">
                @foreach($tdTabs as $tab)
                    <a href="#td-{{ $tab['id'] }}"
                       class="td-tab{{ $loop->first ? ' is-active' : '' }}"
                       data-td-tab="{{ $tab['id'] }}">{{ $tab['label'] }}</a>
                @endforeach
            </nav>
            <button type="button" class="td-btn td-btn--quote td-tabsbar__cta td-js-quote-jump">
                Plan Your Trip <span aria-hidden="true">&rsaquo;</span>
            </button>
        </div>
    </div>

    <div class="td-container td-layout">

        <div class="td-main">

            <section class="td-section" id="td-overview" data-td-section="overview">
                @if($page->extra_heading)
                    <h2 class="td-heading">{{ $page->extra_heading }}<span class="td-heading__line" aria-hidden="true"></span></h2>
                @endif

                @if($page->extra_subheading)
                    <p class="lead text-gray-700 mb-4">{!! $page->extra_subheading !!}</p>
                @endif

                <div class="td-overview rich-text-content">
                    {!! \App\Services\RichText::sanitize($page->content) !!}
                </div>

                @if($page->cta_text && $page->cta_link)
                    <div class="text-center mt-4 pt-4 border-top">
                        <a href="{{ $page->cta_link }}" class="td-btn td-btn--quote td-btn--xl">{{ $page->cta_text }} <span aria-hidden="true">&rsaquo;</span></a>
                    </div>
                @endif
            </section>

            @if($hasGallery)
                <section class="td-section td-white-card" id="td-gallery" data-td-section="gallery">
                    <h2 class="td-heading">Gallery<span class="td-heading__line" aria-hidden="true"></span></h2>
                    <div class="td-day__grid">
                        @foreach($tdGallery['page'] as $index => $item)
                            <button type="button" class="td-js-lightbox" data-group="page" data-index="{{ $index }}"
                                    style="border:0;padding:0;background:transparent;cursor:pointer;border-radius:12px;overflow:hidden;">
                                <img src="{{ $item['u'] }}" alt="{{ $item['c'] }}" loading="lazy"
                                     style="width:100%;height:180px;object-fit:cover;display:block;border-radius:12px;">
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="td-interested">
                <h2>Plan a Trip to {{ $page->title }}</h2>
                <p>Tell us your travel dates and group size — you will receive a personalised proposal directly from {{ $operator['name'] }}, normally within 24 hours.</p>
                <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                    Plan Your Trip <span aria-hidden="true">&rsaquo;</span>
                </button>
                <ul class="td-trustpoints">
                    <li>Best price guarantee</li>
                    <li>No booking fees — request is free</li>
                    @if($operator['email'])
                        <li>Or <a href="mailto:{{ $operator['email'] }}?subject={{ rawurlencode('Enquiry: ' . $page->title) }}">contact the operator directly</a></li>
                    @endif
                </ul>
            </section>

        </div><!-- /td-main -->

        <aside class="td-sidebar">

            <div class="td-quote-card" id="td-quote-card">
                <h2 class="td-quote-title">Plan Your Trip</h2>

                <div class="td-quote-fields">
                    <button type="button" class="td-safari-field" data-safpop="date"
                            data-safpop-target="tdStartDate" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar">
                        <span class="td-safari-field__label"></span>
                        <input type="text" placeholder="Start Date" readonly data-safpop-display aria-label="Start Date">
                        <i class="isax isax-calendar-15" aria-hidden="true"></i>
                    </button>
                    <input type="hidden" id="tdStartDate" value="">

                    <button type="button" class="td-safari-field" data-safpop="trav"
                            data-trav-total="tdTravTotal" data-trav-adults="tdTravAdults" data-trav-children="tdTravChildren"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover">
                        <span class="td-safari-field__label"></span>
                        <input type="text" placeholder="Travelers" readonly data-safpop-display aria-label="Travelers">
                        <i class="isax isax-profile-2user5" aria-hidden="true"></i>
                    </button>
                    <input type="hidden" id="tdTravTotal" value="2">
                    <input type="hidden" id="tdTravAdults" value="2">
                    <input type="hidden" id="tdTravChildren" value="0">

                    <p class="td-quote-error" id="tdQuoteError" hidden>Please choose a start date first.</p>

                    <button type="button" class="td-btn td-btn--quote td-btn--block td-js-quote-jump">
                        Plan Your Trip <span aria-hidden="true">&rsaquo;</span>
                    </button>
                </div>

                <ul class="td-checklist">
                    <li>Best price guarantee</li>
                    <li>Request sent directly to the operator</li>
                    <li>Option to contact the operator directly</li>
                </ul>
            </div>

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
                <a class="td-morelink" href="{{ $operator['link'] }}">More About This Operator &rsaquo;</a>
            </div>

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
                    <p class="td-muted">No reviews yet.</p>
                @endif
            </div>

            @if($relatedPages->isNotEmpty())
                <div class="td-side-card td-relatedlinks">
                    <h3 class="td-side-title">Related Pages</h3>
                    <ul>
                        @foreach($relatedPages as $relatedPage)
                            <li><a href="{{ route('page.show', $relatedPage->slug) }}">{{ $relatedPage->title }} &rsaquo;</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($hasGallery)
                <div class="td-side-card td-photo-card">
                    <button type="button" class="td-photo-card__imgbtn td-js-lightbox" data-group="page" data-index="0"
                            aria-label="Open photo gallery">
                        <img src="{{ $photoCardImage }}" alt="{{ $page->title }} photo" loading="lazy">
                    </button>
                    <button type="button" class="td-morelink td-js-lightbox" data-group="page" data-index="0">
                        Open Photos ({{ count($tdGallery['page']) }})
                    </button>
                </div>
            @endif

        </aside>
    </div><!-- /td-layout -->

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
                            @if($operator['location'])
                                <li>{{ $operator['location'] }}</li>
                            @endif
                            @if($operator['founded'])<li>Founded {{ $operator['founded'] }}</li>@endif
                            @if($operator['employees'])<li>{{ $operator['employees'] }} employees</li>@endif
                        </ul>
                        <div class="td-offered__actions">
                            <button type="button" class="td-btn td-btn--quote td-js-quote-jump">Plan Your Trip &rsaquo;</button>
                            @if($operator['phone'])
                                <a class="td-btn td-btn--ghost" href="tel:{{ preg_replace('/\s+/', '', $operator['phone']) }}">Call {{ $operator['phone'] }}</a>
                            @endif
                            @if($operator['email'])
                                <a class="td-btn td-btn--ghost" href="mailto:{{ $operator['email'] }}">Email</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="td-container">
        <section class="td-section td-white-card" id="td-quote">
            <h2 class="td-heading">Plan Your Trip to {{ $page->title }}<span class="td-heading__line" aria-hidden="true"></span></h2>

            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any() && !old('tour_package_id'))
                <div class="alert alert-danger" role="alert">
                    <strong>Please check the following:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('inquiries.store') }}" method="POST" id="page-inquiry-form" class="td-form">
                @csrf

                <div class="td-form__grid">
                    <div class="td-form__field">
                        <label class="form-label" for="tdFormTravelDate">When do you want to travel?</label>
                        <input type="date" id="tdFormTravelDate" name="travel_date" class="form-control form-control-lg" value="{{ old('travel_date') }}">
                    </div>

                    <div class="td-form__field">
                        <label class="form-label" for="tdFormAdults">How many people are travelling?</label>
                        <div class="td-form__two">
                            <input type="number" id="tdFormAdults" name="adults" class="form-control form-control-lg" min="1" value="{{ old('adults', 2) }}" aria-label="Adults">
                            <input type="number" id="tdFormChildren" name="children" class="form-control form-control-lg" min="0" value="{{ old('children', 0) }}" aria-label="Children">
                        </div>
                    </div>

                    <div class="td-form__field td-form__field--wide">
                        <label class="form-label" for="tdFormMessage">Anything else you'd like to share with us?</label>
                        <textarea id="tdFormMessage" name="message" class="form-control form-control-lg" rows="4">{{ old('message') }}</textarea>
                    </div>
                </div>

                <div class="td-form__contact">
                    <div class="td-form__field">
                        <label class="form-label" for="tdFirstName">First Name *</label>
                        <input type="text" id="tdFirstName" name="first_name" class="form-control form-control-lg" required value="{{ old('first_name') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label" for="tdLastName">Last Name *</label>
                        <input type="text" id="tdLastName" name="last_name" class="form-control form-control-lg" required value="{{ old('last_name') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label" for="tdEmail">Email Address *</label>
                        <input type="email" id="tdEmail" name="email" class="form-control form-control-lg" required value="{{ old('email') }}">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label" for="tdCountry">Country *</label>
                        <select id="tdCountry" name="country" class="form-control form-control-lg" required>
                            <option value="">Select Country</option>
                            @foreach($countryList as $countryOption)
                                <option value="{{ $countryOption }}"{{ old('country') === $countryOption ? ' selected' : '' }}>{{ $countryOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="td-form__field">
                        <label class="form-label" for="tdPhone">Phone Number *</label>
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

        <button type="button" class="td-lightbox__arrow td-lightbox__arrow--next" data-lb-next aria-label="Next image">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
        </button>
    </div>

    <script>window.TD_GALLERY = @json($tdGallery);</script>

    @include('frontend.partials.safari-popovers')

    <script>
    (function () {
        'use strict';

        var HEADER_OFFSET = 96;

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
                var rect = headerEl.getBoundingClientRect();
                h = Math.max(rect.bottom, 72);
            }
            HEADER_OFFSET = Math.round(h) + 58;
            document.documentElement.style.setProperty('--td-sticky-top', Math.round(h) + 'px');
        }
        measureHeader();
        window.addEventListener('resize', measureHeader);

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

        if (tabsbar) {
            var sentinel = document.createElement('div');
            sentinel.style.cssText = 'position:absolute;width:1px;height:1px;';
            tabsbar.parentNode.insertBefore(sentinel, tabsbar);
            var observer = new IntersectionObserver(function (entries) {
                tabsbar.classList.toggle('is-stuck', !entries[0].isIntersecting);
            }, { threshold: 0 });
            observer.observe(sentinel);
        }

        var spySections = Array.prototype.slice.call(document.querySelectorAll('[data-td-section]'));
        var spyTick = false;
        function runSpy() {
            spyTick = false;
            var line = window.scrollY + HEADER_OFFSET + 40;
            var current = spySections[0];
            spySections.forEach(function (section) {
                if (section.offsetTop <= line) current = section;
            });
            if (current) activateTab(current.dataset.tdSection);
        }
        window.addEventListener('scroll', function () {
            if (!spyTick) { spyTick = true; window.requestAnimationFrame(runSpy); }
        }, { passive: true });
        runSpy();

        document.querySelectorAll('.td-js-quote-jump').forEach(function (button) {
            button.addEventListener('click', function () {
                var dateInput = document.getElementById('tdStartDate');
                var errBox = document.getElementById('tdQuoteError');
                if (errBox) errBox.hidden = true;
                var iso = dateInput ? dateInput.value.trim() : '';
                if (!iso) {
                    if (errBox) errBox.hidden = false;
                    var trigger = document.querySelector('[data-safpop="date"]');
                    if (trigger) trigger.click();
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
                scrollToId('td-quote');
                var firstName = document.getElementById('tdFirstName');
                window.setTimeout(function () { if (firstName) firstName.focus({ preventScroll: true }); }, 650);
            });
        });

        @if(session('success') || ($errors->any() && !old('tour_package_id')))
            window.setTimeout(function () { scrollToId('td-quote'); }, 150);
        @endif

        var reviewViewport = document.querySelector('[data-td-review-viewport]');
        if (reviewViewport) {
            var slides = Array.prototype.slice.call(reviewViewport.querySelectorAll('[data-td-review]'));
            var currentOut = document.querySelector('[data-td-review-current]');
            var idx = 0;
            function paintReviews() {
                slides.forEach(function (slide, i) { slide.classList.toggle('is-active', i === idx); });
                if (currentOut) currentOut.textContent = String(idx + 1);
            }
            function move(step) { idx = (idx + step + slides.length) % slides.length; paintReviews(); }
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

        var GALLERY = window.TD_GALLERY || {};
        var lb = document.getElementById('tdLightbox');
        if (lb) {
            var lbImg = lb.querySelector('[data-lb-img]');
            var lbCaption = lb.querySelector('[data-lb-caption]');
            var lbTitle = lb.querySelector('[data-lb-title]');
            var lbCounter = lb.querySelector('[data-lb-counter]');
            var lbPrev = lb.querySelector('[data-lb-prev]');
            var lbNext = lb.querySelector('[data-lb-next]');
            var groupItems = [];
            var groupIndex = 0;
            var lastFocus = null;
            function paintLightbox() {
                var item = groupItems[groupIndex] || { u: '', c: '' };
                lbImg.src = item.u;
                lbImg.alt = item.c || '';
                lbCaption.textContent = item.c || '';
                lbCounter.textContent = groupItems.length > 1 ? (groupIndex + 1) + ' / ' + groupItems.length : '';
            }
            function openLightbox(group, index) {
                lastFocus = document.activeElement;
                groupItems = GALLERY[group] || [];
                if (!groupItems.length) return;
                groupIndex = Math.max(0, Math.min(index || 0, groupItems.length - 1));
                lbTitle.textContent = (groupItems[0] && groupItems[0].c ? groupItems[0].c.split('·')[0] : '') || 'Gallery';
                paintLightbox();
                lb.hidden = false;
                document.body.classList.add('td-lock');
                (lb.querySelector('[data-lb-close]')).focus();
            }
            function closeLightbox() {
                if (lb.hidden) return;
                lb.hidden = true;
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
            lb.addEventListener('click', function (event) { if (event.target === lb) closeLightbox(); });
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

</div><!-- /td-page -->
@endsection