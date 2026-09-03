@extends('frontend.layouts.app')
@section('page-content')

@php
use App\Models\Setting;
use App\Models\Testimonial;

$countryNames = [
    'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
    'ZA' => 'South Africa', 'BW' => 'Botswana', 'ZW' => 'Zimbabwe', 'ZM' => 'Zambia',
];
$flagFor = function (?string $code): string {
    if (! $code || strlen($code) !== 2) { return ''; }
    $code = strtoupper($code);
    return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
};

$countryName = $countryNames[$destination->country_code] ?? '';
$countryFlag = $flagFor($destination->country_code);

$heroUrl = $destination->hasHeroImage()
    ? ($destination->heroUrl('large-webp') ?: $destination->heroUrl())
    : asset('front-end/html/assets/img/placeholder-destination.jpg');

$galleryImages = $destination->galleryImages();

$lat = $destination->latitude;
$lng = $destination->longitude;
$mapEmbedUrl = ($lat && $lng)
    ? 'https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&z=7&output=embed'
    : null;

$operator = [
    'name'      => Setting::get('site_name', 'Afro-Vertex Tours & Safaris'),
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

$reviews = $reviews->map(function (Testimonial $testimonial) use ($flagFor) {
    $code = '';
    if (preg_match('/\(([A-Z]{2})\)/', (string) $testimonial->location, $m)) { $code = $m[1]; }
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

$tdGallery = [];
foreach ($galleryImages as $media) { $tdGallery['destination'][] = ['u' => $media->getUrl(), 'c' => $destination->name]; }
if ($heroUrl) { $tdGallery['destination'][] = ['u' => $heroUrl, 'c' => $destination->name]; }

$faqs       = is_array($destination->faqs) ? $destination->faqs : [];
$hasFaqs    = count($faqs) > 0;
$hasGetting = (bool) ($mapEmbedUrl || $countryName || ($lat && $lng));

$tdTabs = [['id' => 'overview', 'label' => 'Overview']];
if ($hasFaqs) { $tdTabs[] = ['id' => 'highlights', 'label' => 'Highlights']; }
$tdTabs[] = ['id' => 'getting', 'label' => 'Getting There'];
$tdTabs[] = ['id' => 'offeredby', 'label' => 'Offered By'];

$countryList = ['Afghanistan','Albania','Algeria','Argentina','Australia','Austria','Belgium','Botswana','Brazil','Canada','China','Denmark','Egypt','Finland','France','Germany','Ghana','Greece','India','Indonesia','Ireland','Italy','Japan','Kenya','Malawi','Malaysia','Mozambique','Namibia','Netherlands','New Zealand','Nigeria','Norway','Poland','Portugal','Rwanda','South Africa','Spain','Sweden','Switzerland','Tanzania','Thailand','Uganda','United Arab Emirates','United Kingdom','United States','Zambia','Zimbabwe'];

$photoCardImage = $galleryImages->first()?->getUrl('medium-webp') ?: ($galleryImages->first()?->getUrl() ?: $heroUrl);
@endphp

@section('title', $destination->name . ' | Afro-Vertex Tours & Safaris')

<div class="td-page">

    <div class="td-breadcrumb">
        <nav aria-label="Breadcrumb">
            <ol class="td-breadcrumb__list">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">&rsaquo;</li>
                <li><a href="{{ route('destinations.index') }}">Destinations</a></li>
                @if($countryName)
                    <li aria-hidden="true" class="td-breadcrumb__sep">&rsaquo;</li>
                    <li><a href="{{ route('destinations.index', ['country' => $destination->country_code]) }}">{{ $countryName }}</a></li>
                @endif
                <li aria-hidden="true" class="td-breadcrumb__sep">&rsaquo;</li>
                <li class="td-breadcrumb__current" aria-current="page">{{ $destination->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="td-hero">
        <img src="{{ $heroUrl }}" alt="{{ $destination->name }} hero photo" class="td-hero__img">
        <div class="td-hero__shade" aria-hidden="true"></div>

        <div class="td-hero__content">
            <div class="td-container">
                <h1 class="td-hero__title">{{ $destination->name }}</h1>
                <p class="td-hero__offered">Offered By: {{ $operator['name'] }}</p>
                <div class="td-hero__rating">
                    @if($countryFlag)<span class="td-flag" aria-hidden="true">{{ $countryFlag }}</span>@endif
                    <strong>{{ $countryName ?: 'East Africa' }}</strong>
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
            <nav class="td-tabs" aria-label="Destination sections">
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
                <div class="td-overview rich-text-content">
                    {!! \App\Services\RichText::sanitize($destination->description) !!}
                </div>
            </section>

            @if($hasFaqs)
                <section class="td-section td-white-card" id="td-highlights" data-td-section="highlights">
                    <h2 class="td-heading">Highlights &amp; Good to Know<span class="td-heading__line" aria-hidden="true"></span></h2>

                    <ol class="td-days">
                        @foreach($faqs as $index => $faq)
                            <li class="td-day{{ $loop->first ? ' is-open' : '' }}" data-td-day>
                                <button type="button" class="td-day__head" data-td-day-toggle"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="td-faq-body-{{ $index + 1 }}">
                                    <span class="td-day__num">{{ $index + 1 }}</span>
                                    <span class="td-day__titles">
                                        <strong>{{ $faq['question'] ?? '' }}</strong>
                                    </span>
                                    <svg class="td-day__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>

                                <div class="td-day__body" id="td-faq-body-{{ $index + 1 }}" data-td-day-body>
                                    <div class="td-day__grid">
                                        <div class="td-day__desc rich-text-content">{!! \App\Services\RichText::sanitize($faq['answer'] ?? '') !!}</div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            <section class="td-interested">
                <h2>Plan a Trip to {{ $destination->name }}</h2>
                <p>Tell us your travel dates and group size — you will receive a personalised proposal directly from {{ $operator['name'] }}, normally within 24 hours.</p>
                <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                    Plan Your Trip <span aria-hidden="true">&rsaquo;</span>
                </button>
                <ul class="td-trustpoints">
                    <li>Best price guarantee</li>
                    <li>No booking fees — request is free</li>
                    @if($operator['email'])
                        <li>Or <a href="mailto:{{ $operator['email'] }}?subject={{ rawurlencode('Destination enquiry: ' . $destination->name) }}">contact the operator directly</a></li>
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
                                            @if($review['flag'])<span class="td-flag" aria-hidden="true">{{ $review['flag'] ?? '' }}</span>@endif
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
                    <p class="td-muted">No reviews yet for this destination.</p>
                @endif
            </div>

            @if($relatedLinks->isNotEmpty())
                <div class="td-side-card td-relatedlinks">
                    <h3 class="td-side-title">Related Destinations</h3>
                    <ul>
                        @foreach($relatedLinks as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }} &rsaquo;</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($galleryImages->isNotEmpty() || $destination->hasHeroImage())
                <div class="td-side-card td-photo-card">
                    <button type="button" class="td-photo-card__imgbtn td-js-lightbox" data-group="destination" data-index="0"
                            aria-label="Open destination photo gallery">
                        <img src="{{ $photoCardImage }}" alt="{{ $destination->name }} photo" loading="lazy">
                    </button>
                    <button type="button" class="td-morelink td-js-lightbox" data-group="destination" data-index="0">
                        Open Photos ({{ $galleryImages->count() ?: 1 }})
                    </button>
                </div>
            @endif

            @if($mapEmbedUrl)
                <div class="td-side-card td-map-card">
                    <div class="td-map-card__preview">
                        <iframe src="{{ $mapEmbedUrl }}" title="{{ $destination->name }} map preview"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" tabindex="-1"></iframe>
                    </div>
                    <button type="button" class="td-morelink td-js-map-open" data-map-url="{{ $mapEmbedUrl }}">
                        {{ $destination->name }} Map &rsaquo;
                    </button>
                </div>
            @endif

        </aside>
    </div><!-- /td-layout -->
    @if($hasGetting)
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-getting" data-td-section="getting">
                <h2 class="td-heading">Getting There<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class="td-getting">
                    <ul class="td-getting__facts">
                        @if($countryName)
                            <li><strong>Country</strong><span>{{ $countryFlag }} {{ $countryName }}</span></li>
                        @endif
                        @if($lat && $lng)
                            <li><strong>Coordinates</strong><span>{{ number_format((float) $lat, 4) }}, {{ number_format((float) $lng, 4) }}</span></li>
                        @endif
                    </ul>
                    @if($mapEmbedUrl)
                        <div class="td-getting__map">
                            <iframe src="{{ $mapEmbedUrl }}" title="{{ $destination->name }} map" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif

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

                @if($relatedTours->isNotEmpty())
                    <h4 class="td-offered__subtitle">Tours in {{ $destination->name }}</h4>
                    <div class="td-offered__tours">
                        @foreach($relatedTours->take(3) as $other)
                            <a class="td-minicard" href="{{ route('tour.show', $other->slug) }}">
                                <img src="{{ $other->cardImageUrl('thumb-webp') }}" alt="{{ $other->cardTitle() }}" loading="lazy">
                                <span>
                                    <strong>{{ $other->cardTitle() }}</strong>
                                    @php $miniPrice = $fromPrices[$other->id] ?? null; @endphp
                                    <em>{{ $other->duration_days }} days &middot; @if($miniPrice)${{ number_format($miniPrice['amount'], 0) }} pp@else price on request @endif</em>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="td-container">
        <section class="td-section td-white-card" id="td-quote">
            <h2 class="td-heading">Plan Your Trip to {{ $destination->name }}<span class="td-heading__line" aria-hidden="true"></span></h2>

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

            <form action="{{ route('inquiries.store') }}" method="POST" id="destination-inquiry-form" class="td-form">
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

    @if($relatedTours->isNotEmpty())
        <section class="td-related" aria-labelledby="td-related-title">
            <div class="td-container">
                <h2 class="td-heading td-heading--light" id="td-related-title">Tours in {{ $destination->name }}<span class="td-heading__line" aria-hidden="true"></span></h2>

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
                                @php $cardPrice = $fromPrices[$relatedTour->id] ?? null; @endphp
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

                @if($relatedTours->hasPages())
                    <div class="td-related__more">
                        {{ $relatedTours->links() }}
                    </div>
                @endif
            </div>
        </section>
    @endif

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
            if (current) activateTab(current.dataset.tdTdSection);
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

        var WISH_KEY = 'sfbWishTours';
        function readWish() { try { return JSON.parse(localStorage.getItem(WISH_KEY) || '[]'); } catch (e) { return []; } }
        function writeWish(list) { try { localStorage.setItem(WISH_KEY, JSON.stringify(list)); } catch (e) {} }
        function paintWishlist() {
            var saved = readWish();
            document.querySelectorAll('[data-sfb-wishlist-tour]').forEach(function (button) {
                var id = String(button.dataset.sfbWishlistTour);
                var isSaved = saved.indexOf(id) !== -1;
                button.classList.toggle('is-active', isSaved);
                var bubble = button.querySelector('[data-sfb-wish-count]');
                if (bubble) { bubble.hidden = saved.length === 0; bubble.textContent = String(saved.length); }
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
                    lbCounter.textContent = groupItems.length > 1 ? (groupIndex + 1) + ' / ' + groupItems.length : '';
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