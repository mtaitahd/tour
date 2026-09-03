<?php
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
?>
<footer class="avf-footer">
    <div class="avf-inner">

        <!-- About -->
        <div class="avf-about">
            <a class="avf-logo" href="<?php echo e(route('home')); ?>" aria-label="<?php echo e($siteName); ?> home">
                <img src="<?php echo e($logo); ?>" alt="<?php echo e($siteName); ?>">
            </a>
            <div class="avf-about-text">
                <h3 class="avf-about-title"><?php echo e($footerAboutHeading); ?></h3>
                <p><?php echo e($aboutText); ?></p>
                <a class="avf-more" href="<?php echo e(route('page.show', 'about-us')); ?>"><?php echo e($footerAboutLink); ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>

        <!-- Link columns -->
        <div class="avf-grid">

            <!-- Our Statistics -->
            <div class="avf-col">
                <h4 class="avf-heading"><?php echo e($headingStats); ?></h4>
                <ul class="avf-stats">
                    <?php $__empty_1 = true; $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li><span class="avf-stat-num"><?php echo e($s['value']); ?></span> <span class="avf-stat-label"><?php echo e($s['label']); ?></span></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="avf-muted"><?php echo e($emptyStats); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Safaris by Park -->
            <div class="avf-col">
                <h4 class="avf-heading"><?php echo e($headingParks); ?></h4>
                <ul class="avf-menu">
                    <?php $__empty_1 = true; $__currentLoopData = $parks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li><a href="<?php echo e(route('destination.show', $p->slug)); ?>"><?php echo e($p->name); ?> <?php echo e($p->type === 'mountain' ? 'Treks' : 'Safaris'); ?></a></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="avf-muted"><?php echo e($emptyParks); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Safaris by Country -->
            <div class="avf-col">
                <h4 class="avf-heading"><?php echo e($headingCountries); ?></h4>
                <div class="avf-subgrid">
                    <ul class="avf-menu">
                        <?php $__currentLoopData = $countriesA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('destinations.index', ['country' => $c['code']])); ?>"><?php echo e($c['name']); ?> Safaris</a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <ul class="avf-menu">
                        <?php $__currentLoopData = $countriesB; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('destinations.index', ['country' => $c['code']])); ?>"><?php echo e($c['name']); ?> Safaris</a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>

            <!-- Safaris by Type -->
            <div class="avf-col">
                <h4 class="avf-heading"><?php echo e($headingTypes); ?></h4>
                <div class="avf-subgrid">
                    <ul class="avf-menu">
                        <?php $__currentLoopData = $typesA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('tours.category', $t->slug)); ?>"><?php echo e($t->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <ul class="avf-menu">
                        <?php $__currentLoopData = $typesB; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('tours.category', $t->slug)); ?>"><?php echo e($t->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>

            <!-- General -->
            <div class="avf-col">
                <h4 class="avf-heading"><?php echo e($headingGeneral); ?></h4>
                <ul class="avf-menu">
                    <?php $__empty_1 = true; $__currentLoopData = $generalLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li><a href="<?php echo e($gl['url']); ?>"><?php echo e($gl['label']); ?></a></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="avf-muted"><?php echo e($emptyGeneral); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>

        <!-- Social + currency -->
        <div class="avf-utility">
            <?php if($socialLinks->isNotEmpty()): ?>
                <div class="avf-social">
                    <?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="avf-social-btn" href="<?php echo e($s['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($s['label']); ?>">
                            <i class="bi <?php echo e($s['icon']); ?>" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            <div class="avf-currency">
                <button type="button" class="avf-currency-btn" aria-label="Current currency"><?php echo e($currencyLabel); ?> <span aria-hidden="true">&rsaquo;</span></button>
            </div>
        </div>

        <!-- Partners (only genuine, verified logos) -->
        <?php if($partnerLogos->isNotEmpty()): ?>
            <div class="avf-partners">
                <h5 class="avf-partners-title"><?php echo e($headingPartners); ?></h5>
                <div class="avf-partners-row">
                    <?php $__currentLoopData = $partnerLogos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $partnerImg = !empty($partner['logo_image_id'])
                                ? \App\Models\GalleryImage::find($partner['logo_image_id'])
                                : null;
                        ?>
                        <?php if($partnerImg): ?>
                            <?php if(!empty($partner['link'])): ?>
                                <a class="avf-partner" href="<?php echo e($partner['link']); ?>" target="_blank" rel="noopener">
                                    <img src="<?php echo e($partnerImg->getUrl('thumb')); ?>" alt="<?php echo e($partner['name'] ?? 'Partner'); ?>" loading="lazy">
                                </a>
                            <?php else: ?>
                                <span class="avf-partner">
                                    <img src="<?php echo e($partnerImg->getUrl('thumb')); ?>" alt="<?php echo e($partner['name'] ?? 'Partner'); ?>" loading="lazy">
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Copyright -->
    <div class="avf-copyright">
        <div class="avf-inner avf-copyright-inner">
            <a class="avf-copy-logo" href="<?php echo e(route('home')); ?>" aria-label="<?php echo e($siteName); ?> home">
                <img src="<?php echo e($logo); ?>" alt="<?php echo e($siteName); ?>">
            </a>
            <p class="avf-copy-text">&copy; <?php echo e(date('Y')); ?> <?php echo e($siteName); ?>. All Rights Reserved.</p>
            <ul class="avf-copy-links">
                <?php $__currentLoopData = $copyLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><a href="<?php echo e($cl['url']); ?>"><?php echo e($cl['label']); ?></a></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</footer><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\partials\footer.blade.php ENDPATH**/ ?>