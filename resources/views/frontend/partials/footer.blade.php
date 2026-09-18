@php
    use App\Models\Setting;
    use App\Models\Page;
    use App\Models\GalleryImage;

    $siteName   = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $logo       = Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp');

    // ---- Footer text (admin-editable via Website Content → Footer) ----
    $footerAboutHeading = Setting::get('footer_about_heading') ?: 'About ' . $siteName;
    $footerAboutText    = Setting::get('footer_about_text');
    $footerAboutLink    = Setting::get('footer_about_link_text') ?: 'More About Us';
    $headingParks       = Setting::get('footer_heading_parks') ?: 'Safaris by Park';
    $headingCountries   = Setting::get('footer_heading_countries') ?: 'Safaris by Country';
    $headingTypes       = Setting::get('footer_heading_types') ?: 'Safaris by Type';
    $headingGeneral     = Setting::get('footer_heading_general') ?: 'General';
    $headingPartners    = Setting::get('footer_heading_partners') ?: 'Our Partners';
    $emptyParks         = Setting::get('footer_empty_parks') ?: 'Coming soon.';
    $emptyGeneral       = Setting::get('footer_empty_general') ?: 'Coming soon.';
    $copyPrivacy        = Setting::get('footer_copy_privacy') ?: 'Privacy Policy';
    $copyTerms          = Setting::get('footer_copy_terms') ?: 'Terms & Conditions';

    // ---- About description ----
    if ($footerAboutText) {
        $aboutText = $footerAboutText;
    } else {
        $aboutText = $siteName . ' designs tailor-made African safaris across East Africa. Travel with trusted local partners and verified experts.';
    }

    // ---- Footer link groups (managed under Website Content → Footer) ----
    $footerParks = Setting::json('footer_links_park', [
        ['label' => 'Maasai Mara Game Reserve Safaris', 'url' => '/maasai-mara'],
        ['label' => 'Mount Kilimanjaro Treks',          'url' => '/mount-kilimanjaro'],
        ['label' => 'Serengeti National Park Safaris', 'url' => '/serengeti-national-park'],
    ]);
    $footerCountries = Setting::json('footer_links_country', [
        ['label' => 'Tanzania Safaris', 'url' => '/destinations?country=TZ'],
        ['label' => 'Kenya Safaris',    'url' => '/destinations?country=KE'],
    ]);
    $footerTypes = Setting::json('footer_links_type', [
        ['label' => 'Kilimanjaro Climbing', 'url' => '/kilimanjaro-climbing-package'],
        ['label' => 'Tanzania Tours',       'url' => '/tanzania-tours'],
    ]);
    $footerGeneral = Setting::json('footer_links_general', [
        ['label' => 'About Us',             'url' => '/pages/about-us'],
        ['label' => 'Why Choose Us',        'url' => route('home') . '#why-choose-us'],
        ['label' => 'Contact Us',           'url' => '/pages/contact'],
        ['label' => 'Blog',                 'url' => '/blog'],
        ['label' => 'Terms and Conditions', 'url' => '/pages/terms-and-conditions'],
    ]);

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

    // ---- Partner logos ----
    $partnerLogos = collect(Setting::json('partner_logos') ?: []);

    // ---- Copyright links ----
    $pageSlugs = Page::pluck('slug')->toArray();
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

            <!-- Safaris by Park -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingParks }}</h4>
                <ul class="avf-menu">
                    @forelse ($footerParks as $p)
                        <li><a href="{{ $p['url'] ?? '#' }}">{{ $p['label'] ?? '' }}</a></li>
                    @empty
                        <li class="avf-muted">{{ $emptyParks }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Safaris by Country -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingCountries }}</h4>
                <ul class="avf-menu">
                    @forelse ($footerCountries as $c)
                        <li><a href="{{ $c['url'] ?? '#' }}">{{ $c['label'] ?? '' }}</a></li>
                    @empty
                        <li class="avf-muted">{{ $emptyParks }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- Safaris by Type -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingTypes }}</h4>
                <ul class="avf-menu">
                    @forelse ($footerTypes as $t)
                        <li><a href="{{ $t['url'] ?? '#' }}">{{ $t['label'] ?? '' }}</a></li>
                    @empty
                        <li class="avf-muted">{{ $emptyParks }}</li>
                    @endforelse
                </ul>
            </div>

            <!-- General -->
            <div class="avf-col">
                <h4 class="avf-heading">{{ $headingGeneral }}</h4>
                <ul class="avf-menu">
                    @forelse ($footerGeneral as $gl)
                        <li><a href="{{ $gl['url'] ?? '#' }}">{{ $gl['label'] ?? '' }}</a></li>
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
                                ? GalleryImage::find($partner['logo_image_id'])
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
