
<?php $__env->startSection('page-content'); ?>

<?php
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
?>

<div class="td-page">

    
    <div class="td-breadcrumb">
        <nav aria-label="Breadcrumb">
            <ol class="td-breadcrumb__list">
                <li><a href="<?php echo e(route('home')); ?>">Home</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <li><a href="<?php echo e(route('tours.index')); ?>">All Tours</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <?php if($countryName): ?>
                    <li><a href="<?php echo e(route('tours.index', ['destination' => optional($tour->destinations->first())->slug])); ?>"><?php echo e($countryName); ?> Tours</a></li>
                    <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <?php endif; ?>
                <li><a href="<?php echo e($operator['link']); ?>">Tour Operator</a></li>
                <li aria-hidden="true" class="td-breadcrumb__sep">›</li>
                <li class="td-breadcrumb__current" aria-current="page"><?php echo e($tour->cardTitle()); ?></li>
            </ol>
        </nav>
    </div>

    
    <section class="td-hero">
        <img src="<?php echo e($heroUrl); ?>" alt="<?php echo e($tour->cardTitle()); ?> hero photo" class="td-hero__img">
        <div class="td-hero__shade" aria-hidden="true"></div>

        <button type="button" class="td-wishlist" data-sfb-wishlist-tour="<?php echo e($tour->id); ?>"
                aria-label="Save this tour to your wishlist">
            <svg class="td-wishlist__heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.9-10-9.5C.4 8 2 4.5 5.5 4.2 7.6 4 9.3 5 12 7.6 14.7 5 16.4 4 18.5 4.2 22 4.5 23.6 8 22 11.5 19.5 16.1 12 21 12 21z"/></svg>
            <span class="td-wishlist__count" data-sfb-wish-count hidden>0</span>
        </button>

        <div class="td-hero__content">
            <div class="td-container">
                <h1 class="td-hero__title"><?php echo e($tour->cardTitle()); ?></h1>
                <p class="td-hero__offered">Offered By: <?php echo e($operator['name']); ?></p>
                <div class="td-hero__rating">
                    <?php echo $renderStars($displayRating); ?>

                    <?php if($displayRating): ?>
                        <span class="td-hero__score"><?php echo e(number_format((float) $displayRating, 1)); ?></span>
                    <?php endif; ?>
                    <?php if($displayReviews): ?>
                        <a href="#td-reviews-card" class="td-hero__reviews td-js-scroll" data-target="td-reviews-card"><?php echo e($displayReviews); ?> review<?php echo e($displayReviews === 1 ? '' : 's'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    
    <div class="td-tabsbar" id="td-tabsbar">
        <div class="td-container td-tabsbar__inner">
            <nav class="td-tabs" aria-label="Tour sections">
                <?php $__currentLoopData = $tdTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#td-<?php echo e($tab['id']); ?>"
                       class="td-tab<?php echo e($loop->first ? ' is-active' : ''); ?>"
                       data-td-tab="<?php echo e($tab['id']); ?>"><?php echo e($tab['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
            <button type="button" class="td-btn td-btn--quote td-tabsbar__cta td-js-quote-jump">
                Get a Free Quote <span aria-hidden="true">›</span>
            </button>
        </div>
    </div>

    
    <div class="td-container td-layout">

        
        <div class="td-main">

            
            <section class="td-section" id="td-overview" data-td-section="overview">
                <div class="td-overview">
                    <?php echo $tour->overview; ?>

                </div>
            </section>

            
            <?php if(count($routeMapDays) > 0 || count($routePoints) > 2 || $mapEmbedUrl): ?>
                <section class="td-section td-white-card" id="td-route">
                    <h2 class="td-heading">Route<span class="td-heading__line" aria-hidden="true"></span></h2>

                    <div class="td-route">
                        <div class="td-route__mapwrap">
                            <?php if(count($routeMapDays) > 0): ?>
                                <div id="td-leaflet-route" style="width:100%;height:420px;position:relative;z-index:0;" data-route='<?php echo json_encode($routeMapDays, 15, 512) ?>'></div>
                            <?php elseif($mapEmbedUrl): ?>
                                <iframe src="<?php echo e($mapEmbedUrl); ?>" title="<?php echo e($countryName); ?> route map"
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                        allowfullscreen></iframe>
                            <?php else: ?>
                                <div class="td-routemap" aria-hidden="true">
                                    <?php $__currentLoopData = $routePoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="td-routemap__node td-routemap__node--<?php echo e($point['marker']); ?>">
                                            <span class="td-routemap__dot"></span>
                                            <span class="td-routemap__label"><?php echo e($point['place']); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="td-route__list-wrap">
                            <p class="td-route__country">
                                <?php if($countryFlag): ?><span class="td-flag" aria-hidden="true"><?php echo e($countryFlag); ?></span><?php endif; ?>
                                <strong><?php echo e($countryName); ?></strong>
                            </p>
                            <ul class="td-route__list">
                                <?php $__currentLoopData = $routePoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="td-route__item td-route__item--<?php echo e($point['marker']); ?>">
                                        <span class="td-route__dot" aria-hidden="true"></span>
                                        <span class="td-route__text">
                                            <strong><?php echo e($point['label']); ?></strong><?php if($point['days']): ?> <em>(<?php echo e($point['days']); ?>)</em><?php endif; ?> — <?php echo e($point['place']); ?>

                                        </span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            
            <?php if(count($features)): ?>
                <section class="td-section td-white-card">
                    <h2 class="td-heading">Tour Features<span class="td-heading__line" aria-hidden="true"></span></h2>
                    <div class="td-features">
                        <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="td-feature">
                                <span class="td-feature__icon"><?php echo $iconSvg($feature['icon']); ?></span>
                                <span class="td-feature__body">
                                    <strong><?php echo e($feature['title']); ?></strong>
                                    <span><?php echo e($feature['text']); ?></span>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            
            <?php if(count($transportRows) || $safariCarImages->isNotEmpty()): ?>
                <section class="td-section td-white-card">
                    <h2 class="td-heading">Activities &amp; Transportation<span class="td-heading__line" aria-hidden="true"></span></h2>
                    <ul class="td-transport">
                        <?php $__currentLoopData = $transportRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <span class="td-transport__icon"><?php echo $iconSvg($row['icon']); ?></span>
                                <span><?php echo e($row['label']); ?> <strong><?php echo e($row['value']); ?></strong></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($safariCarImages->isNotEmpty()): ?>
                            <li>
                                <span class="td-transport__icon"><?php echo $iconSvg('vehicle'); ?></span>
                                <span>Game-drive vehicle <em>with pop-up roof for optimal wildlife viewing</em></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </section>
            <?php endif; ?>

            
            <?php if($accommodationRows->isNotEmpty()): ?>
                <section class="td-section td-white-card" id="td-accommodation">
                    <h2 class="td-heading">Accommodation &amp; Meals<span class="td-heading__line" aria-hidden="true"></span></h2>

                    <?php if($accommodationNotice): ?>
                        <p class="td-note"><?php echo $accommodationNotice; ?></p>
                    <?php endif; ?>

                    <div class="td-acc-table" role="table" aria-label="Day by day accommodation and meals">
                        <div class="td-acc-row td-acc-row--head" role="row">
                            <span role="columnheader">Day</span>
                            <span role="columnheader">Accommodation</span>
                            <span role="columnheader">Meals</span>
                            <span role="columnheader">Photos</span>
                        </div>

                        <?php $__currentLoopData = $accommodationRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="td-acc-row<?php echo e($loop->odd ? ' is-alt' : ''); ?>" role="row">
                                <span class="td-acc-day" role="cell">
                                    <?php if(!$row['hasStay'] && $loop->last): ?>
                                        End of tour<br><small>Day <?php echo e($row['day']); ?></small>
                                    <?php else: ?>
                                        Day <?php echo e($row['day']); ?>

                                    <?php endif; ?>
                                </span>

                                <span class="td-acc-stay" role="cell">
                                    <?php if($row['hasStay']): ?>
                                        <?php $__currentLoopData = $row['tiers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="td-acc-tier">
                                                <em class="td-acc-badge td-acc-badge--<?php echo e($tier['tier_key']); ?>"><?php echo e($tier['type']); ?></em>
                                                <?php if($tier['url']): ?>
                                                    <a href="<?php echo e($tier['url']); ?>" target="_blank" rel="noopener"><?php echo e($tier['name']); ?></a>
                                                <?php else: ?>
                                                    <strong><?php echo e($tier['name']); ?></strong>
                                                <?php endif; ?>
                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <span class="td-acc-none">(No accommodation)</span>
                                    <?php endif; ?>
                                </span>

                                <span class="td-acc-meals" role="cell"><?php echo e($row['meals'] !== '' ? $row['meals'] : '—'); ?></span>

                                <span class="td-acc-thumbs" role="cell">
                                    <?php $photos = $accommodationPhotoData[$row['day']] ?? []; ?>
                                    <?php if(count($photos)): ?>
                                        <?php $__currentLoopData = array_slice($photos, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button" class="td-thumb td-js-lightbox"
                                                    data-group="acc-day-<?php echo e($row['day']); ?>" data-index="<?php echo e($pi); ?>"
                                                    aria-label="Open photo: <?php echo e($photo['c']); ?>">
                                                <img src="<?php echo e($photo['u']); ?>" alt="<?php echo e($photo['c']); ?>" loading="lazy">
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(count($photos) > 3): ?>
                                            <button type="button" class="td-thumb td-thumb--more td-js-lightbox"
                                                    data-group="acc-day-<?php echo e($row['day']); ?>" data-index="3"
                                                    aria-label="Open all <?php echo e(count($photos)); ?> photos">
                                                +<?php echo e(count($photos) - 3); ?><span>Photos</span>
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="td-acc-nophotos">—</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            
            <section class="td-interested">
                <h2>Interested in This Tour?</h2>
                <p>Tell us your travel dates and group size — you will receive a personalised proposal directly from <?php echo e($operator['name']); ?>, normally within 24 hours.</p>
                <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                    Get a Free Quote <span aria-hidden="true">›</span>
                </button>
                <ul class="td-trustpoints">
                    <li>Best price guarantee</li>
                    <li>No booking fees — request is free</li>
                    <?php if($operator['email']): ?>
                        <li>Or <a href="mailto:<?php echo e($operator['email']); ?>?subject=<?php echo e(rawurlencode('Tour enquiry: ' . $tour->cardTitle())); ?>">contact the operator directly</a></li>
                    <?php endif; ?>
                </ul>
            </section>

        </div><!-- /td-main -->

        
        <aside class="td-sidebar">

            
            <div class="td-quote-card" id="td-quote-card">
                <?php if($priceFrom): ?>
                    <p class="td-quote-price">
                        From <strong>$<?php echo e(number_format((float) $priceFrom)); ?></strong>
                        <span>pp (<?php echo e($priceCurrency); ?>)</span>
                    </p>
                <?php elseif($isPriceOnRequest): ?>
                    <p class="td-quote-price td-quote-price--request">
                        <strong>Price on Request</strong>
                        <span><?php echo e($tour->pricing_source === 'none' ? 'Request a Package Price' : 'Prices vary by season — request a quote'); ?></span>
                    </p>
                <?php endif; ?>
                <?php if($hasInclExcl): ?>
                    <a href="#td-inclusions" class="td-quote-incl td-js-scroll" data-target="td-inclusions">What is included in this tour</a>
                <?php endif; ?>

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

            
            <div class="td-side-card td-operator">
                <div class="td-operator__logo">
                    <img src="<?php echo e($operator['logo']); ?>" alt="<?php echo e($operator['name']); ?> logo" loading="lazy">
                </div>
                <p class="td-operator__kicker">Offered By</p>
                <h3 class="td-operator__name"><a href="<?php echo e($operator['link']); ?>"><?php echo e($operator['name']); ?></a></h3>
                <div class="td-operator__rating">
                    <?php echo $renderStars($siteRatingAvg); ?>

                    <?php if($siteRatingAvg): ?>
                        <span class="td-hero__score"><?php echo e(number_format((float) $siteRatingAvg, 1)); ?></span>
                    <?php endif; ?>
                    <span class="td-muted">(<?php echo e($siteReviewCount); ?> review<?php echo e($siteReviewCount === 1 ? '' : 's'); ?>)</span>
                </div>
                <ul class="td-operator__meta">
                    <?php if($countryFlag): ?>
                        <li><span class="td-flag" aria-hidden="true"><?php echo e($countryFlag); ?></span> <?php echo e($countryName); ?></li>
                    <?php endif; ?>
                    <?php if($operator['location']): ?>
                        <li><?php echo e($operator['location']); ?></li>
                    <?php endif; ?>
                    <?php if($operator['founded']): ?>
                        <li>Founded <?php echo e($operator['founded']); ?></li>
                    <?php endif; ?>
                    <?php if($operator['employees']): ?>
                        <li><?php echo e($operator['employees']); ?> employees</li>
                    <?php endif; ?>
                </ul>
                <a class="td-morelink" href="<?php echo e($operator['link']); ?>">More About This Operator ›</a>
            </div>

            
            <div class="td-side-card td-reviews" id="td-reviews-card">
                <h3 class="td-side-title">Customer Reviews</h3>

                <?php if($reviews->isNotEmpty()): ?>
                    <div class="td-reviews__viewport" data-td-review-viewport>
                        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="td-review<?php echo e($loop->first ? ' is-active' : ''); ?>" data-td-review>
                                <header class="td-review__head">
                                    <?php if($review['avatar']): ?>
                                        <img class="td-review__avatar" src="<?php echo e($review['avatar']); ?>" alt="Photo of <?php echo e($review['name']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="td-review__avatar td-review__avatar--initial" aria-hidden="true"><?php echo e($review['initial']); ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo e($review['name']); ?></strong>
                                        <span class="td-review__loc">
                                            <?php if($review['flag']): ?><span class="td-flag" aria-hidden="true"><?php echo e($review['flag']); ?></span><?php endif; ?>
                                            <?php echo e($review['location']); ?>

                                        </span>
                                    </div>
                                </header>
                                <div class="td-review__stars">
                                    <?php echo $renderStars((float) $review['rating']); ?>

                                    <span class="td-hero__score"><?php echo e($review['rating']); ?>.0</span>
                                </div>
                                <p class="td-review__preview" data-td-preview><?php echo e(Str::limit($review['content'], 160)); ?></p>
                                <p class="td-review__full" data-td-full hidden><?php echo e($review['content']); ?></p>
                                <button type="button" class="td-review__toggle" data-td-toggle
                                        aria-expanded="false">Full Review</button>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="td-reviews__nav">
                        <button type="button" class="td-arrow" data-td-review-prev aria-label="Previous review">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        </button>
                        <span class="td-reviews__counter"><b data-td-review-current>1</b> of <?php echo e($reviews->count()); ?></span>
                        <button type="button" class="td-arrow" data-td-review-next aria-label="Next review">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                <?php else: ?>
                    <p class="td-muted">No reviews yet for this tour.</p>
                <?php endif; ?>
            </div>

            
            <?php if($relatedLinks->isNotEmpty()): ?>
                <div class="td-side-card td-relatedlinks">
                    <h3 class="td-side-title">Related Links</h3>
                    <ul>
                        <?php $__currentLoopData = $relatedLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e($link['url']); ?>"><?php echo e($link['label']); ?> ›</a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <?php if($galleryImages->isNotEmpty() || $tour->hasHeroImage()): ?>
                <?php $photoCardImage = $galleryImages->first()?->getUrl('medium-webp') ?: ($galleryImages->first()?->getUrl() ?: $heroUrl); ?>
                <div class="td-side-card td-photo-card">
                    <button type="button" class="td-photo-card__imgbtn td-js-lightbox" data-group="tour" data-index="0"
                            aria-label="Open tour photo gallery">
                        <img src="<?php echo e($photoCardImage); ?>" alt="<?php echo e($tour->cardTitle()); ?> photo" loading="lazy">
                    </button>
                    <button type="button" class="td-morelink td-js-lightbox" data-group="tour" data-index="0">
                        Open Photos (<?php echo e($galleryImages->count() ?: 1); ?>)
                    </button>
                </div>
            <?php endif; ?>

            
            <?php if($mapEmbedUrl): ?>
                <div class="td-side-card td-map-card">
                    <div class="td-map-card__preview">
                        <iframe src="<?php echo e($mapEmbedUrl); ?>" title="<?php echo e($countryName); ?> map preview"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" tabindex="-1"></iframe>
                    </div>
                    <button type="button" class="td-morelink td-js-map-open" data-map-url="<?php echo e($mapEmbedUrl); ?>">
                        <?php echo e($countryName ?: 'Destination'); ?> Map ›
                    </button>
                </div>
            <?php endif; ?>

        </aside>
    </div><!-- /td-layout -->

    
    <?php if($hasDayByDay): ?>
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-daybyday" data-td-section="daybyday">
                <h2 class="td-heading">Day by Day<span class="td-heading__line" aria-hidden="true"></span></h2>

                <ol class="td-days">
                    <?php $__currentLoopData = $itinerary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $dayPhotos = $dayPhotoData[$index + 1] ?? [];
                            $dayTiers = collect($day['accommodations'] ?? []);
                        ?>
                        <li class="td-day<?php echo e($loop->first ? ' is-open' : ''); ?>" data-td-day data-td-day-idx="<?php echo e($index + 1); ?>">
                            <button type="button" class="td-day__head" data-td-day-toggle
                                    aria-expanded="<?php echo e($loop->first ? 'true' : 'false'); ?>"
                                    aria-controls="td-day-body-<?php echo e($index + 1); ?>">
                                <span class="td-day__num"><?php echo e($index + 1); ?></span>
                                <span class="td-day__titles">
                                    <strong><?php echo e($day['title'] ?? 'Day ' . ($index + 1)); ?></strong>
                                    <?php if($dayTiers->isNotEmpty()): ?>
                                        <span><?php echo e($dayTiers->pluck('name')->implode(' / ')); ?></span>
                                    <?php endif; ?>
                                </span>
                                <svg class="td-day__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>

                            <div class="td-day__body" id="td-day-body-<?php echo e($index + 1); ?>" data-td-day-body>
                                <div class="td-day__grid">
                                    <div class="td-day__desc rich-text-content"><?php echo $day['description'] ?? ''; ?></div>

                                    <?php if(count($dayPhotos)): ?>
                                        <div class="td-day__thumbs">
                                            <?php $__currentLoopData = array_slice($dayPhotos, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <button type="button" class="td-thumb td-js-lightbox"
                                                        data-group="day-<?php echo e($index + 1); ?>" data-index="<?php echo e($pi); ?>"
                                                        aria-label="Open photo: <?php echo e($photo['c']); ?>">
                                                    <img src="<?php echo e($photo['u']); ?>" alt="<?php echo e($photo['c']); ?>" loading="lazy">
                                                </button>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <dl class="td-day__facts">
                                    <?php if(trim((string) ($day['meals'] ?? '')) !== ''): ?>
                                        <div><dt>Meals</dt><dd><?php echo e($day['meals']); ?></dd></div>
                                    <?php endif; ?>
                                    <?php if($dayTiers->isNotEmpty()): ?>
                                        <div><dt>Accommodation</dt><dd><?php echo e($dayTiers->pluck('name')->implode(' / ')); ?></dd></div>
                                    <?php else: ?>
                                        <div><dt>Accommodation</dt><dd>(No accommodation)</dd></div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </section>
        </div>
    <?php endif; ?>

    
    <?php if($hasTourPrice || $isPriceOnRequest): ?>
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-pricing" data-td-section="pricing">
                <h2 class="td-heading">Pricing<span class="td-heading__line" aria-hidden="true"></span></h2>

                
                <div class="td-pricing-hero">
                    <div class="td-pricing-hero__card">
                        <span class="td-pricing-hero__kicker">Starting From</span>
                        <?php if($priceFrom): ?>
                            <span class="td-pricing-hero__amount">$<?php echo e(number_format((float) $priceFrom, 0)); ?></span>
                        <?php else: ?>
                            <span class="td-pricing-hero__amount td-pricing-hero__amount--request">On Request</span>
                        <?php endif; ?>
                        <span class="td-pricing-hero__unit">per person (<?php echo e($priceCurrency); ?>)</span>
                    </div>

                    <div class="td-pricing-hero__details">
                        <?php if($tour->duration_days): ?>
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span><?php echo e($tour->duration_days); ?> Days<?php echo e($tour->duration_nights ? ' / ' . $tour->duration_nights . ' Nights' : ''); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($tour->tour_level): ?>
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l2.4 5 5.6.8-4 3.9 1 5.5-5-2.6-5 2.6 1-5.5-4-3.9L9.6 8z"/></svg>
                                <span><?php echo e($tourLevelLabel); ?> Level</span>
                            </div>
                        <?php endif; ?>
                        <?php if($tour->is_group_departure): ?>
                            <div class="td-pricing-hero__detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.4"/><path d="M15.5 14.6c2.9.4 5 2.7 5 5.4"/></svg>
                                <span>Group Departure</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <?php if($priceMatrix): ?>
                    <div class="td-rates">
                        <?php $__currentLoopData = $priceMatrix['seasons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seasonCode => $seasonLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="td-rate-block">
                                <h3 class="td-rate-season"><?php echo e($seasonLabel); ?> — Price per Person</h3>
                                <div class="td-tablewrap">
                                    <table class="td-table">
                                        <thead>
                                            <tr>
                                                <th scope="col"><?php echo e($priceMatrix['duration'] === 'single_day' ? 'Tour' : 'Package Level'); ?></th>
                                                <?php $__currentLoopData = $priceMatrix['sizes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <th scope="col"><?php echo e($size); ?> persons</th>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $priceMatrix['levels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <th scope="row"><span class="td-tierbadge td-tierbadge--<?php echo e(strtolower($level['key'])); ?>"><?php echo e($level['name']); ?></span></th>
                                                    <?php $__currentLoopData = $priceMatrix['sizes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php $cell = $priceMatrix['cells'][$seasonCode][$level['key']] ?? null; ?>
                                                        <td>
                                                            <?php if($cell): ?>
                                                                $<?php echo e(number_format((float) $cell['pp'][$size], 0)); ?>

                                                                <small>pp · group $<?php echo e(number_format((float) $cell['total'][$size], 0)); ?></small>
                                                            <?php else: ?>
                                                                —
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <p class="td-footnote">Automatic prices are shown for groups of 2, 4 or 6 travelers. Other group sizes or missing combinations are quoted on request — use the quote form below to request a custom price.</p>
                    </div>
                <?php endif; ?>

                
                <?php if($seasonPricing->isNotEmpty() && !$priceMatrix): ?>
                    <div class="td-rates">
                        <div class="td-rate-block">
                            <h3 class="td-rate-season">Package Base Rates</h3>
                            <div class="td-tablewrap">
                                <table class="td-table">
                                    <thead>
                                        <tr><th scope="col">Type of tour</th><th scope="col">2 persons</th><th scope="col">4 persons</th><th scope="col">6 persons</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $seasonPricing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <th scope="row"><span class="td-tierbadge td-tierbadge--<?php echo e(strtolower($tier['season'])); ?>"><?php echo e(ucfirst(strtolower($tier['season']))); ?></span></th>
                                                <?php $__currentLoopData = ['price_2p', 'price_4p', 'price_6p']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <td>$<?php echo e(number_format((float) $tier[$priceKey], 0)); ?> <small>USD*</small></td>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="td-footnote">Final rates may vary between High Season and Low Wet Season. Select your travel date or request a quotation for the applicable seasonal rate.</p>
                    </div>
                <?php endif; ?>

                
                <?php if($isPriceOnRequest): ?>
                    <div class="td-rates">
                        <div class="td-rate-block">
                            <h3 class="td-rate-season">This tour's package price is not listed</h3>
                            <p>Request a Package Price using the quote form below and we'll prepare a tailored price for your travel dates and group size.</p>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if($hasInclExcl): ?>
                    <?php $incPreview = collect($tour->inclusions ?? []); ?>
                    <div class="td-pricing-incl">
                        <p>This tour includes: <?php echo e($incPreview->take(4)->implode(', ')); ?><?php echo e($incPreview->count() > 4 ? ' and more' : ''); ?>.</p>
                        <a href="#td-inclusions" class="td-pricing-incl__link td-js-scroll" data-target="td-inclusions">See full inclusions ›</a>
                    </div>
                <?php endif; ?>

                
                <div class="td-pricing-cta">
                    <button type="button" class="td-btn td-btn--quote td-btn--xl td-js-quote-jump">
                        Get a Free Quote <span aria-hidden="true">›</span>
                    </button>
                    <p>Personalised proposal within 24 hours — no booking fees.</p>
                </div>
            </section>
        </div>
    <?php endif; /* /hasTourPrice */ ?>

    
    <?php if($hasInclExcl): ?>
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-inclusions" data-td-section="inclusions">
                <h2 class="td-heading">What is Included?<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class=" td-inc-grid">
                    <?php if(count($tour->inclusions ?? [])): ?>
                        <div>
                            <h3 class="td-inc-title td-inc-title--in">Included</h3>
                            <ul class="td-inc-list">
                                <?php $__currentLoopData = $tour->inclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <svg class="td-mark td-mark--yes" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span><?php echo e($inc); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if(count($tour->exclusions ?? [])): ?>
                        <div>
                            <h3 class="td-inc-title td-inc-title--ex">Excluded</h3>
                            <ul class="td-inc-list">
                                <?php $__currentLoopData = $tour->exclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <svg class="td-mark td-mark--no" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>
                                        <span><?php echo e($exc); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>

    
    <?php if($hasGetting): ?>
        <div class="td-container">
            <section class="td-section td-white-card td-panel" id="td-getting" data-td-section="getting">
                <h2 class="td-heading">Getting There<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class="td-getting">
                    <ul class="td-getting__facts">
                        <?php if(trim((string) $tour->starting_point) !== ''): ?>
                            <li><strong>Starting point</strong><span><?php echo e($tour->starting_point); ?></span></li>
                        <?php endif; ?>
                        <?php if(trim((string) $tour->ending_point) !== ''): ?>
                            <li><strong>End point</strong><span><?php echo e($tour->ending_point); ?></span></li>
                        <?php endif; ?>
                        <?php $__currentLoopData = $transportRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><strong><?php echo e(rtrim($row['label'], ':')); ?></strong><span><?php echo e($row['value']); ?></span></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($safariCarImages->isNotEmpty()): ?>
                            <li><strong>Transportation</strong><span>Game-drive vehicle</span></li>
                        <?php endif; ?>
                    </ul>
                    <?php if($mapEmbedUrl): ?>
                        <div class="td-getting__map">
                            <iframe src="<?php echo e($mapEmbedUrl); ?>" title="Meeting point map" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>

    
    <div class="td-container">
        <section class="td-section td-white-card td-panel" id="td-offeredby" data-td-section="offeredby">
            <h2 class="td-heading">Offered By<span class="td-heading__line" aria-hidden="true"></span></h2>

            <div class="td-offered">
                <div class="td-offered__id">
                    <img class="td-offered__logo" src="<?php echo e($operator['logo']); ?>" alt="<?php echo e($operator['name']); ?> logo" loading="lazy">
                    <div>
                        <h3><?php echo e($operator['name']); ?></h3>
                        <div class="td-operator__rating">
                            <?php echo $renderStars($siteRatingAvg); ?>

                            <?php if($siteRatingAvg): ?>
                                <span class="td-hero__score"><?php echo e(number_format((float) $siteRatingAvg, 1)); ?></span>
                            <?php endif; ?>
                            <span class="td-muted">(<?php echo e($siteReviewCount); ?> review<?php echo e($siteReviewCount === 1 ? '' : 's'); ?>)</span>
                        </div>
                        <ul class="td-operator__meta">
                            <?php if($countryFlag): ?>
                                <li><span class="td-flag" aria-hidden="true"><?php echo e($countryFlag); ?></span> <?php echo e($countryName); ?></li>
                            <?php endif; ?>
                            <?php if($operator['founded']): ?><li>Founded <?php echo e($operator['founded']); ?></li><?php endif; ?>
                            <?php if($operator['employees']): ?><li><?php echo e($operator['employees']); ?> employees</li><?php endif; ?>
                        </ul>
                        <div class="td-offered__actions">
                            <button type="button" class="td-btn td-btn--quote td-js-quote-jump">Get a Free Quote ›</button>
                            <?php if($operator['phone']): ?>
                                <a class="td-btn td-btn--ghost" href="tel:<?php echo e(preg_replace('/\s+/', '', $operator['phone'])); ?>">Call <?php echo e($operator['phone']); ?></a>
                            <?php endif; ?>
                            <?php if($operator['email']): ?>
                                <a class="td-btn td-btn--ghost" href="mailto:<?php echo e($operator['email']); ?>">Email</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($relatedTours->isNotEmpty()): ?>
                    <h4 class="td-offered__subtitle">More tours from this operator</h4>
                    <div class="td-offered__tours">
                        <?php $__currentLoopData = $relatedTours->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $other): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="td-minicard" href="<?php echo e(route('tour.show', $other->slug)); ?>">
                                <img src="<?php echo e($other->cardImageUrl('thumb-webp')); ?>" alt="<?php echo e($other->cardTitle()); ?>" loading="lazy">
                                <span>
                                    <strong><?php echo e($other->cardTitle()); ?></strong>
                                    <?php $miniPrice = $relatedFromPrices[$other->id] ?? null; ?>
                                    <em><?php echo e($other->duration_days); ?> days · <?php if($miniPrice): ?>$<?php echo e(number_format($miniPrice['amount'], 0)); ?> pp@else price on request <?php endif; ?></em>
                                </span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    
    <div class="td-container">
        <section class="td-section td-white-card" id="td-quote">
            <h2 class="td-heading">Request a Free Quote<span class="td-heading__line" aria-hidden="true"></span></h2>

            <?php if(session('success')): ?>
                <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if($errors->any() && old('tour_package_id') == $tour->id): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Please check the following:</strong>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('inquiries.store')); ?>" method="POST" id="tour-inquiry-form" class="td-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="tour_package_id" value="<?php echo e($tour->id); ?>">

                <div class="td-form__grid">
                    <div class="td-form__field">
                        <label class="form-label fw-medium">Who are you travelling with?</label>
                        <div class="td-radio-row">
                            <?php $__currentLoopData = ['Honeymoon', 'Couple', 'Family', 'Group of friends', 'Solo', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="td-radio"><input type="radio" name="companions" value="<?php echo e($companionOption); ?>"> <?php echo e($companionOption); ?></label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdFormTravelDate">When do you want to travel?</label>
                        <input type="date" id="tdFormTravelDate" name="travel_date" class="form-control form-control-lg" value="<?php echo e(old('travel_date')); ?>">
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Accommodation preference</label>
                        <div class="td-radio-row">
                            <?php $__currentLoopData = ['SILVER' => 'Silver', 'GOLD' => 'Gold', 'PLATINUM' => 'Platinum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accValue => $accLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="td-radio"><input type="radio" name="accommodation" value="<?php echo e($accValue); ?>"> <?php echo e($accLabel); ?></label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Type of Room</label>
                        <div class="td-radio-row">
                            <?php $__currentLoopData = ['Single', 'Double', 'Twin', 'Triple', 'Family', 'Suite']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="td-radio"><input type="radio" name="room_type" value="<?php echo e($roomOption); ?>"> <?php echo e($roomOption); ?></label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Type of Beds</label>
                        <div class="td-radio-row">
                            <?php $__currentLoopData = ['King' => 'King', 'Queen' => 'Queen', 'Twin' => 'Twin Beds', 'Double' => 'Double Bed', 'Any' => 'Any']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bedValue => $bedLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="td-radio"><input type="radio" name="bed_type" value="<?php echo e($bedValue); ?>"> <?php echo e($bedLabel); ?></label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="td-form__field">
                        <label class="form-label fw-medium">Budget range per person (USD)</label>
                        <div class="td-form__two">
                            <input type="number" name="budget_min" class="form-control form-control-lg" placeholder="Minimum" min="0" required value="<?php echo e(old('budget_min')); ?>">
                            <input type="number" name="budget_max" class="form-control form-control-lg" placeholder="Maximum" min="0" required value="<?php echo e(old('budget_max')); ?>">
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
                            <input type="number" id="tdFormAdults" name="adults" class="form-control form-control-lg" min="1" value="<?php echo e(old('adults', 2)); ?>" aria-label="Adults">
                            <input type="number" id="tdFormChildren" name="children" class="form-control form-control-lg" min="0" value="<?php echo e(old('children', 0)); ?>" aria-label="Children">
                        </div>
                    </div>

                    <?php if($priceMatrix && count($priceMatrix['levels']) > 1): ?>
                        <div class="td-form__field">
                            <label class="form-label fw-medium" for="tdFormLevel">Package Level</label>
                            <select id="tdFormLevel" name="package_level" class="form-control form-control-lg">
                                <?php $__currentLoopData = $priceMatrix['levels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lvl['key']); ?>" <?php if(old('package_level', $defaultLevel ?? $priceMatrix['levels'][0]['key']) === $lvl['key']): echo 'selected'; endif; ?>><?php echo e($lvl['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="td-form__field td-form__field--wide">
                        <label class="form-label fw-medium" for="tdFormMessage">Anything else you'd like to share with us?</label>
                        <textarea id="tdFormMessage" name="message" class="form-control form-control-lg" rows="4"><?php echo e(old('message')); ?></textarea>
                    </div>
                </div>

                
                <div class="td-quote-lookup" id="tdQuoteLookup" hidden></div>

                <div class="td-form__contact">
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdFirstName">First Name *</label>
                        <input type="text" id="tdFirstName" name="first_name" class="form-control form-control-lg" required value="<?php echo e(old('first_name')); ?>">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdLastName">Last Name *</label>
                        <input type="text" id="tdLastName" name="last_name" class="form-control form-control-lg" required value="<?php echo e(old('last_name')); ?>">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdEmail">Email Address *</label>
                        <input type="email" id="tdEmail" name="email" class="form-control form-control-lg" required value="<?php echo e(old('email')); ?>">
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdCountry">Country *</label>
                        <select id="tdCountry" name="country" class="form-control form-control-lg" required>
                            <option value="">Select Country</option>
                            <?php $__currentLoopData = ['Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo','Costa Rica','Croatia','Cuba','Cyprus','Czechia','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini','Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada','Guatemala','Guinea','Guyana','Haiti','Honduras','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati','Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius','Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar','Namibia','Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan','Palau','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan','Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $countryOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($countryOption); ?>"<?php echo e(old('country') === $countryOption ? ' selected' : ''); ?>><?php echo e($countryOption); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="td-form__field">
                        <label class="form-label fw-medium" for="tdPhone">Phone Number *</label>
                        <input type="tel" id="tdPhone" name="phone" class="form-control form-control-lg" required value="<?php echo e(old('phone')); ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-warning mt-2"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="td-btn td-btn--quote td-btn--xl mt-4">Submit Travel Proposal</button>
            </form>
        </section>
    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    
    <?php echo $__env->make('frontend.partials.faq-section', ['faqSubject' => $faqSubject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if($relatedTours->isNotEmpty()): ?>
        <section class="td-related" aria-labelledby="td-related-title">
            <div class="td-container">
                <h2 class="td-heading td-heading--light" id="td-related-title">Related Tours<span class="td-heading__line" aria-hidden="true"></span></h2>

                <div class="td-related__grid">
                    <?php $__currentLoopData = $relatedTours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedTour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="td-tour-card">
                            <a class="td-tour-card__media" href="<?php echo e(route('tour.show', $relatedTour->slug)); ?>">
                                <img src="<?php echo e($relatedTour->cardImageUrl('medium-webp')); ?>" alt="<?php echo e($relatedTour->cardTitle()); ?>" loading="lazy">
                                <span class="td-tour-card__shade" aria-hidden="true"></span>
                                <h3><?php echo e($relatedTour->cardTitle()); ?></h3>
                            </a>

                            <button type="button" class="td-wishlist td-wishlist--card" data-sfb-wishlist-tour="<?php echo e($relatedTour->id); ?>"
                                    aria-label="Save <?php echo e($relatedTour->cardTitle()); ?> to your wishlist">
                                <svg class="td-wishlist__heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.9-10-9.5C.4 8 2 4.5 5.5 4.2 7.6 4 9.3 5 12 7.6 14.7 5 16.4 4 18.5 4.2 22 4.5 23.6 8 22 11.5 19.5 16.1 12 21 12 21z"/></svg>
                                <span class="td-wishlist__count" data-sfb-wish-count hidden>0</span>
                            </button>

                            <div class="td-tour-card__body">
                                <?php $cardPrice = $relatedFromPrices[$relatedTour->id] ?? null; ?>
                                <p class="td-tour-card__price">
                                    <?php if($cardPrice): ?>
                                        <strong>$<?php echo e(number_format($cardPrice['amount'], 0)); ?></strong>
                                        <span>pp <?php echo e($cardPrice['currency']); ?></span>
                                    <?php else: ?>
                                        <strong>Quote Request</strong>
                                        <span>price on request</span>
                                    <?php endif; ?>
                                </p>
                                <ul class="td-tour-card__meta">
                                    <?php if($countryName): ?><li><?php echo e($countryFlag); ?> <?php echo e($countryName); ?></li><?php endif; ?>
                                    <?php if($relatedTour->duration_days): ?><li><?php echo e($relatedTour->duration_days); ?> days</li><?php endif; ?>
                                    <?php if($relatedTour->categories->first()): ?><li><?php echo e($relatedTour->categories->first()->name); ?></li><?php endif; ?>
                                    <?php if($tourLevelLabel = ($levelLabels[strtolower((string) $relatedTour->tour_level)] ?? null)): ?><li><?php echo e($tourLevelLabel); ?></li><?php endif; ?>
                                    <?php $places = $relatedTour->destinations->take(2)->pluck('name'); ?>
                                    <?php if($places->isNotEmpty()): ?><li>Places: <?php echo e($places->implode(', ')); ?></li><?php endif; ?>
                                </ul>
                                <div class="td-tour-card__op">
                                    <img src="<?php echo e($operator['logo']); ?>" alt="" loading="lazy">
                                    <span><?php echo e($operator['name']); ?></span>
                                    <?php echo $renderStars($siteRatingAvg, 'td-stars--sm'); ?>

                                    <?php if($siteRatingAvg): ?>
                                        <span class="td-hero__score"><?php echo e(number_format((float) $siteRatingAvg, 1)); ?></span>
                                        <span class="td-muted">(<?php echo e($siteReviewCount); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
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

    <script>window.TD_GALLERY = <?php echo json_encode($tdGallery, 15, 512) ?>;</script>

    
    <?php echo $__env->make('frontend.partials.safari-popovers', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
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
        <?php if(session('success') || ($errors->any() && old('tour_package_id') == $tour->id)): ?>
            window.setTimeout(function () { scrollToId('td-quote'); }, 150);
        <?php endif; ?>

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

    
    <script>
    (function () {
        'use strict';
        var LOOKUP_URL = <?php echo json_encode($priceLookupUrl, 15, 512) ?>;
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

    
    <?php if(count($routeMapDays) > 0): ?>
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
    <?php endif; ?>

    
    <?php if(count($faqs ?? [])): ?>
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
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\tours\show.blade.php ENDPATH**/ ?>