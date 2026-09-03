@php
    use App\Models\Setting;
    use App\Models\Destination;
    use App\Models\TourPackage;
    use App\Models\TourCategory;
    use App\Models\Testimonial;
    use App\Models\Page;
    use App\Models\GalleryImage;

    $siteName   = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $logo       = Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp');

    // ---- Footer text (admin-editable via Settings) ----
    $footerAboutHeading = Setting::get('footer_about_heading') ?: 'About ' . $siteName;
    $footerAboutText    = Setting::get('footer_about_text');
    $footerAboutLink    = Setting::get('footer_about_link_text') ?: 'More About Us';
    $headingStats       = Setting::get('footer_heading_statistics') ?: 'Our Statistics';
    $headingParks       = Setting::get('footer_heading_parks') ?: 'Safaris by Park';
    $headingCountries   = Setting::get('footer_heading_countries') ?: 'Safaris by Country';
    $headingTypes       = Setting::get('footer_heading_types') ?: 'Safaris by Type';
    $headingGeneral     = Setting::get('footer_heading_general') ?: 'General';
    $headingPartners    = Setting::get('footer_heading_partners') ?: 'Our Partners';
    $emptyStats         = Setting::get('footer_empty_statistics') ?: 'Statistics coming soon.';
    $emptyParks         = Setting::get('footer_empty_parks') ?: 'Coming soon.';
    $emptyGeneral       = Setting::get('footer_empty_general') ?: 'Coming soon.';
    $copyPrivacy        = Setting::get('footer_copy_privacy') ?: 'Privacy Policy';
    $copyTerms          = Setting::get('footer_copy_terms') ?: 'Terms & Conditions';

    // ---- Statistics (real, dynamic) ----
    $stats = collect();
    $destCount  = (int) Destination::count();
    if ($destCount > 0)  $stats->push(['value' => $destCount, 'label' => 'Safari destinations']);
    $parkCount  = (int) Destination::where('type', 'National Park')->count();
    if ($parkCount > 0)  $stats->push(['value' => $parkCount, 'label' => 'Parks & reserves']);
    $tourCount  = (int) TourPackage::where('status', 'published')->count();
    if ($tourCount > 0)  $stats->push(['value' => $tourCount, 'label' => 'Tour packages']);

    $partnerLogos = collect(Setting::json('partner_logos') ?: []);
    if ($partnerLogos->count() > 0) $stats->push(['value' => $partnerLogos->count(), 'label' => 'Trusted partners']);

    try { $reviewCount = (int) Testimonial::published()->general()->count(); }
    catch (\Throwable $e) { $reviewCount = 0; }
    if ($reviewCount > 0) $stats->push(['value' => $reviewCount, 'label' => 'Customer reviews']);
    // Local safari experts: no data source available -> intentionally hidden

    // ---- About description ----
    if ($footerAboutText) {
        $aboutText = $footerAboutText;
    } else {
        $aboutText = $siteName . ' designs tailor-made African safaris across East Africa. ';
        $parts = [];
        if ($destCount) $parts[] = $destCount . ' safari destinations';
        if ($parkCount) $parts[] = $parkCount . ' national parks & reserves';
        if ($tourCount) $parts[] = $tourCount . ' curated tour packages';
        if (count($parts)) $aboutText .= 'Explore ' . implode(', ', $parts) . ' with trusted local partners.';
        else              $aboutText .= 'Travel with trusted local partners and verified experts.';
    }

    // ---- Parks ----
    $parks = Destination::whereIn('type', ['National Park', 'mountain'])->orderBy('name')->get();

    // ---- Countries (from active destinations) ----
    $countryNames = [
        'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
        'ZW' => 'Zimbabwe', 'BW' => 'Botswana', 'ZA' => 'South Africa', 'CD' => 'DR Congo',
        'BI' => 'Burundi', 'MZ' => 'Mozambique', 'SS' => 'South Sudan', 'ET' => 'Ethiopia',
    ];
    $countryCodes = Destination::distinct()->pluck('country_code')->filter()->values();
    $countries = $countryCodes
        ->map(fn($c) => ['code' => $c, 'name' => $countryNames[$c] ?? null])
        ->filter(fn($c) => !empty($c['name']));
    $halfC = ceil($countries->count() / 2);
    $countriesA = $countries->slice(0, $halfC);
    $countriesB = $countries->slice($halfC);

    // ---- Types ----
    $types = TourCategory::orderBy('name')->get();
    $halfT = ceil($types->count() / 2);
    $typesA = $types->slice(0, $halfT);
    $typesB = $types->slice($halfT);

    // ---- General links (only existing pages / routes) ----
    $pageSlugs = Page::pluck('slug')->toArray();
    $generalCandidates = [
        ['label' => 'About Us',                    'type' => 'page',  'slug' => 'about-us'],
        ['label' => 'Why Choose Us',               'type' => 'url',   'url'  => route('home') . '#why-choose-us'],
        ['label' => 'Contact Us',                  'type' => 'page',  'slug' => 'contact'],
        ['label' => 'Blog',                        'type' => 'route', 'route'=> 'blog.index'],
        ['label' => 'Frequently Asked Questions',  'type' => 'page',  'slug' => 'faqs'],
        ['label' => 'Terms and Conditions',        'type' => 'page',  'slug' => 'terms-and-conditions'],
        ['label' => 'Privacy Policy',              'type' => 'page',  'slug' => 'privacy-policy'],
        ['label' => 'Cookie Policy',               'type' => 'page',  'slug' => 'cookie-policy'],
        ['label' => 'Booking Policy',              'type' => 'page',  'slug' => 'booking-terms'],
        ['label' => 'Cancellation Policy',         'type' => 'page',  'slug' => 'refund-policy'],
    ];
    $generalLinks = collect();
    foreach ($generalCandidates as $cand) {
        if ($cand['type'] === 'url') {
            $generalLinks->push(['label' => $cand['label'], 'url' => $cand['url']]);
        } elseif ($cand['type'] === 'route') {
            if (\Illuminate\Support\Facades\Route::has($cand['route'])) {
                $generalLinks->push(['label' => $cand['label'], 'url' => route($cand['route'])]);
            }
        } else {
            if (in_array($cand['slug'], $pageSlugs, true)) {
                $generalLinks->push(['label' => $cand['label'], 'url' => route('page.show', $cand['slug'])]);
            }
        }
    }

    // ---- Social ----
    $socialLinks = collect([
        ['label' => 'Facebook', 'url' => Setting::get('social_facebook'),  'icon' => 'bi-facebook'],
        ['label' => 'Instagram','url' => Setting::get('social_instagram'), 'icon' => 'bi-instagram'],
        ['label' => 'YouTube',  'url' => Setting::get('social_youtube'),   'icon' => 'bi-youtube'],
        ['label' => 'X',        'url' => Setting::get('social_twitter'),   'icon' => 'bi-twitter-x'],
        ['label' => 'LinkedIn', 'url' => Setting::get('social_linkedin'),  'icon' => 'bi-linkedin'],
    ])->filter(fn($s) => !empty($s['url']));

    // ---- Currency (single currency supported) ----
    $currencyLabel = 'USD $';

    // ---- Copyright links ----
    $copyCandidates = [
        ['label' => $copyPrivacy,  'slug' => 'privacy-policy'],
        ['label' => $copyTerms,    'slug' => 'terms-and-conditions'],
    ];
    $copyLinks = collect();
    foreach ($copyCandidates as $c) {
        if (in_array($c['slug'], $pageSlugs, true)) {
            $copyLinks->push(['label' => $c['label'], 'url' => route('page.show', $c['slug'])]);
        }
    }
@endphp
<footer class="avf-footer">
    <div class="avf-inner">

        <!-- About -->
        <div class="avf-about">
            <a class="avf-logo" href="{{ route('home') }}" aria-label="{{ $siteName }} home">
                <img src="{{ $logo }}" alt="{{ $siteName }}">
            </a>
            <div class="avf-about-text">
                <h3 class="avf-about-title">{{ $footerAboutHeading }}</h3>
                <p>{{ $aboutText }}</p>
                <a class="avf-more" href="{{ route('page.show', 'about-us') }}">{{ $footerAboutLink }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>

        <!-- Link columns -->
        <div class="avf-grid">

            <!-- Our Statistics -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingStats }}</h4>
                <ul class="avf-stats">
                    @forelse ($stats as $s)
                        <li><span class="avf-stat-num">{{ $s['value'] }}</span> <span class="avf-stat-label">{{ $s['label'] }}</span></li>
                    @empty
                        <li class="avf-muted">{{ $emptyStats }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Safaris by Park -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingParks }}</h4>
                <ul class="avf-menu">
                    @forelse ($parks as $p)
                        <li><a href="{{ route('destination.show', $p->slug) }}">{{ $p->name }} {{ $p->type === 'mountain' ? 'Treks' : 'Safaris' }}</a></li>
                    @empty
                        <li class="avf-muted">{{ $emptyParks }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Safaris by Country -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingCountries }}</h4>
                <div class="avf-subgrid">
                    <ul class="avf-menu">
                        @foreach ($countriesA as $c)
                            <li><a href="{{ route('destinations.index', ['country' => $c['code']]) }}">{{ $c['name'] }} Safaris</a></li>
                        @endforeach
                    </ul>
                    <ul class="avf-menu">
                        @foreach ($countriesB as $c)
                            <li><a href="{{ route('destinations.index', ['country' => $c['code']]) }}">{{ $c['name'] }} Safaris</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Safaris by Type -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingTypes }}</h4>
                <div class="avf-subgrid">
                    <ul class="avf-menu">
                        @foreach ($typesA as $t)
                            <li><a href="{{ route('tours.category', $t->slug) }}">{{ $t->name }}</a></li>
                        @endforeach
                    </ul>
                    <ul class="avf-menu">
                        @foreach ($typesB as $t)
                            <li><a href="{{ route('tours.category', $t->slug) }}">{{ $t->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- General -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingGeneral }}</h4>
                <ul class="avf-menu">
                    @forelse ($generalLinks as $gl)
                        <li><a href="{{ $gl['url'] }}">{{ $gl['label'] }}</a></li>
                    @empty
                        <li class="avf-muted">{{ $emptyGeneral }}</li>
                    @endforelse
                </ul>
            </div>

        </div>

        <!-- Social + currency -->
        <div class="avf-utility">
            @if ($socialLinks->isNotEmpty())
                <div class="avf-social">
                    @foreach ($socialLinks as $s)
                        <a class="avf-social-btn" href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}">
                            <i class="bi {{ $s['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="avf-currency">
                <button type="button" class="avf-currency-btn" aria-label="Current currency">{{ $currencyLabel }} <span aria-hidden="true">&rsaquo;</span></button>
            </div>
        </div>

        <!-- Partners (only genuine, verified logos) -->
        @if ($partnerLogos->isNotEmpty())
            <div class="avf-partners">
                <h5 class="avf-partners-title">{{ $headingPartners }}</h5>
                <div class="avf-partners-row">
                    @foreach ($partnerLogos as $partner)
                        @php
                            $partnerImg = !empty($partner['logo_image_id'])
                                ? \App\Models\GalleryImage::find($partner['logo_image_id'])
                                : null;
                        @endphp
                        @if ($partnerImg)
                            @if (!empty($partner['link']))
                                <a class="avf-partner" href="{{ $partner['link'] }}" target="_blank" rel="noopener">
                                    <img src="{{ $partnerImg->getUrl('thumb') }}" alt="{{ $partner['name'] ?? 'Partner' }}" loading="lazy">
                                </a>
                            @else
                                <span class="avf-partner">
                                    <img src="{{ $partnerImg->getUrl('thumb') }}" alt="{{ $partner['name'] ?? 'Partner' }}" loading="lazy">
                                </span>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <!-- Copyright -->
    <div class="avf-copyright">
        <div class="avf-inner avf-copyright-inner">
            <a class="avf-copy-logo" href="{{ route('home') }}" aria-label="{{ $siteName }} home">
                <img src="{{ $logo }}" alt="{{ $siteName }}">
            </a>
            <p class="avf-copy-text">&copy; {{ date('Y') }} {{ $siteName }}. All Rights Reserved.</p>
            <ul class="avf-copy-links">
                @foreach ($copyLinks as $cl)
                    <li><a href="{{ $cl['url'] }}">{{ $cl['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>