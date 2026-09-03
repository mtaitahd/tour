
<?php
    use App\Models\Setting;
    use App\Models\Destination;
    use App\Models\User;
?>

<?php $__env->startSection('title', Setting::get('site_name', 'Afro-Vertex Tours & Safaris') . ' – Best Safaris, Climbs & Beach Tours in East Africa'); ?>

<?php $__env->startSection('extra-head'); ?>
    <meta name="description" content="<?php echo e(Setting::get('meta_description', 'Discover Tanzania safaris, Kilimanjaro climbs, Zanzibar beaches and gorilla trekking with Afro-Vertex Tours.')); ?>">
    <meta name="keywords" content="safari tanzania, kilimanjaro climb, zanzibar beach, uganda gorillas, serengeti migration, east africa tours">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>

    <!-- Hero Section -->
    <section class="afro-hero" id="afro-hero">
        <div class="afro-hero__bg">
            <?php if($heroImageUrl = Setting::imageUrl('hero_image_id')): ?>
                <img src="<?php echo e($heroImageUrl); ?>" alt="Safari landscape with elephants at golden hour" class="afro-hero__img">
            <?php else: ?>
                <img src="<?php echo e(asset('front-end/html/assets/img/avt-hero.webp')); ?>" alt="Safari landscape with elephants at golden hour" class="afro-hero__img">
            <?php endif; ?>
        </div>
        <div class="afro-hero__overlay"></div>
        <div class="afro-hero__content">
            <div class="container">
                <div class="afro-hero__text wow fadeInUp" data-wow-delay="0.2s">
                    <h1 class="afro-hero__title"><?php echo e(Setting::get('hero_title', 'Discover the Wild Beauty of East Africa')); ?></h1>
                    <p class="afro-hero__subtitle"><?php echo e(Setting::get('hero_subtitle', 'Handcrafted safaris, mountain climbs, and beach escapes — curated by local experts who know Africa best.')); ?></p>
                </div>

                <div class="afro-search wow fadeInUp" data-wow-delay="0.4s">
                    <form class="afro-search__form" action="<?php echo e(route('tours.index')); ?>" method="get" role="search" aria-label="Safari search">
                        <div class="afro-search__bar">
                            <!-- Destination -->
                            <div class="afro-search__field destination-field" id="destinationField">
                                <label class="afro-search__field-inner" for="afro-destination">
                                    <i class="isax isax-location5 afro-search__icon" id="destinationIcon" aria-hidden="true"></i>
                                    <span class="afro-search__stack">
                                        <span class="afro-search__label">Where To</span>
                                        <input type="text" id="afro-destination" class="afro-search__input destination-search-input" placeholder="Where To" autocomplete="off" aria-label="Destination" aria-autocomplete="list" aria-expanded="false" aria-controls="destinationList">
                                    </span>
                                </label>
                                <button type="button" class="afro-search__clear" id="destinationClear" aria-label="Clear destination" hidden>
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                </button>
                                <input type="hidden" name="destination" id="afro-destination-value">
                                <div class="destination-dropdown" id="destinationDropdown" role="listbox" aria-label="Destinations" hidden>
                                    <div class="destination-dropdown__pointer" aria-hidden="true"></div>
                                    <div class="destination-dropdown__header">
                                        <span>Start typing or select below</span>
                                        <button type="button" class="destination-dropdown__close" id="destinationClose" aria-label="Close destinations">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                        </button>
                                    </div>
                                    <div class="destination-dropdown__list" id="destinationList" role="presentation"></div>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="afro-search__field start-date-field">
                                <div class="afro-search__input-wrap">
                                    <i class="isax isax-calendar-15 afro-search__icon" aria-hidden="true"></i>
                                    <span class="afro-search__stack">
                                        <span class="afro-search__label">Start Date</span>
                                        <input type="text" id="afro-date" class="afro-search__input" placeholder="Start Date" readonly autocomplete="off" aria-label="Start date" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar">
                                    </span>
                                </div>
                                <input type="hidden" name="when" id="afro-date-value" value="">
                            </div>

                            <!-- Travelers -->
                            <div class="afro-search__field travelers-field">
                                <div class="afro-search__input-wrap">
                                    <i class="isax isax-profile-2user5 afro-search__icon" aria-hidden="true"></i>
                                    <span class="afro-search__stack">
                                        <span class="afro-search__label">Travelers</span>
                                        <input type="text" id="afro-travellers" class="afro-search__input" value="2 Adults" readonly autocomplete="off" aria-label="Travelers" aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover">
                                    </span>
                                </div>
                                <button type="button" id="afro-travellers-reset" class="afro-search__clear afro-travellers-reset" aria-label="Reset travelers" hidden>
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                </button>
                                <input type="hidden" name="travellers" id="travellers-total" value="2">
                                <input type="hidden" name="adults" id="travellers-adults" value="2">
                                <input type="hidden" name="children" id="travellers-children" value="0">
                            </div>

                            <button type="submit" class="search-submit-button">
                                Find Safari Tours
                                <i class="isax isax-arrow-right-35 afro-search__btn-icon" aria-hidden="true"></i>
                            </button>
                        </div>
                        <input type="hidden" name="is_flexible" id="is-flexible-value" value="">
                    </form>
                </div>
            </div>
        </div>

    </section>
    <!-- /Hero Section -->

    <!-- About Us Section -->
    <section class="afro-about" id="afro-about">
        <div class="afro-about__container">
            <div class="afro-about__grid">

                <!-- Left Column: Content -->
                <div class="afro-about__content wow fadeInUp" data-wow-delay="0.2s">
                    <span class="afro-about__eyebrow"><?php echo e(Setting::get('home_about_eyebrow') ?: 'Our Story'); ?></span>
                    <h2 class="afro-about__title"><?php echo Setting::get('home_about_title') ?: 'About Us &ndash; Afro-Vertex Tours &amp; Safaris'; ?></h2>
                    <p class="afro-about__text"><?php echo Setting::get('home_about_text') ?: 'Welcome to Afro-Vertex Africa Tanzania Safari LTD, where unforgettable African adventures meet the untamed beauty of nature. Based in Tanzania, we are a trusted safari operator and destination management company dedicated to creating immersive wildlife experiences, tailor-made journeys, mountain adventures, and relaxing beach escapes across East Africa.'; ?></p>

                    <!-- Trust Features Checklist -->
                    <?php
                        $checklistRaw = Setting::get('home_about_checklist');
                        if ($checklistRaw) {
                            $checklistItems = array_map('trim', explode("\n", $checklistRaw));
                            $checklistItems = array_filter($checklistItems);
                        } else {
                            $checklistItems = ['Custom Safari Itineraries', '24/7 Customer Support', 'Licensed & Insured', 'Professional Local Guides', 'Best Price Guarantee', 'Sustainable Tourism'];
                        }
                        $checklistA = array_slice($checklistItems, 0, 3);
                        $checklistB = array_slice($checklistItems, 3, 3);
                    ?>
                    <div class="afro-about__checklist">
                        <div class="afro-about__check-col">
                            <?php $__currentLoopData = $checklistA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="afro-about__check-item">
                                <span class="afro-about__check-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="afro-about__check-label"><?php echo e($item); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="afro-about__check-col">
                            <?php $__currentLoopData = $checklistB; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="afro-about__check-item">
                                <span class="afro-about__check-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="afro-about__check-label"><?php echo e($item); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Discover More Button -->
                    <a href="<?php echo e(Setting::get('home_about_btn_link') ?: route('page.show', 'about-us')); ?>" class="afro-about__btn">
                        <?php echo e(Setting::get('home_about_btn_text') ?: 'Discover More'); ?>

                        <svg class="afro-about__btn-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                <!-- Right Column: Photo Stack -->
                <div class="afro-about__photos wow fadeInUp" data-wow-delay="0.4s">
                    <div class="afro-about__photos-bg" aria-hidden="true"></div>
                    <div class="afro-about__polaroid afro-about__polaroid--back">
                        <div class="afro-about__polaroid-img-wrap">
                            <img src="<?php echo e(asset('public/111/serengeti-great-migration.webp')); ?>" alt="Vast East African savannah landscape at golden hour" class="afro-about__polaroid-img" loading="lazy" width="600" height="450">
                        </div>
                        <span class="afro-about__polaroid-caption"><?php echo e(Setting::get('home_about_polaroid_1') ?: 'Wild Encounters'); ?></span>
                    </div>
                    <div class="afro-about__polaroid afro-about__polaroid--mid">
                        <div class="afro-about__polaroid-img-wrap">
                            <img src="<?php echo e(asset('public/114/serengeti-elephants.webp')); ?>" alt="Safari vehicle with tourists observing wildlife in Tanzania" class="afro-about__polaroid-img" loading="lazy" width="600" height="450">
                        </div>
                        <span class="afro-about__polaroid-caption"><?php echo e(Setting::get('home_about_polaroid_2') ?: 'Memories Forever'); ?></span>
                    </div>
                    <div class="afro-about__polaroid afro-about__polaroid--front">
                        <div class="afro-about__polaroid-img-wrap">
                            <img src="<?php echo e(asset('public/112/serengeti-lions-1.webp')); ?>" alt="Majestic wildlife in the Serengeti" class="afro-about__polaroid-img" loading="lazy" width="600" height="450">
                        </div>
                        <span class="afro-about__polaroid-caption"><?php echo e(Setting::get('home_about_polaroid_3') ?: 'Breathtaking Views'); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- /About Us Section -->

    <!-- ══════════════════════════════════════════════════════════════════════
         SAFARI ATLAS EXPLORER — Premium Interactive Country Explorer
         ══════════════════════════════════════════════════════════════════════ -->
    <section class="sae" id="sae" role="region" aria-label="Safari Atlas Explorer — Explore Africa's top safari countries">
        <?php
        $saeCountries = [
            [
                'id'=>'tanzania','rank'=>1,'name'=>'Tanzania','slug'=>'tanzania',
                'rating'=>4.9,'reviewCount'=>'2,847',
                'description'=>"Home to the Serengeti, Ngorongoro Crater, Mount Kilimanjaro and the islands of Zanzibar, Tanzania offers an extraordinary range of adventures. Witness the Great Migration, explore wildlife-rich national parks with professional local guides, climb Africa's highest mountain or finish your journey beside the Indian Ocean.",
                'priceRange'=>'From $238 per person/day','bestTime'=>'June to October & January to February','highSeason'=>'July to October',
                'heroImage'=>asset('public/safari-countries/tanzania.webp'),
                'heroImageAlt'=>'Serengeti Great Migration with wildebeest herds crossing the savannah',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','tanzania'),
                'tourCount'=>124,
                'destinations'=>[
                    ['name'=>'Serengeti','cat'=>'Wildlife'],
                    ['name'=>'Ngorongoro','cat'=>'Crater'],
                    ['name'=>'Zanzibar','cat'=>'Beach'],
                ],
            ],
            [
                'id'=>'kenya','rank'=>2,'name'=>'Kenya','slug'=>'kenya',
                'rating'=>4.8,'reviewCount'=>'2,341',
                'description'=>"Kenya is the birthplace of the safari experience. From the vast plains of the Maasai Mara to the flamingo-lined shores of Lake Nakuru and the snow-capped peak of Mount Kenya, this iconic destination delivers classic African wildlife encounters combined with vibrant cultural heritage.",
                'priceRange'=>'From $215 per person/day','bestTime'=>'July to October & January to February','highSeason'=>'July to October',
                'heroImage'=>asset('public/safari-countries/kenya.webp'),
                'heroImageAlt'=>'Maasai Mara savannah landscape with acacia trees at golden hour',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','kenya'),
                'tourCount'=>108,
                'destinations'=>[
                    ['name'=>'Maasai Mara','cat'=>'Wildlife'],
                    ['name'=>'Amboseli','cat'=>'Safari'],
                    ['name'=>'Diani Beach','cat'=>'Beach'],
                ],
            ],
            [
                'id'=>'uganda','rank'=>3,'name'=>'Uganda','slug'=>'uganda',
                'rating'=>4.7,'reviewCount'=>'1,658',
                'description'=>"Known as the Pearl of Africa, Uganda is home to endangered mountain gorillas in Bwindi Impenetrable Forest, chimpanzees in Kibale and the powerful Murchison Falls. Combine primate trekking with savannah safaris, water sports on Lake Victoria and lush crater-lake scenery.",
                'priceRange'=>'From $265 per person/day','bestTime'=>'June to September & December to February','highSeason'=>'June to September',
                'heroImage'=>asset('public/safari-countries/uganda.jpg'),
                'heroImageAlt'=>'Mountain gorilla in Bwindi Impenetrable Forest Uganda',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','uganda'),
                'tourCount'=>73,
                'destinations'=>[
                    ['name'=>'Bwindi','cat'=>'Gorillas'],
                    ['name'=>'Murchison Falls','cat'=>'Adventure'],
                    ['name'=>'Jinja','cat'=>'Water'],
                ],
            ],
            [
                'id'=>'rwanda','rank'=>4,'name'=>'Rwanda','slug'=>'rwanda',
                'rating'=>4.8,'reviewCount'=>'1,203',
                'description'=>"Rwanda offers intimate gorilla trekking experiences in Volcanoes National Park alongside golden monkey encounters and the dramatic canopy walk in Nyungwe Forest. The Land of a Thousand Hills also features Akagera's savannah Big Five and the tranquil shores of Lake Kivu.",
                'priceRange'=>'From $310 per person/day','bestTime'=>'June to September & December to February','highSeason'=>'June to September',
                'heroImage'=>asset('public/safari-countries/rwanda.jpg'),
                'heroImageAlt'=>'Gorilla trekking experience in Volcanoes National Park Rwanda',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','rwanda'),
                'tourCount'=>41,
                'destinations'=>[
                    ['name'=>'Volcanoes NP','cat'=>'Gorillas'],
                    ['name'=>'Nyungwe','cat'=>'Forest'],
                    ['name'=>'Akagera','cat'=>'Savannah'],
                ],
            ],
        ];
        $saeCount = count($saeCountries);
        $saeFirst = $saeCountries[0];
        ?>

        <div class="sae__inner">

            
            <header class="sae__header wow fadeInUp" data-wow-delay="0.1s">
                <p class="sae__eyebrow">Explore Africa</p>
                <h2 class="sae__title">Find Your Wild Side</h2>
                <p class="sae__subtitle">Four remarkable countries. Four completely different ways to experience Africa.</p>
            </header>

            
            <nav class="sae__rail-wrap wow fadeInUp" data-wow-delay="0.15s" aria-label="Select a safari country">
                <button class="sae__rail-arrow sae__rail-arrow--prev" type="button" aria-label="Previous countries" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="sae__rail" role="tablist" aria-label="Safari countries">
                    <?php $__currentLoopData = $saeCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        class="sae__marker <?php echo e($loop->first ? 'sae__marker--active' : ''); ?>"
                        data-id="<?php echo e($c['id']); ?>"
                        data-index="<?php echo e($loop->index); ?>"
                        role="tab"
                        id="sae-tab-<?php echo e($c['id']); ?>"
                        aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>"
                        aria-controls="sae-panel"
                        tabindex="<?php echo e($loop->first ? '0' : '-1'); ?>"
                    >
                        <span class="sae__marker-num"><?php echo e(str_pad($c['rank'], 2, '0', STR_PAD_LEFT)); ?></span>
                        <span class="sae__marker-name"><?php echo e($c['name']); ?></span>
                        <span class="sae__marker-rating"><?php echo e(number_format($c['rating'], 1)); ?></span>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="sae__rail-progress" aria-hidden="true">
                    <span class="sae__rail-progress-fill"></span>
                </div>
                <button class="sae__rail-arrow sae__rail-arrow--next" type="button" aria-label="Next countries">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </nav>

            
            <div class="sae__canvas wow fadeInUp" data-wow-delay="0.25s" id="sae-panel" role="tabpanel" aria-labelledby="sae-tab-<?php echo e($saeFirst['id']); ?>">

                
                <div class="sae__visual">
                    <?php $__currentLoopData = $saeCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="sae__slide <?php echo e($loop->first ? 'sae__slide--active' : ''); ?>" data-id="<?php echo e($c['id']); ?>" aria-hidden="<?php echo e($loop->first ? 'false' : 'true'); ?>">
                        <div class="sae__img-frame">
                            <img
                                src="<?php echo e($c['heroImage']); ?>"
                                alt="<?php echo e($c['heroImageAlt']); ?>"
                                class="sae__img"
                                loading="<?php echo e($loop->index < 2 ? 'eager' : 'lazy'); ?>"
                                width="840" height="560"
                                decoding="async"
                            >
                        </div>
                        <span class="sae__rank-hero" aria-hidden="true"><?php echo e(str_pad($c['rank'], 2, '0', STR_PAD_LEFT)); ?></span>
                        <div class="sae__location-tag">
                            <span class="sae__location-continent">Africa</span>
                            <span class="sae__location-slash">/</span>
                            <span class="sae__location-country"><?php echo e($c['name']); ?></span>
                            <span class="sae__location-tours"><?php echo e($c['tourCount']); ?> curated journeys</span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <div class="sae__seal" aria-hidden="true">
                        <div class="sae__seal-ring"></div>
                        <div class="sae__seal-inner">
                            <span class="sae__seal-rank" data-seal-rank>#<?php echo e($saeFirst['rank']); ?></span>
                            <span class="sae__seal-label">Top Safari<br>Country</span>
                        </div>
                    </div>
                </div>

                
                <div class="sae__info">
                    <div class="sae__info-inner">

                        <h3 class="sae__country-name" data-sae-name><?php echo e($saeFirst['name']); ?></h3>

                        <div class="sae__rating">
                            <span class="sae__stars" aria-hidden="true">★★★★★</span>
                            <span class="sae__score" data-sae-score><?php echo e($saeFirst['rating']); ?></span>
                            <span class="sae__rating-label">Traveller Rating</span>
                        </div>

                        <p class="sae__rank-label" data-sae-rank-label>Ranked #<?php echo e($saeFirst['rank']); ?> of <?php echo e($saeCount); ?> safari countries</p>

                        <p class="sae__desc" data-sae-desc><?php echo e($saeFirst['description']); ?></p>

                        <a class="sae__guide-link" data-sae-guide href="<?php echo e($saeFirst['countryPageUrl']); ?>">
                            Read the country guide
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>

                        
                        <div class="sae__notes">
                            <div class="sae__note">
                                <span class="sae__note-num">01</span>
                                <div class="sae__note-body">
                                    <span class="sae__note-label">Daily Rate</span>
                                    <span class="sae__note-value" data-sae-price><?php echo e($saeFirst['priceRange']); ?></span>
                                </div>
                            </div>
                            <div class="sae__note">
                                <span class="sae__note-num">02</span>
                                <div class="sae__note-body">
                                    <span class="sae__note-label">Best Travel Window</span>
                                    <span class="sae__note-value" data-sae-best><?php echo e($saeFirst['bestTime']); ?></span>
                                </div>
                            </div>
                            <div class="sae__note">
                                <span class="sae__note-num">03</span>
                                <div class="sae__note-body">
                                    <span class="sae__note-label">Peak Wildlife Season</span>
                                    <span class="sae__note-value" data-sae-season><?php echo e($saeFirst['highSeason']); ?></span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="sae__actions">
                            <a class="sae__btn sae__btn--primary" data-sae-primary href="<?php echo e($saeFirst['safariPageUrl']); ?>">
                                <span data-sae-primary-text>Explore <?php echo e($saeFirst['name']); ?> Safaris</span>
                                <svg class="sae__btn-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 5 7 7-7 7"/></svg>
                            </a>
                            <a class="sae__btn sae__btn--secondary" data-sae-secondary href="<?php echo e($saeFirst['countryPageUrl']); ?>">
                                <span data-sae-secondary-text>Country Guide</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="sae__route wow fadeInUp" data-wow-delay="0.35s" aria-label="Key destinations">
                <div class="sae__route-inner">
                    <?php $__currentLoopData = $saeFirst['destinations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $dest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="sae__route-stop">
                        <span class="sae__route-dot"></span>
                        <span class="sae__route-name"><?php echo e($dest['name']); ?></span>
                        <span class="sae__route-cat"><?php echo e($dest['cat']); ?></span>
                    </div>
                    <?php if(!$loop->last): ?>
                    <span class="sae__route-line" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <script id="sae-data" type="application/json"><?php echo json_encode($saeCountries); ?></script>

        
        <script>
        (function () {
            var root = document.getElementById('sae');
            if (!root) return;
            var data;
            try { data = JSON.parse(document.getElementById('sae-data').textContent); } catch (e) { return; }
            if (!data || !data.length) return;

            var count = data.length;
            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var TRANSITION = reducedMotion ? 0 : 420;
            var busy = false;

            var markers   = root.querySelectorAll('.sae__marker');
            var slides    = root.querySelectorAll('.sae__slide');
            var rail      = root.querySelector('.sae__rail');
            var prevBtn   = root.querySelector('.sae__rail-arrow--prev');
            var nextBtn   = root.querySelector('.sae__rail-arrow--next');
            var progFill  = root.querySelector('.sae__rail-progress-fill');

            var elName     = root.querySelector('[data-sae-name]');
            var elScore    = root.querySelector('[data-sae-score]');
            var elRankLbl  = root.querySelector('[data-sae-rank-label]');
            var elDesc     = root.querySelector('[data-sae-desc]');
            var elPrice    = root.querySelector('[data-sae-price]');
            var elBest     = root.querySelector('[data-sae-best]');
            var elSeason   = root.querySelector('[data-sae-season]');
            var elPriBtn   = root.querySelector('[data-sae-primary]');
            var elSecBtn   = root.querySelector('[data-sae-secondary]');
            var elPriTxt   = root.querySelector('[data-sae-primary-text]');
            var elSecTxt   = root.querySelector('[data-sae-secondary-text]');
            var elGuide    = root.querySelector('[data-sae-guide]');
            var elSealRank = root.querySelector('[data-seal-rank]');
            var routeInner = root.querySelector('.sae__route-inner');
            var panel      = root.querySelector('#sae-panel');

            function getItem(id) {
                for (var i = 0; i < count; i++) { if (data[i].id === id) return data[i]; }
                return null;
            }

            function updateRailProgress(idx) {
                if (!progFill || !rail) return;
                var total = rail.scrollWidth - rail.clientWidth;
                var pct = total > 0 ? (rail.scrollLeft / total) * 100 : 0;
                if (count <= 1) pct = 0;
                progFill.style.width = Math.min(pct + ((idx / Math.max(count - 1, 1)) * (100 - pct)), 100) + '%';
            }

            function scrollToMarker(idx) {
                var m = markers[idx];
                if (!m || !rail) return;
                var rLeft = rail.getBoundingClientRect().left;
                var mLeft = m.getBoundingClientRect().left;
                var offset = mLeft - rLeft + rail.scrollLeft - (rail.clientWidth / 2) + (m.clientWidth / 2);
                rail.scrollTo({ left: offset, behavior: reducedMotion ? 'auto' : 'smooth' });
            }

            function buildRoute(destinations) {
                if (!routeInner || !destinations || !destinations.length) {
                    if (routeInner) routeInner.closest('.sae__route').style.display = 'none';
                    return;
                }
                routeInner.closest('.sae__route').style.display = '';
                var html = '';
                for (var i = 0; i < destinations.length; i++) {
                    html += '<div class="sae__route-stop"><span class="sae__route-dot"></span><span class="sae__route-name">' + escapeHtml(destinations[i].name) + '</span><span class="sae__route-cat">' + escapeHtml(destinations[i].cat) + '</span></div>';
                    if (i < destinations.length - 1) html += '<span class="sae__route-line" aria-hidden="true"></span>';
                }
                routeInner.innerHTML = html;
            }

            function escapeHtml(t) {
                var d = document.createElement('div');
                d.appendChild(document.createTextNode(t));
                return d.innerHTML;
            }

            function select(id, fromClick) {
                if (busy) return;
                var item = getItem(id);
                if (!item) return;
                busy = true;
                var idx = 0;
                for (var i = 0; i < count; i++) { if (data[i].id === id) { idx = i; break; } }

                markers.forEach(function (m) {
                    var active = m.getAttribute('data-id') === id;
                    m.classList.toggle('sae__marker--active', active);
                    m.setAttribute('aria-selected', active ? 'true' : 'false');
                    m.setAttribute('tabindex', active ? '0' : '-1');
                });

                if (fromClick) scrollToMarker(idx);
                updateRailProgress(idx);

                panel.setAttribute('aria-labelledby', 'sae-tab-' + id);

                if (TRANSITION > 0) {
                    panel.style.opacity = '0';
                    panel.style.transform = 'translateY(8px)';
                }

                setTimeout(function () {
                    slides.forEach(function (s) {
                        var active = s.getAttribute('data-id') === id;
                        s.classList.toggle('sae__slide--active', active);
                        s.setAttribute('aria-hidden', active ? 'false' : 'true');
                    });

                    elName.textContent   = item.name;
                    elScore.textContent  = item.rating;
                    elRankLbl.textContent = 'Ranked #' + item.rank + ' of ' + count + ' safari countries';
                    elDesc.textContent   = item.description;
                    elPrice.textContent  = item.priceRange;
                    elBest.textContent   = item.bestTime;
                    elSeason.textContent = item.highSeason;
                    elPriTxt.textContent = 'Explore ' + item.name + ' Safaris';
                    elSecTxt.textContent = 'Country Guide';
                    elPriBtn.href        = item.safariPageUrl;
                    elSecBtn.href        = item.countryPageUrl;
                    elGuide.href         = item.countryPageUrl;
                    elSealRank.textContent = '#' + item.rank;

                    var full = Math.floor(item.rating);
                    var stars = '';
                    for (var s = 0; s < full; s++) stars += '★';
                    if (item.rating % 1 >= 0.5) stars += '★';
                    root.querySelector('.sae__stars').textContent = stars;

                    buildRoute(item.destinations);

                    if (TRANSITION > 0) {
                        panel.style.opacity = '1';
                        panel.style.transform = 'translateY(0)';
                    }

                    setTimeout(function () { busy = false; }, TRANSITION);
                }, TRANSITION);
            }

            markers.forEach(function (m) {
                m.addEventListener('click', function () { select(m.getAttribute('data-id'), true); });
            });

            var railNav = root.querySelector('.sae__rail');
            if (railNav) railNav.addEventListener('keydown', function (e) {
                var arr = Array.prototype.slice.call(markers);
                var cur = document.activeElement;
                var ci = arr.indexOf(cur);
                if (ci === -1) return;
                var ni = -1;
                if (e.key === 'ArrowRight') { e.preventDefault(); ni = (ci + 1) % count; }
                else if (e.key === 'ArrowLeft') { e.preventDefault(); ni = (ci - 1 + count) % count; }
                else if (e.key === 'Home') { e.preventDefault(); ni = 0; }
                else if (e.key === 'End') { e.preventDefault(); ni = count - 1; }
                if (ni !== -1) { arr[ni].focus(); select(arr[ni].getAttribute('data-id'), true); }
            });

            if (prevBtn) prevBtn.addEventListener('click', function () {
                if (!rail) return;
                rail.scrollBy({ left: -260, behavior: reducedMotion ? 'auto' : 'smooth' });
            });
            if (nextBtn) nextBtn.addEventListener('click', function () {
                if (!rail) return;
                rail.scrollBy({ left: 260, behavior: reducedMotion ? 'auto' : 'smooth' });
            });

            if (rail) {
                var updateArrows = function () {
                    if (prevBtn) prevBtn.disabled = rail.scrollLeft <= 4;
                    if (nextBtn) nextBtn.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 4;
                };
                rail.addEventListener('scroll', updateArrows, { passive: true });
                updateArrows();
                new ResizeObserver(updateArrows).observe(rail);
            }

            var wheelTimer = null;
            if (rail) rail.addEventListener('wheel', function (e) {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    e.preventDefault();
                    rail.scrollLeft += e.deltaY;
                    if (wheelTimer) clearTimeout(wheelTimer);
                    wheelTimer = setTimeout(function () { updateArrows(); }, 120);
                }
            }, { passive: false });

            updateRailProgress(0);

            if (reducedMotion) {
                panel.style.transition = 'none';
                panel.style.opacity = '1';
                panel.style.transform = 'none';
            } else {
                panel.style.transition = 'opacity ' + TRANSITION + 'ms ease, transform ' + TRANSITION + 'ms ease';
            }
        })();
        </script>
    </section>
    <!-- /Safari Atlas Explorer -->

    <!-- Featured Destinations -->
    <section class="section destination-section">
        <div class="featured-tours-container">
            <div class="ft-head ft-head--split wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header-six section-header-six--left">
                    <h2>Explore Top Destinations<span class="text-primary">.</span></h2>
                    <p class="section-header-six__desc">Diverse landscapes, iconic wildlife, and cultures that stay with you long after you return home.</p>
                </div>
                <a href="<?php echo e(route('destinations.index')); ?>" class="btn btn-dark sec-head-btn">
                    View All Destinations <i class="isax isax-arrow-right-3 ms-2"></i>
                </a>
            </div>

            <div class="featured-tours-grid">
                <?php $__empty_1 = true; $__currentLoopData = $featuredDestinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $destToursCount = $dest->tours()->where('status', 'published')->count();
                        $destImage = $dest->hasHeroImage() ? $dest->heroUrl('medium') : asset('public/assets/images/safari-hero.jpg');
                    ?>
                    <article class="afro-tour-card afro-tour-card--refined">
                        <a href="<?php echo e(route('destination.show', $dest->slug)); ?>" class="afro-tour-card__img-link" aria-label="<?php echo e($dest->name); ?>">
                            <div class="afro-tour-card__img-wrap">
                                <img src="<?php echo e($destImage); ?>" alt="<?php echo e($dest->name); ?>" class="afro-tour-card__img" width="800" height="600" loading="lazy"
                                     onerror="this.onerror=null;this.src='<?php echo e(asset('public/assets/images/safari-hero.jpg')); ?>';">
                                <div class="afro-tour-card__overlay" aria-hidden="true"></div>
                                <span class="afro-tour-card__badge"><?php echo e($destToursCount); ?> <?php echo e(Str::plural('Tour', $destToursCount)); ?></span>
                                <h3 class="afro-tour-card__title"><?php echo e($dest->name); ?></h3>
                            </div>
                        </a>

                        <div class="afro-tour-card__body">

                            <?php if($dest->description): ?>
                                <p class="afro-tour-card__desc"><?php echo e(Str::limit(strip_tags($dest->description), 120)); ?></p>
                            <?php endif; ?>

                            <div class="afro-tour-card__meta">
                                <span class="afro-tour-card__meta-item">
                                    <i class="isax isax-location5" aria-hidden="true"></i>
                                    <?php echo e($dest->country_code ? strtoupper($dest->country_code) : 'East Africa'); ?>

                                    <?php if($dest->type): ?>
                                        · <?php echo e(ucfirst(str_replace('_', ' ', $dest->type))); ?>

                                    <?php endif; ?>
                                </span>
                            </div>

                            <a href="<?php echo e(route('destination.show', $dest->slug)); ?>" class="afro-tour-card__more">
                                View Destination
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="afro-tour-grid__empty">
                        <h4>No featured destinations yet</h4>
                        <p>Check back soon — new destinations are on the way.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Tours -->
    <section class="section ft-section">
        <div class="featured-tours-container">
            <div class="ft-head wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header-six section-header-six--left">
                    <h2>Featured Tours Around East Africa<span class="text-primary">.</span></h2>
                    <p class="section-header-six__desc">Handpicked itineraries across Tanzania, Kenya, Uganda, and Rwanda — designed by locals who know every trail and watering hole.</p>
                </div>
            </div>

            <div class="featured-tours-grid">
                <?php $__empty_1 = true; $__currentLoopData = $featuredTours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $SingleTour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $tourLevel = str_replace('_', ' ', $SingleTour->tour_level ?? '');
                        $tourCategory = $SingleTour->categories->first()->name ?? null;
                        $visitParts = $SingleTour->destinations->pluck('name')->all();
                        $visitLine = collect()
                            ->when($SingleTour->starting_point, fn ($c) => $c->push($SingleTour->starting_point))
                            ->merge($visitParts)
                            ->when($SingleTour->ending_point, fn ($c) => $c->push($SingleTour->ending_point . ' (End)'))
                            ->implode(', ');
                    ?>
                    <article class="afro-tour-card afro-tour-card--refined">
                        <a href="<?php echo e(route('tour.show', $SingleTour->slug)); ?>" class="afro-tour-card__img-link" aria-label="<?php echo e($SingleTour->cardTitle()); ?>">
                            <div class="afro-tour-card__img-wrap">
                                <img src="<?php echo e($SingleTour->cardImageUrl('medium')); ?>" alt="<?php echo e($SingleTour->cardTitle()); ?>" class="afro-tour-card__img" width="800" height="600" loading="<?php echo e($loop->index < 3 ? 'eager' : 'lazy'); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo e(asset('public/assets/images/safari-hero.jpg')); ?>';">
                                <div class="afro-tour-card__overlay" aria-hidden="true"></div>
                                <h3 class="afro-tour-card__title"><?php echo e($SingleTour->cardTitle()); ?></h3>
                            </div>
                        </a>

                        <button type="button"
                                class="av-fav-btn"
                                data-tour-id="<?php echo e($SingleTour->id); ?>"
                                data-tour-title="<?php echo e($SingleTour->cardTitle()); ?>"
                                aria-pressed="false"
                                aria-label="Add <?php echo e($SingleTour->cardTitle()); ?> to favourites">
                            <i class="ti ti-heart" aria-hidden="true"></i>
                        </button>

                        <div class="afro-tour-card__body">

                            <?php if($SingleTour->overview): ?>
                                <p class="afro-tour-card__desc"><?php echo e(Str::limit(strip_tags($SingleTour->overview), 120)); ?></p>
                            <?php endif; ?>

                            <div class="afro-tour-card__price-row">
                                <div class="afro-tour-card__price">
                                    <?php $cardFrom = $fromPrices[$SingleTour->id] ?? null; ?>
                                    <?php if($cardFrom): ?>
                                        <span class="afro-tour-card__price-label">From</span>
                                        <span class="afro-tour-card__price-amount">$<?php echo e(number_format($cardFrom['amount'], 0)); ?></span>
                                        <span class="afro-tour-card__price-unit">per person</span>
                                    <?php else: ?>
                                        <span class="afro-tour-card__price-amount afro-tour-card__price-amount--quote">Request a Quote</span>
                                    <?php endif; ?>
                                </div>
                                <?php if($SingleTour->duration_days): ?>
                                    <span class="afro-tour-card__duration">
                                        <i class="isax isax-clock-1" aria-hidden="true"></i>
                                        <?php echo e($SingleTour->duration_days); ?> Days
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php
                                $attrParts = collect([$tourCategory, ucfirst($tourLevel)])->filter();
                            ?>
                            <?php if($attrParts->isNotEmpty()): ?>
                                <p class="afro-tour-card__attrs">
                                    <?php echo e($attrParts->implode(' · ')); ?>

                                </p>
                            <?php endif; ?>

                            <?php if($visitLine): ?>
                                <p class="afro-tour-card__desc">
                                    <strong>You Visit:</strong> <?php echo str_replace(' (End)', ' <span class="text-muted">(End)</span>', e($visitLine)); ?>

                                </p>
                            <?php endif; ?>

                            <a href="<?php echo e(route('tour.show', $SingleTour->slug)); ?>" class="afro-tour-card__more">
                                View Tour
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
                            </a>
                        </div>

                        <footer class="afro-tour-card__foot">
                            <img src="<?php echo e(Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp')); ?>" alt="Afro-Vertex Tours &amp; Safaris logo" width="34" height="34" loading="lazy"
                                 onerror="this.onerror=null;this.src='<?php echo e(asset('public/assets/images/logo-icon.png')); ?>';">
                            <div class="afro-tour-card__foot-info">
                                <strong>Afro-Vertex Tours &amp; Safaris</strong>
                                <span>
                                    <span class="afro-tour-card__stars">★★★★★</span>
                                    <?php echo e(number_format($avgOperatorRating, 1)); ?>/5 – <?php echo e($operatorReviews); ?> Review<?php echo e(Str::plural('s', $operatorReviews)); ?>

                                </span>
                            </div>
                        </footer>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="afro-tour-grid__empty">
                        <h4>No featured tours yet</h4>
                        <p>Check back soon or browse all tours.</p>
                        <a href="<?php echo e(route('tours.index')); ?>" class="afro-tour-card__cta afro-tour-card__cta--inline">View All Tours</a>
                    </div>
                <?php endif; ?>
            </div>

            <a href="<?php echo e(route('tours.index')); ?>" class="ft-all-btn">
                Explore All <?php echo e($toursCount); ?> Safari Tours
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
            </a>
        </div>
    </section>

    <!-- Worldwide Group Travellers -->
    <?php
        $wwgtCountries = config('countries.countries', []);
        $wwgtFeaturedCodes = config('countries.featured', []);
        $wwgtFeatured = collect($wwgtCountries)->filter(function ($c) use ($wwgtFeaturedCodes) {
            return in_array($c['code'], $wwgtFeaturedCodes, true);
        })->sortBy(function ($c) use ($wwgtFeaturedCodes) {
            return array_search($c['code'], $wwgtFeaturedCodes, true);
        })->values();

        $wwgtMore = collect($wwgtCountries)->filter(function ($c) use ($wwgtFeaturedCodes) {
            return ! in_array($c['code'], $wwgtFeaturedCodes, true);
        })->sortBy('name')->values();
    ?>
    <section class="wwgt" id="worldwide-group-travellers" aria-labelledby="wwgt-heading">
        <div class="container wwgt__container">
            <div class="wwgt__head wow fadeInUp">
                <span class="badge badge-soft-primary rounded-pill mb-2 wwgt__eyebrow">Worldwide Group Travellers</span>
                <h2 class="wwgt__title" id="wwgt-heading">Kilimanjaro Group Tours for Adventurers from Every Country</h2>
                <p class="wwgt__desc">Our group tours bring together climbers and travellers from around the world. Choose your country to explore relevant travel information and start planning your Kilimanjaro adventure.</p>
            </div>

            <div class="wwgt__grid wwgt__grid--more" id="wwgt-grid" role="list" aria-label="Countries">
                <?php $__currentLoopData = $wwgtFeatured; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wwgtC): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('frontend.partials.country-card', ['wwgtC' => $wwgtC, 'wwgtFeaturedFlag' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $wwgtMore; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wwgtC): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('frontend.partials.country-card', ['wwgtC' => $wwgtC, 'wwgtFeaturedFlag' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="wwgt__expand" id="wwgt-expand" hidden>
                <label class="wwgt__search" for="wwgt-search">
                    <span class="wwgt__search-label">Search countries</span>
                    <span class="wwgt__search-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="search" id="wwgt-search" class="wwgt__search-input" placeholder="Search by country name…" autocomplete="off">
                    </span>
                </label>
                <p class="wwgt__empty" id="wwgt-empty" hidden>No countries match your search.</p>
            </div>

            <div class="wwgt__actions">
                <button type="button" class="wwgt__toggle" id="wwgt-toggle" aria-expanded="false" aria-controls="wwgt-grid">
                    <span class="wwgt__toggle-label">View All Countries</span>
                    <span class="wwgt__toggle-icon" aria-hidden="true">▾</span>
                </button>
            </div>

            <div class="wwgt__visa" role="complementary">
                <div class="wwgt__visa-body">
                    <span class="wwgt__visa-badge" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </span>
                    <div>
                        <h3 class="wwgt__visa-title">Tanzania Visa Information</h3>
                        <p class="wwgt__visa-text">Visa requirements and fees vary by nationality. Travellers should confirm the latest entry requirements through Tanzania’s official immigration website before departure.</p>
                    </div>
                </div>
                <a class="wwgt__visa-link" href="https://visa.immigration.go.tz/" target="_blank" rel="noopener noreferrer">
                    Check official visa requirements
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="hiw">
        <div class="hiw__bg" aria-hidden="true">
            <div class="hiw__bg-pattern"></div>
            <div class="hiw__bg-acacia"></div>
        </div>
        <div class="container hiw__container">
            <div class="hiw__head hiw__head--left wow fadeInUp" data-wow-delay="0.2s">
                <div class="hiw__eyebrow">
                    <span class="hiw__eyebrow-text">How It Works</span>
                </div>
                <h2 class="hiw__title">Your Safari, Made Simple</h2>
                <p class="hiw__desc">Three easy steps from dream trip to departure day — we handle the details so you can focus on the adventure.</p>
            </div>

            <div class="hiw__cards-wrap">
                <div class="hiw__journey" aria-hidden="true">
                    <svg class="hiw__journey-svg" viewBox="0 0 1000 80" preserveAspectRatio="none">
                        <path d="M 167 26 Q 333 64 500 26 Q 667 64 833 26" fill="none" stroke="rgba(196,137,63,0.75)" stroke-width="3" stroke-dasharray="1 11" stroke-linecap="round" />
                    </svg>
                </div>

                <div class="hiw__grid">
                    <!-- Step 01 -->
                    <article class="hiw-card hiw-card--green wow fadeInUp" data-wow-delay="0.2s">
                        <span class="hiw-card__marker" aria-hidden="true"></span>
                        <span class="hiw-card__tab" aria-hidden="true"></span>
                        <div class="hiw-card__icon">
                            <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="32" cy="32" r="26" />
                                <polygon points="32,17 39,32 32,47 25,32" />
                                <circle cx="32" cy="32" r="2.4" fill="currentColor" stroke="none" />
                            </svg>
                        </div>
                        <span class="hiw-card__num">01</span>
                        <h3 class="hiw-card__title">Find Your Tour</h3>
                        <p class="hiw-card__desc">Explore handpicked safaris or search for the adventure that fits you.</p>
                        <span class="hiw-card__pattern" aria-hidden="true"></span>
                    </article>

                    <!-- Step 02 -->
                    <article class="hiw-card hiw-card--gold wow fadeInUp" data-wow-delay="0.35s">
                        <span class="hiw-card__marker" aria-hidden="true"></span>
                        <span class="hiw-card__tab" aria-hidden="true"></span>
                        <div class="hiw-card__icon">
                            <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="12" y="15" width="40" height="38" rx="6" />
                                <line x1="12" y1="25" x2="52" y2="25" />
                                <line x1="22" y1="10" x2="22" y2="18" />
                                <line x1="42" y1="10" x2="42" y2="18" />
                                <path d="M26 41 l5 5 9-11" />
                            </svg>
                        </div>
                        <span class="hiw-card__num">02</span>
                        <h3 class="hiw-card__title">Book &amp; Confirm</h3>
                        <p class="hiw-card__desc">Choose your dates and travelers, then reserve your place with confidence.</p>
                        <span class="hiw-card__pattern" aria-hidden="true"></span>
                    </article>

                    <!-- Step 03 -->
                    <article class="hiw-card hiw-card--green wow fadeInUp" data-wow-delay="0.5s">
                        <span class="hiw-card__marker" aria-hidden="true"></span>
                        <span class="hiw-card__tab" aria-hidden="true"></span>
                        <div class="hiw-card__icon">
                            <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 42 V33 H17 L22 22 H44 L50 33 V42" />
                                <path d="M10 42 H54" />
                                <path d="M22 33 V23 H44 V33" />
                                <path d="M17 33 H50" />
                                <circle cx="20" cy="44" r="4.5" />
                                <circle cx="46" cy="44" r="4.5" />
                            </svg>
                        </div>
                        <span class="hiw-card__num">03</span>
                        <h3 class="hiw-card__title">Enjoy the Journey</h3>
                        <p class="hiw-card__desc">Meet your guide, explore East Africa, and create lasting memories.</p>
                        <span class="hiw-card__pattern" aria-hidden="true"></span>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- Relaxing Accommodations -->
    <?php if($accommodations->count() > 0): ?>
        <section class="section bg-light-200">
            <div class="featured-tours-container">
                <div class="wow fadeInUp" data-wow-delay="0.2s">
                    <div class="section-header-six section-header-six--left">
                        <span class="badge badge-soft-primary rounded-pill mb-2">Where You'll Stay</span>
                        <h2>Relaxing Accommodations<span class="text-primary">.</span></h2>
                        <p class="section-header-six__desc">Boutique lodges, tented camps, and beach resorts — each stay handpicked for comfort, character, and a front-row seat to the wild.</p>
                    </div>
                </div>

                <div class="featured-tours-grid">
                    <?php $__currentLoopData = $accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="afro-tour-card afro-tour-card--refined wow fadeInUp" role="button" tabindex="0"
                             data-bs-toggle="modal" data-bs-target="#accommodationModal<?php echo e($stay->id); ?>" style="cursor: pointer;">
                            <div class="afro-tour-card__img-wrap">
                                <img src="<?php echo e($stay->cardImageUrl('medium')); ?>" alt="<?php echo e($stay->name); ?>" class="afro-tour-card__img" width="800" height="600" loading="lazy"
                                     onerror="this.onerror=null;this.src='<?php echo e(asset('public/assets/images/safari-hero.jpg')); ?>';">
                                <div class="afro-tour-card__overlay" aria-hidden="true"></div>
                                <?php if($stay->tier): ?>
                                    <span class="afro-tour-card__badge"><?php echo e($stay->tier); ?></span>
                                <?php endif; ?>
                                <h3 class="afro-tour-card__title"><?php echo e($stay->name); ?></h3>
                            </div>

                            <div class="afro-tour-card__body">
                                <div class="afro-tour-card__meta">
                                    <span class="afro-tour-card__meta-item">
                                        <i class="isax isax-location5" aria-hidden="true"></i>
                                        <?php echo e($stay->destination->name ?? $stay->location ?? 'East Africa'); ?>

                                    </span>
                                </div>

                                <?php if($stay->price_from): ?>
                                    <div class="afro-tour-card__price-row">
                                        <div class="afro-tour-card__price">
                                            <span class="afro-tour-card__price-label">From</span>
                                            <span class="afro-tour-card__price-amount afro-tour-card__price-amount--quote"><?php echo e($stay->currency); ?> <?php echo e(number_format($stay->price_from, 0)); ?></span>
                                            <span class="afro-tour-card__price-unit">/ night</span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <span class="afro-tour-card__more">
                                    View Details
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <a href="<?php echo e(route('accommodations.index')); ?>" class="ft-all-btn">
                    View All Accommodations
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
                </a>
            </div>
        </section>

        <!-- Accommodation Detail Modals — rendered outside the cards' section on
             purpose, so section-level overflow/transform handling can never clip
             or interfere with Bootstrap's fixed-position modal. -->
        <?php $__currentLoopData = $accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="modal fade" id="accommodationModal<?php echo e($stay->id); ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo e($stay->name); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php $stayGallery = $stay->galleryImages(); ?>

                            <?php if($stay->hasHeroImage() || $stayGallery->isNotEmpty()): ?>
                                <div class="row g-2 mb-3">
                                    <?php if($stay->hasHeroImage()): ?>
                                        <div class="col-6">
                                            <img src="<?php echo e($stay->heroUrl('medium')); ?>" class="img-fluid rounded" style="width:100%; height:160px; object-fit:cover;" alt="<?php echo e($stay->name); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $stayGallery->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-6">
                                            <img src="<?php echo e($img->getUrl('medium')); ?>" class="img-fluid rounded" style="width:100%; height:160px; object-fit:cover;" alt="<?php echo e($stay->name); ?>">
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <?php if($stay->tier): ?>
                                    <span class="badge bg-teal"><?php echo e($stay->tier); ?></span>
                                <?php endif; ?>
                                <span class="text-muted">
                                    <i class="isax isax-location5 me-1"></i><?php echo e($stay->destination->name ?? $stay->location ?? 'East Africa'); ?>

                                </span>
                                <?php if($stay->price_from): ?>
                                    <span class="fw-semibold text-primary ms-auto">
                                        From <?php echo e($stay->currency); ?> <?php echo e(number_format($stay->price_from, 0)); ?>/night
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if($stay->description): ?>
                                <p class="mb-3"><?php echo $stay->description; ?></p>
                            <?php endif; ?>

                            <?php if(!empty($stay->amenities)): ?>
                                <h6 class="mb-2">Amenities</h6>
                                <ul class="list-unstyled row g-1 mb-0">
                                    <?php $__currentLoopData = $stay->amenities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="col-6"><i class="isax isax-tick-circle5 text-primary me-1"></i><?php echo e($amenity); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo e(route('page.show', 'contact')); ?>" class="btn btn-primary">Enquire About This Stay</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>



    <!-- Why Choose Us -->
    <?php
        $whyChooseUsCards = json_decode(Setting::get('why_choose_us_cards', '[]'), true) ?? [];
    ?>
    <?php if(count($whyChooseUsCards) > 0): ?>
        <section class="section" id="why-choose-us">
            <div class="container">
                <div class="wow fadeInUp" data-wow-delay="0.2s">
                    <div class="section-header-six section-header-six--left">
                        <span class="badge badge-soft-primary rounded-pill mb-2">Why Choose Us</span>
                        <h2>The Africa Experts You Can Trust<span class="text-primary">.</span></h2>
                        <p class="section-header-six__desc">Over a decade of on-the-ground experience, trusted partnerships, and a genuine love for the places we call home.</p>
                    </div>
                </div>

                <div class="flip-cards">
                    <?php $__currentLoopData = $whyChooseUsCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $num = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT); ?>
                        <div class="flip-card" tabindex="0" role="button" aria-expanded="false" aria-label="<?php echo e($card['title'] ?? ''); ?>">
                            <div class="flip-card-inner">
                                <div class="flip-card-front">
                                    <span class="flip-card__num" aria-hidden="true"><?php echo e($num); ?></span>
                                    <?php if(!empty($card['icon'])): ?>
                                        <span class="flip-card__icon" aria-hidden="true"><i class="<?php echo e($card['icon']); ?>"></i></span>
                                    <?php endif; ?>
                                    <h3 class="flip-card__title"><?php echo e($card['title'] ?? ''); ?></h3>
                                    <span class="flip-card__hint">Hover or tap to learn more</span>
                                </div>
                                <div class="flip-card-back">
                                    <span class="flip-card-back__icon" aria-hidden="true">
                                        <?php if(!empty($card['icon'])): ?><i class="<?php echo e($card['icon']); ?>"></i><?php endif; ?>
                                    </span>
                                    <h3 class="flip-card__title"><?php echo e($card['title'] ?? ''); ?></h3>
                                    <?php if(!empty($card['description'])): ?>
                                        <p class="flip-card-back__text"><?php echo e($card['description']); ?></p>
                                    <?php endif; ?>
                                    <a class="flip-card-back__link" href="<?php echo e(Route::has('about') ? route('about') : '#'); ?>">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <script>
                (function () {
                    var cards = document.querySelectorAll('.flip-card');
                    function setFlipped(card, state) {
                        card.classList.toggle('is-flipped', state);
                        card.setAttribute('aria-expanded', state ? 'true' : 'false');
                    }
                    cards.forEach(function (card) {
                        card.addEventListener('click', function (e) {
                            if (e.target.closest('a, button')) return;
                            var flipped = card.classList.contains('is-flipped');
                            cards.forEach(function (c) { if (c !== card) setFlipped(c, false); });
                            setFlipped(card, !flipped);
                        });
                        card.addEventListener('keydown', function (e) {
                            if (e.key === ' ' || e.key === 'Enter') {
                                e.preventDefault();
                                var flipped = card.classList.contains('is-flipped');
                                cards.forEach(function (c) { if (c !== card) setFlipped(c, false); });
                                setFlipped(card, !flipped);
                            }
                        });
                    });
                })();
                </script>
            </div>
        </section>
    <?php endif; ?>

    <!-- Latest Blog Posts -->
    <section class="latest-blog-section">
        <div class="latest-blog-container">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="section-header-six section-header-six--left">
                    <h2>Latest Blog Posts<span class="text-primary">.</span></h2>
                    <p class="section-header-six__desc">Stories, tips, and field notes from the road — everything you need before your next African adventure.</p>
                </div>
            </div>

            <div class="latest-blog-grid">
                <?php $__empty_1 = true; $__currentLoopData = $latestPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $BlogPost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="blog-card">
                        <a href="<?php echo e(route('blog.show', $BlogPost->slug)); ?>" class="blog-card-link" aria-label="<?php echo e($BlogPost->cardTitle()); ?>">
                            <img src="<?php echo e($BlogPost->cardImageUrl('medium')); ?>"
                                 alt="<?php echo e($BlogPost->cardTitle()); ?>"
                                 width="800" height="600"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?php echo e(asset('public/assets/images/kilimanjaro-hero-2.jpg')); ?>';">
                            <h3 class="blog-card-title"><?php echo e($BlogPost->cardTitle()); ?></h3>
                        </a>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="latest-blog-empty">No blog posts yet — check back soon for travel tips and stories.</p>
                <?php endif; ?>
            </div>

            <a href="<?php echo e(route('blog.index')); ?>" class="all-blog-posts-button">
                <span>All Blog Posts</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
            </a>
        </div>
    </section>

    <?php if(!empty($galleryImages)): ?>
    <?php
        $scwImages = $galleryImages;
        $scwCount = count($scwImages);
        $scwFirst = $scwImages[0];
    ?>

    <!-- ══════════════════════════════════════════════════════════════════════
         SAFARI CINEMA WALL — Premium Interactive Gallery
         ══════════════════════════════════════════════════════════════════════ -->
    <section class="scw" id="explore-gallery" role="region" aria-label="Safari Cinema Wall — Image gallery">

        <div class="scw__inner">

            
            <header class="scw__header wow fadeInUp" data-wow-delay="0.1s">
                <p class="scw__eyebrow">The Untamed Archive</p>
                <h2 class="scw__title">The Afro Gallery</h2>
                <p class="scw__subtitle">Raw beauty, untold stories, and landscapes that demand to be explored — curated from our travels across the continent.</p>
            </header>

            
            <?php
                $scwCategories = ['All', 'Wildlife', 'Beaches', 'Mountains', 'Culture', 'Lodges'];
            ?>
            <div class="scw__filters" role="tablist" aria-label="Filter gallery by category">
                <?php $__currentLoopData = $scwCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fi => $fc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        class="scw__filter-btn <?php echo e($fi === 0 ? 'scw__filter-btn--active' : ''); ?>"
                        data-scw-cat="<?php echo e(strtolower($fc)); ?>"
                        role="tab"
                        aria-selected="<?php echo e($fi === 0 ? 'true' : 'false'); ?>"
                    ><?php echo e($fc); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="scw__wall wow fadeInUp" data-wow-delay="0.2s">

                
                <div class="scw__reel" role="tablist" aria-label="Gallery frames">
                    <?php $__currentLoopData = $scwImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($i < 3): ?>
                        <button
                            class="scw__reel-frame <?php echo e($i === 0 ? 'scw__reel-frame--active' : ''); ?>"
                            data-scw-idx="<?php echo e($i); ?>"
                            data-scw-cat="<?php echo e(strtolower($img['category'] ?? 'wildlife')); ?>"
                            role="tab"
                            aria-selected="<?php echo e($i === 0 ? 'true' : 'false'); ?>"
                            aria-label="View frame <?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>: <?php echo e($img['alt']); ?>"
                        >
                            <span class="scw__reel-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></span>
                            <div class="scw__reel-thumb">
                                <img
                                    src="<?php echo e($img['url']); ?>"
                                    alt="<?php echo e($img['alt']); ?>"
                                    class="scw__reel-img"
                                    width="190" height="140"
                                    loading="<?php echo e($i === 0 ? 'eager' : 'lazy'); ?>"
                                    decoding="async"
                                >
                            </div>
                        </button>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="scw__stage">
                    <?php $__currentLoopData = $scwImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="scw__frame <?php echo e($i === 0 ? 'scw__frame--active' : ''); ?>"
                        data-scw-idx="<?php echo e($i); ?>"
                        data-scw-cat="<?php echo e(strtolower($img['category'] ?? 'wildlife')); ?>"
                        aria-hidden="<?php echo e($i === 0 ? 'false' : 'true'); ?>"
                    >
                        <div class="scw__frame-img-wrap">
                            <a
                                href="<?php echo e($img['url']); ?>"
                                data-fancybox="cinema-wall"
                                data-caption="<?php echo e($img['alt']); ?>"
                                class="scw__frame-link"
                                tabindex="-1"
                            >
                                <img
                                    src="<?php echo e($img['url']); ?>"
                                    alt="<?php echo e($img['alt']); ?>"
                                    class="scw__frame-img"
                                    width="900" height="560"
                                    loading="<?php echo e($i < 2 ? 'eager' : 'lazy'); ?>"
                                    decoding="async"
                                >
                            </a>
                            <div class="scw__frame-grad" aria-hidden="true"></div>
                            <div class="scw__frame-curve" aria-hidden="true"></div>
                        </div>
                        <div class="scw__frame-caption">
                            <span class="scw__frame-cat"><?php echo e($img['country']); ?> / <?php echo e($img['destination']); ?></span>
                            <span class="scw__frame-title"><?php echo e($img['destination']); ?></span>
                            <?php if($img['caption']): ?>
                                <span class="scw__frame-desc"><?php echo e(\Illuminate\Support\Str::limit($img['caption'], 80)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <button
                        type="button"
                        class="scw__explore"
                        data-scw-explore
                        aria-label="Open current gallery image in lightbox"
                    >
                        <span class="scw__explore-ring" aria-hidden="true"></span>
                        <span class="scw__explore-play" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="8,4 20,12 8,20"/></svg>
                        </span>
                        <span class="scw__explore-label">Explore Frame</span>
                    </button>

                    
                    <button type="button" class="scw__stage-nav scw__stage-nav--prev" data-scw-nav="prev" aria-label="Previous frame">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" class="scw__stage-nav scw__stage-nav--next" data-scw-nav="next" aria-label="Next frame">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>

                
                <div class="scw__panel">
                    <div class="scw__panel-inner">

                        <span class="scw__panel-chapter" data-scw-chapter>01</span>
                        <div class="scw__panel-divider" aria-hidden="true"></div>

                        <span class="scw__panel-label">Current Frame</span>
                        <p class="scw__panel-statement" data-scw-statement><?php echo e($scwFirst['destination']); ?></p>

                        <?php if($scwFirst['caption']): ?>
                            <p class="scw__panel-caption" data-scw-caption><?php echo e(\Illuminate\Support\Str::limit($scwFirst['caption'], 100)); ?></p>
                        <?php else: ?>
                            <p class="scw__panel-caption" data-scw-caption></p>
                        <?php endif; ?>

                        <div class="scw__panel-meta">
                            <div class="scw__panel-meta-row">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span data-scw-location><?php echo e($scwFirst['destination']); ?></span>
                            </div>
                        </div>

                        
                        <div class="scw__previews" data-scw-previews>
                            <?php $__currentLoopData = $scwImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($i > 0 && $i < 4): ?>
                                <button
                                    type="button"
                                    class="scw__preview <?php echo e($i === 1 ? 'scw__preview--active' : ''); ?>"
                                    data-scw-idx="<?php echo e($i); ?>"
                                    aria-label="View frame <?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>: <?php echo e($img['alt']); ?>"
                                >
                                    <span class="scw__preview-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></span>
                                    <div class="scw__preview-thumb">
                                        <img
                                            src="<?php echo e($img['url']); ?>"
                                            alt="<?php echo e($img['alt']); ?>"
                                            class="scw__preview-img"
                                            width="220" height="90"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </div>
                                    <span class="scw__preview-title"><?php echo e($img['destination']); ?></span>
                                </button>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        
                        <div class="scw__viewall-wrap">
                            <button
                                type="button"
                                class="scw__viewall"
                                data-scw-viewall
                                aria-label="View all <?php echo e($scwCount); ?> photos in lightbox"
                            >
                                <span class="scw__viewall-text">View<br><strong><?php echo e($scwCount); ?></strong></span>
                                <svg class="scw__viewall-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            
            <div class="scw__timeline wow fadeInUp" data-wow-delay="0.3s">
                <button type="button" class="scw__tl-arrow scw__tl-arrow--prev" data-scw-tl-nav="prev" aria-label="Previous frame">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>

                <div class="scw__tl-track">
                    <div class="scw__tl-line" aria-hidden="true">
                        <span class="scw__tl-fill" data-scw-tl-fill></span>
                    </div>
                    <?php $__currentLoopData = $scwImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($i < 6): ?>
                        <button
                            type="button"
                            class="scw__tl-tick <?php echo e($i === 0 ? 'scw__tl-tick--active' : ''); ?>"
                            data-scw-idx="<?php echo e($i); ?>"
                            data-scw-cat="<?php echo e(strtolower($img['category'] ?? 'wildlife')); ?>"
                            aria-label="Frame <?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>: <?php echo e($img['destination']); ?>"
                        >
                            <span class="scw__tl-dot"></span>
                            <span class="scw__tl-cat"><?php echo e($img['destination']); ?></span>
                        </button>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <button type="button" class="scw__tl-arrow scw__tl-arrow--next" data-scw-tl-nav="next" aria-label="Next frame">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

        </div>

        
        <script id="scw-data" type="application/json"><?php echo json_encode($scwImages); ?></script>

        
        <script>
        (function () {
            'use strict';
            var root = document.getElementById('explore-gallery');
            if (!root) return;

            var allData;
            try { allData = JSON.parse(document.getElementById('scw-data').textContent); } catch (e) { return; }
            if (!allData || !allData.length) return;

            var data = allData;
            var count = data.length;
            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var TRANSITION = reducedMotion ? 0 : 450;
            var busy = false;
            var currentIdx = 0;

            var elChapter    = root.querySelector('[data-scw-chapter]');
            var elStatement  = root.querySelector('[data-scw-statement]');
            var elLocation   = root.querySelector('[data-scw-location]');
            var elCaption    = root.querySelector('[data-scw-caption]');
            var elExplore    = root.querySelector('[data-scw-explore]');
            var elPreviews   = root.querySelector('[data-scw-previews]');
            var reel         = root.querySelector('.scw__reel');
            var stage        = root.querySelector('.scw__stage');
            var tlTrack      = root.querySelector('.scw__tl-track');
            var filterBtns   = root.querySelectorAll('.scw__filter-btn');

            /* ── Build preview HTML ────────────────────────────── */
            function buildPreviewHTML(startIdx) {
                var html = '';
                var shown = 0;
                for (var j = 1; j < count && shown < 3; j++) {
                    var idx = (startIdx + j) % count;
                    var active = (idx === currentIdx) ? ' scw__preview--active' : '';
                    html += '<button type="button" class="scw__preview' + active + '" data-scw-idx="' + idx + '" aria-label="View frame ' + String(idx + 1).padStart(2, '0') + ': ' + (data[idx].alt || '') + '">';
                    html += '<span class="scw__preview-num">' + String(idx + 1).padStart(2, '0') + '</span>';
                    html += '<div class="scw__preview-thumb"><img src="' + data[idx].url + '" alt="' + (data[idx].alt || '') + '" class="scw__preview-img" width="220" height="90" loading="lazy" decoding="async"></div>';
                    html += '<span class="scw__preview-title">' + (data[idx].destination || '') + '</span>';
                    html += '</button>';
                    shown++;
                }
                return html;
            }

            function updatePreviews(idx) {
                if (!elPreviews) return;
                elPreviews.innerHTML = buildPreviewHTML(idx);
                elPreviews.querySelectorAll('.scw__preview').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        selectIdx(parseInt(btn.getAttribute('data-scw-idx')), true);
                    });
                });
            }

            /* ── Core selection ──────────────────────────────── */
            function selectIdx(idx, fromClick) {
                if (busy || idx === currentIdx || idx < 0 || idx >= count) return;
                busy = true;

                var frames     = root.querySelectorAll('.scw__frame');
                var reelFrames = root.querySelectorAll('.scw__reel-frame');
                var tlTicks    = root.querySelectorAll('.scw__tl-tick');
                var tlFill     = root.querySelector('[data-scw-tl-fill]');

                frames.forEach(function (f) {
                    var match = parseInt(f.getAttribute('data-scw-idx')) === idx;
                    f.classList.toggle('scw__frame--active', match);
                    f.setAttribute('aria-hidden', match ? 'false' : 'true');
                });

                reelFrames.forEach(function (r) {
                    var ri = parseInt(r.getAttribute('data-scw-idx'));
                    var match = ri === idx;
                    r.classList.toggle('scw__reel-frame--active', match);
                    r.setAttribute('aria-selected', match ? 'true' : 'false');
                });

                tlTicks.forEach(function (t) {
                    var ti = parseInt(t.getAttribute('data-scw-idx'));
                    var match = ti === idx;
                    t.classList.toggle('scw__tl-tick--active', match);
                });

                if (elChapter) elChapter.textContent = String(idx + 1).padStart(2, '0');
                if (elStatement) elStatement.textContent = data[idx].destination;
                if (elLocation) elLocation.textContent = data[idx].destination;
                if (elCaption) {
                    elCaption.textContent = data[idx].caption ? data[idx].caption.substring(0, 100) : '';
                }

                updatePreviews(idx);

                if (tlFill) {
                    var pct = count > 1 ? (idx / (count - 1)) * 100 : 0;
                    tlFill.style.width = Math.min(pct, 100) + '%';
                }

                currentIdx = idx;
                setTimeout(function () { busy = false; }, TRANSITION);
            }

            function selectNext() {
                var next = (currentIdx + 1) % count;
                selectIdx(next, true);
            }

            function selectPrev() {
                var prev = (currentIdx - 1 + count) % count;
                selectIdx(prev, true);
            }

            /* ── Lightbox ────────────────────────────────────── */
            function openLightbox() {
                if (typeof jQuery === 'undefined' || !jQuery.fn.fancybox) return;
                var items = jQuery('[data-fancybox="cinema-wall"]', root);
                if (items.length) {
                    jQuery.fancybox.open(items, { loop: true, keyboard: true }, currentIdx);
                }
            }

            function initFancybox() {
                if (typeof jQuery === 'undefined' || !jQuery.fn.fancybox) return;
                jQuery('[data-fancybox="cinema-wall"]', root).fancybox({
                    loop: true, keyboard: true, arrows: true, infobar: true,
                    toolbar: true, buttons: ['zoom', 'slideShow', 'thumbs', 'close'],
                    animationEffect: 'zoom', transitionEffect: 'fade',
                    protect: true, preventCaptionOverlap: true, idleTime: 4,
                    clickContent: function (current) { return current.type === 'image' ? 'zoom' : false; }
                });
            }

            /* ── Render wall from data ────────────────────────── */
            function renderWall() {
                count = data.length;
                currentIdx = 0;

                // Reel (first 3)
                var rHTML = '';
                for (var i = 0; i < Math.min(3, count); i++) {
                    rHTML += '<button class="scw__reel-frame' + (i === 0 ? ' scw__reel-frame--active' : '') + '" data-scw-idx="' + i + '" role="tab" aria-selected="' + (i === 0 ? 'true' : 'false') + '" aria-label="View frame ' + String(i + 1).padStart(2, '0') + ': ' + (data[i].alt || '') + '">';
                    rHTML += '<span class="scw__reel-num">' + String(i + 1).padStart(2, '0') + '</span>';
                    rHTML += '<div class="scw__reel-thumb"><img src="' + data[i].url + '" alt="' + (data[i].alt || '') + '" class="scw__reel-img" width="190" height="140" loading="' + (i === 0 ? 'eager' : 'lazy') + '" decoding="async"></div>';
                    rHTML += '</button>';
                }
                reel.innerHTML = rHTML;

                // Frames (all)
                var fHTML = '';
                for (var i = 0; i < count; i++) {
                    fHTML += '<div class="scw__frame' + (i === 0 ? ' scw__frame--active' : '') + '" data-scw-idx="' + i + '" aria-hidden="' + (i === 0 ? 'false' : 'true') + '">';
                    fHTML += '<div class="scw__frame-img-wrap">';
                    fHTML += '<a href="' + data[i].url + '" data-fancybox="cinema-wall" data-caption="' + (data[i].alt || '') + '" class="scw__frame-link" tabindex="' + (i === 0 ? '0' : '-1') + '">';
                    fHTML += '<img src="' + data[i].url + '" alt="' + (data[i].alt || '') + '" class="scw__frame-img" width="900" height="560" loading="' + (i < 2 ? 'eager' : 'lazy') + '" decoding="async">';
                    fHTML += '</a>';
                    fHTML += '<div class="scw__frame-grad" aria-hidden="true"></div>';
                    fHTML += '<div class="scw__frame-curve" aria-hidden="true"></div>';
                    fHTML += '</div>';
                    fHTML += '<div class="scw__frame-caption">';
                    fHTML += '<span class="scw__frame-cat">' + (data[i].country || '') + ' / ' + (data[i].destination || '') + '</span>';
                    fHTML += '<span class="scw__frame-title">' + (data[i].destination || '') + '</span>';
                    if (data[i].caption) {
                        fHTML += '<span class="scw__frame-desc">' + data[i].caption.substring(0, 80) + '</span>';
                    }
                    fHTML += '</div></div>';
                }
                stage.innerHTML = fHTML;

                // Timeline (first 6, unique labels)
                var tHTML = '<div class="scw__tl-line" aria-hidden="true"><span class="scw__tl-fill" data-scw-tl-fill></span></div>';
                var seen = {};
                var labels = [];
                for (var i = 0; i < Math.min(6, count); i++) {
                    var dest = data[i].destination || '';
                    var key = dest.toLowerCase();
                    if (dest && !seen[key]) { seen[key] = true; labels.push(dest); }
                    else { labels.push('Frame ' + String(i + 1).padStart(2, '0')); }
                }
                for (var i = 0; i < Math.min(6, count); i++) {
                    tHTML += '<button type="button" class="scw__tl-tick' + (i === 0 ? ' scw__tl-tick--active' : '') + '" data-scw-idx="' + i + '" aria-label="Frame ' + String(i + 1).padStart(2, '0') + ': ' + (data[i].destination || '') + '">';
                    tHTML += '<span class="scw__tl-dot"></span>';
                    tHTML += '<span class="scw__tl-cat">' + (labels[i] || '') + '</span>';
                    tHTML += '</button>';
                }
                tlTrack.innerHTML = tHTML;

                // Update panel text
                if (elChapter) elChapter.textContent = '01';
                if (elStatement) elStatement.textContent = data[0].destination;
                if (elLocation) elLocation.textContent = data[0].destination;
                if (elCaption) elCaption.textContent = data[0].caption ? data[0].caption.substring(0, 100) : '';

                updatePreviews(0);

                var tlFill = root.querySelector('[data-scw-tl-fill]');
                if (tlFill) tlFill.style.width = '0%';

                // Re-bind events
                bindEvents();

                // Re-init Fancybox
                initFancybox();

                if (reducedMotion) {
                    root.querySelectorAll('.scw__frame').forEach(function (f) { f.style.transition = 'none'; });
                }
            }

            /* ── Event binding ───────────────────────────────── */
            function bindEvents() {
                root.querySelectorAll('.scw__reel-frame').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        selectIdx(parseInt(btn.getAttribute('data-scw-idx')), true);
                    });
                });

                root.querySelectorAll('.scw__tl-tick').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        selectIdx(parseInt(btn.getAttribute('data-scw-idx')), true);
                    });
                });

                root.querySelectorAll('[data-scw-nav]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        btn.getAttribute('data-scw-nav') === 'next' ? selectNext() : selectPrev();
                    });
                });

                root.querySelectorAll('[data-scw-tl-nav]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        btn.getAttribute('data-scw-tl-nav') === 'next' ? selectNext() : selectPrev();
                    });
                });

                if (elExplore) elExplore.addEventListener('click', openLightbox);

                var viewAllBtn = root.querySelector('[data-scw-viewall]');
                if (viewAllBtn) viewAllBtn.addEventListener('click', openLightbox);
            }

            /* ── Category filter ─────────────────────────────── */
            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var cat = btn.getAttribute('data-scw-cat');
                    filterBtns.forEach(function (b) {
                        b.classList.remove('scw__filter-btn--active');
                        b.setAttribute('aria-selected', 'false');
                    });
                    btn.classList.add('scw__filter-btn--active');
                    btn.setAttribute('aria-selected', 'true');

                    if (cat === 'all') {
                        data = allData;
                    } else {
                        data = allData.filter(function (img) {
                            return (img.category || '').toLowerCase() === cat;
                        });
                    }
                    if (!data.length) data = allData;
                    renderWall();
                });
            });

            /* ── Keyboard navigation ─────────────────────────── */
            root.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') { e.preventDefault(); selectNext(); }
                else if (e.key === 'ArrowLeft') { e.preventDefault(); selectPrev(); }
            });

            /* ── Init ────────────────────────────────────────── */
            bindEvents();
            initFancybox();

            var tlFill = root.querySelector('[data-scw-tl-fill]');
            if (tlFill) tlFill.style.width = '0%';

            if (reducedMotion) {
                root.querySelectorAll('.scw__frame').forEach(function (f) { f.style.transition = 'none'; });
            }
        })();
        </script>
    </section>
    <!-- /Safari Cinema Wall -->
    <?php endif; ?>

    <!-- Testimonials & Reviews -->
    <!-- Customer Reviews -->
    <section class="section testi-sec-six pb-3" id="customer-reviews">
        <div class="container">
            <div class="wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header-six section-header-six--left">
                    <span class="badge badge-soft-primary rounded-pill mb-2">Traveller Stories</span>
                    <h2>Our Latest Customer Reviews</h2>
                </div>
            </div>

            <div class="text-center mb-4 wow fadeInUp" data-wow-delay="0.25s">
                <a href="https://g.page/r/CR7qe8CBnNH7EBM/review" target="_blank" rel="noopener" class="btn btn-primary">
                    Leave a Review <i class="isax isax-edit-2 ms-1"></i>
                </a>
            </div>

            <div class="testi-carousel-wrap wow fadeInUp" data-wow-delay="0.3s">
                <div class="owl-carousel testi-carousel">
                    <?php $__empty_1 = true; $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="testi-card">
                            <div class="testi-card__stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="ti ti-star-filled <?php echo e($i <= $testimonial->rating ? 'text-warning' : 'text-gray-3'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testi-card__text">"<?php echo e($testimonial->content); ?>"</p>
                            <a href="<?php echo e(route('page.show', 'contact')); ?>" class="testi-card__more">Read More</a>
                            <div class="testi-card__person">
                                <div class="avatar avtar-lg me-2">
                                    <?php if($testimonial->hasAvatar()): ?>
                                        <img src="<?php echo e($testimonial->avatarUrl('thumb')); ?>" class="rounded-circle" alt="<?php echo e($testimonial->name); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo e(asset('front-end/html/assets/img/users/user-28.jpg')); ?>" class="rounded-circle" alt="<?php echo e($testimonial->name); ?>">
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="testi-card__name mb-0"><?php echo e($testimonial->name); ?></h6>
                                    <?php if($testimonial->location): ?>
                                        <span class="testi-card__loc d-block text-muted"><?php echo e($testimonial->location); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="testi-card testi-card--empty">
                            <p class="text-muted mb-0">No reviews yet. Be the first to leave one!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    
    <?php
        $ctaTitle = Setting::get('cta_title', 'Your Tour in Africa, Our Passion!');
        $ctaSubtitle = Setting::get('cta_subtitle', 'Planning an Africa tour can be exciting, but we know you might have questions. Whether you are curious about the best time to visit, what to pack, or which safari suits your style, our expert team is ready to guide you.');
        $primaryLink = Setting::get('cta_button_link', '/contact');
        $primaryText = Setting::get('cta_button_text', 'Plan My Safari');
        $wa = Setting::get('whatsapp_number');
        $waLink = $wa ? ('https://wa.me/' . preg_replace('/[^0-9]/', '', $wa)) : '#';
        $ctaImage = Setting::get('cta_image') ?: asset('public/assets/images/safari-hero.jpg');
    ?>
    <section class="plan-cta" aria-labelledby="planCtaHeading">
        <svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute">
            <defs>
                <clipPath id="planCtaCurve" clipPathUnits="objectBoundingBox">
                    <path d="M0.07,0 C0,0.18 0.11,0.34 0.05,0.52 C0,0.70 0.09,0.86 0.06,1 L1,1 L1,0 Z"></path>
                </clipPath>
            </defs>
        </svg>

        <div class="plan-cta__media">
            <img src="<?php echo e($ctaImage); ?>" alt="Friendly Afro-Vertex local safari guide beside an open safari vehicle on the golden-hour savannah" class="plan-cta__img" width="900" height="1100" loading="lazy">
        </div>

        <div class="plan-cta__panel">
            <div class="plan-cta__decor" aria-hidden="true">
                <svg class="plan-cta__contours" viewBox="0 0 600 600" preserveAspectRatio="xMidYMid slice">
                    <g fill="none" stroke="#ffffff" stroke-width="1.5" transform="rotate(-12 300 320)">
                        <ellipse cx="300" cy="320" rx="110" ry="74"/>
                        <ellipse cx="300" cy="320" rx="175" ry="122"/>
                        <ellipse cx="300" cy="320" rx="240" ry="170"/>
                        <ellipse cx="300" cy="320" rx="305" ry="218"/>
                        <ellipse cx="300" cy="320" rx="370" ry="266"/>
                    </g>
                </svg>
                <svg class="plan-cta__compass" viewBox="0 0 200 200" aria-hidden="true">
                    <g fill="none" stroke="#d8a24a" stroke-width="2">
                        <circle cx="100" cy="100" r="92"/>
                        <circle cx="100" cy="100" r="74"/>
                        <circle cx="100" cy="100" r="6" fill="#d8a24a" stroke="none"/>
                        <line x1="100" y1="8" x2="100" y2="34"/>
                        <line x1="100" y1="166" x2="100" y2="192"/>
                        <line x1="8" y1="100" x2="34" y2="100"/>
                        <line x1="166" y1="100" x2="192" y2="100"/>
                    </g>
                    <path d="M100 30 L116 100 L100 170 L84 100 Z" fill="#d8a24a" opacity="0.55"/>
                    <path d="M30 100 L100 84 L170 100 L100 116 Z" fill="#d8a24a" opacity="0.22"/>
                </svg>
            </div>

            <div class="plan-cta__content">
                <span class="plan-cta__label">Plan With Local Experts</span>
                <span class="plan-cta__label-line" aria-hidden="true"></span>
                <h2 class="plan-cta__title" id="planCtaHeading"><?php echo e($ctaTitle); ?></h2>
                <p class="plan-cta__desc"><?php echo e($ctaSubtitle); ?></p>
                <div class="plan-cta__actions">
                    <a href="<?php echo e($primaryLink); ?>" class="plan-cta__btn plan-cta__btn--primary"><?php echo e($primaryText); ?></a>
                    <a href="<?php echo e($waLink); ?>" target="_blank" rel="noopener" class="plan-cta__btn plan-cta__btn--wa">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i> Chat on WhatsApp
                    </a>
                </div>
                <div class="plan-cta__trust">
                    <span class="plan-cta__trust-item">Local experts</span>
                    <span class="plan-cta__trust-sep" aria-hidden="true"></span>
                    <span class="plan-cta__trust-item">Tailor-made trips</span>
                    <span class="plan-cta__trust-sep" aria-hidden="true"></span>
                    <span class="plan-cta__trust-item">Fast response</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Horizontal Trustees (Client Logos) -->
    

        <section class="tours-faq-section">
            <div class="sfb-container">
                <?php echo $__env->make('frontend.partials.faq-section', ['faqSubject' => 'African'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </section>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-scripts'); ?>
    <?php echo $__env->make('frontend.partials.faq-script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <div class="start-date-calendar" id="startDateCalendar" role="dialog" aria-label="Choose start date" hidden>
        <div class="popover-pointer" aria-hidden="true"></div>
        <div class="sdc-header">
            <span class="sdc-title">Start Date</span>
            <button type="button" class="popover-close" data-popover-close aria-label="Close calendar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="sdc-nav">
            <button type="button" class="sdc-nav-btn" id="sdcPrev" aria-label="Previous month">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
            </button>
            <div class="sdc-month" id="sdcMonth" aria-live="polite">Month</div>
            <button type="button" class="sdc-nav-btn sdc-nav-btn--next" id="sdcNext" aria-label="Next month">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
            </button>
        </div>
        <div class="sdc-weekdays" aria-hidden="true">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>
        <div class="calendar-grid" id="sdcGrid" role="grid"></div>
        <label class="sdc-flex">
            <input type="checkbox" id="sdcFlexible">
            <span>My dates are somewhat flexible</span>
        </label>
    </div>

    <div class="travelers-popover" id="travellersPopover" role="dialog" aria-label="Travelers selector" hidden>
        <div class="popover-pointer" aria-hidden="true"></div>
        <div class="tp-header">
            <span class="tp-title">Travelers</span>
            <button type="button" class="popover-close" data-popover-close aria-label="Close travelers selector">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="tp-rows">
            <div class="tp-row">
                <div class="tp-row__text">
                    <span class="tp-row__label">Adults</span>
                    <span class="tp-row__age">(18+ years)</span>
                </div>
                <div class="tp-counter" role="group" aria-label="Adults count">
                    <button type="button" class="tp-counter__btn" data-step="-1" data-target="adults" aria-label="Decrease adults">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="tp-counter__value" id="tpAdults" aria-live="polite">2</span>
                    <button type="button" class="tp-counter__btn tp-counter__btn--plus" data-step="1" data-target="adults" aria-label="Increase adults">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="tp-row">
                <div class="tp-row__text">
                    <span class="tp-row__label">Children</span>
                    <span class="tp-row__age">(0–17 years)</span>
                </div>
                <div class="tp-counter" role="group" aria-label="Children count">
                    <button type="button" class="tp-counter__btn" data-step="-1" data-target="children" aria-label="Decrease children">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="tp-counter__value" id="tpChildren" aria-live="polite">0</span>
                    <button type="button" class="tp-counter__btn tp-counter__btn--plus" data-step="1" data-target="children" aria-label="Increase children">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="tp-footer">
            <button type="button" class="tp-done" id="tpDone">Done</button>
        </div>
    </div>

    <script>
    (function () {
        'use strict';

        var dateField = document.getElementById('afro-date');
        var travField = document.getElementById('afro-travellers');
        if (!dateField || !travField) return;

        var cal = document.getElementById('startDateCalendar');
        var pop = document.getElementById('travellersPopover');

        /* ── Shared popover manager ──────────────────────────── */
        var openPop = null;          // 'date' | 'trav' | null
        var returnFocusTo = null;
        var isMobile = function () { return window.matchMedia('(max-width: 767.98px)').matches; };

        function positionUnder(popEl, fieldWrap, pointer, alignRight) {
            if (isMobile()) { popEl.style.left = ''; popEl.style.top = ''; return; }
            var r = fieldWrap.getBoundingClientRect();
            var left = alignRight ? r.right + window.scrollX - popEl.offsetWidth : r.left + window.scrollX;
            var maxLeft = window.scrollX + document.documentElement.clientWidth - popEl.offsetWidth - 12;
            var minLeft = window.scrollX + 12;
            left = Math.min(Math.max(left, minLeft), Math.max(minLeft, maxLeft));
            popEl.style.left = left + 'px';
            popEl.style.top = (r.bottom + window.scrollY + 10) + 'px';
            if (pointer) {
                var center = (r.left + r.width / 2) + window.scrollX - left;
                pointer.style.left = Math.min(Math.max(center - 9, 14), popEl.offsetWidth - 32) + 'px';
            }
        }

        function open(which) {
            close(true);
            openPop = which;
            var el, trigger;
            if (which === 'date') {
                el = cal; trigger = dateField;
                cal.classList.add('is-open'); cal.hidden = false;
                positionUnder(cal, dateField.closest('.afro-search__input-wrap'), cal.querySelector('.popover-pointer'));
            } else {
                el = pop; trigger = travField;
                syncTempFromCommitted();
                pop.classList.add('is-open'); pop.hidden = false;
                positionUnder(pop, travField.closest('.afro-search__input-wrap'), pop.querySelector('.popover-pointer'), true);
            }
            trigger.setAttribute('aria-expanded', 'true');
            returnFocusTo = trigger;
            var f = el.querySelector('button:not([disabled]), input, [tabindex]:not([tabindex="-1"])');
            if (f) f.focus();
        }

        function close(silent) {
            if (!openPop) return;
            var trigger = openPop === 'date' ? dateField : travField;
            var el = openPop === 'date' ? cal : pop;
            el.hidden = true; el.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            openPop = null;
            if (!silent && returnFocusTo) { try { returnFocusTo.focus(); } catch (e) {} }
        }

        document.addEventListener('click', function (e) {
            if (!openPop) return;
            var el = openPop === 'date' ? cal : pop;
            var trigger = openPop === 'date' ? dateField : travField;
            if (!el.contains(e.target) && !trigger.closest('.afro-search__field').contains(e.target)) close();
        });

        /* ── Start Date calendar ─────────────────────────────── */
        var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var MONTHS_S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var DAYS_FULL = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var pad = function (n) { return String(n).padStart(2, '0'); };
        var toISO = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
        var norm = function (d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); };
        var today = norm(new Date());
        var todayISO = toISO(today);

        var grid = document.getElementById('sdcGrid');
        var monthLbl = document.getElementById('sdcMonth');
        var prevBtn = document.getElementById('sdcPrev');
        var nextBtn = document.getElementById('sdcNext');
        var flexChk = document.getElementById('sdcFlexible');
        var isoInput = document.getElementById('afro-date-value');
        var flexInput = document.getElementById('is-flexible-value');

        var viewY = today.getFullYear(), viewM = today.getMonth();

        function renderCalendar() {
            monthLbl.textContent = MONTHS[viewM] + ' ' + viewY;
            var viewingCurrent = viewY === today.getFullYear() && viewM === today.getMonth();
            prevBtn.disabled = viewingCurrent;

            grid.innerHTML = '';
            var first = new Date(viewY, viewM, 1);
            var offset = (first.getDay() + 6) % 7;              // Monday-first
            var start = new Date(viewY, viewM, 1 - offset);
            for (var i = 0; i < 42; i++) {
                (function () {
                    var d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                    var iso = toISO(d);
                    var past = norm(d) < today;
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'calendar-day' + (past ? ' is-past' : '') + (iso === todayISO ? ' is-today' : '');
                    b.textContent = d.getDate();
                    b.setAttribute('role', 'gridcell');
                    b.setAttribute('aria-label', DAYS_FULL[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear());
                    if (past) {
                        b.disabled = true;
                        b.tabIndex = -1;
                        b.setAttribute('aria-disabled', 'true');
                    } else {
                        b.dataset.iso = iso;
                        b.tabIndex = -1;
                        if (isoInput.value && isoInput.value === iso) b.classList.add('is-selected');
                        b.addEventListener('click', function () { pickDate(iso); });
                    }
                    grid.appendChild(b);
                })();
            }
            var sel = grid.querySelector('.is-selected:not(.is-past)');
            var firstAvail = grid.querySelector('.calendar-day:not(.is-past)');
            (sel || firstAvail || grid.firstElementChild).tabIndex = 0;
        }

        function pickDate(iso) {
            isoInput.value = iso;
            var parts = iso.split('-');
            dateField.value = parseInt(parts[2], 10) + ' ' + MONTHS_S[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
            renderCalendar();
            close();
        }

        prevBtn.addEventListener('click', function () {
            viewM--; if (viewM < 0) { viewM = 11; viewY--; }
            renderCalendar();
        });
        nextBtn.addEventListener('click', function () {
            viewM++; if (viewM > 11) { viewM = 0; viewY++; }
            renderCalendar();
        });

        flexChk.addEventListener('change', function () {
            flexInput.value = flexChk.checked ? '1' : '';
        });

        grid.addEventListener('keydown', function (e) {
            var btns = Array.prototype.slice.call(grid.querySelectorAll('.calendar-day'));
            var idx = btns.indexOf(document.activeElement);
            if (idx === -1) return;
            var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
            if (!step) return;
            e.preventDefault();
            var n = idx + step;
            while (n >= 0 && n < btns.length && btns[n].disabled) n += (step > 0 ? 1 : -1);
            if (n >= 0 && n < btns.length) { btns[idx].tabIndex = -1; btns[n].tabIndex = 0; btns[n].focus(); }
        });

        function openDate() {
            var sel = isoInput.value ? isoInput.value.split('-') : null;
            if (sel) { viewY = parseInt(sel[0], 10); viewM = parseInt(sel[1], 10) - 1; }
            else { viewY = today.getFullYear(); viewM = today.getMonth(); }
            renderCalendar();
            open('date');
        }

        dateField.addEventListener('click', openDate);
        dateField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDate(); }
        });
        cal.querySelectorAll('[data-popover-close]').forEach(function (b) {
            b.addEventListener('click', function () { close(); });
        });

        /* ── Travellers popover ──────────────────────────────── */
        var MAX_TOTAL = 12;
        var committed = { adults: 2, children: 0 };
        var temp = { adults: 2, children: 0 };
        var aOut = document.getElementById('tpAdults');
        var cOut = document.getElementById('tpChildren');
        var totalInput = document.getElementById('travellers-total');
        var adultsInput = document.getElementById('travellers-adults');
        var childrenInput = document.getElementById('travellers-children');
        var resetBtn = document.getElementById('afro-travellers-reset');

        function summary(a, c) {
            var p1 = a + ' ' + (a === 1 ? 'Adult' : 'Adults');
            var p2 = c > 0 ? ', ' + c + ' ' + (c === 1 ? 'Child' : 'Children') : '';
            return p1 + p2;
        }

        function paintTemp() {
            aOut.textContent = temp.adults;
            cOut.textContent = temp.children;
            var total = temp.adults + temp.children;
            pop.querySelectorAll('.tp-counter__btn').forEach(function (b) {
                var t = b.dataset.target, s = parseInt(b.dataset.step, 10);
                var v = temp[t];
                var dis = (s < 0) ? v <= (t === 'adults' ? 1 : 0)
                                  : v >= MAX_TOTAL || total >= MAX_TOTAL;
                b.disabled = dis;
            });
        }

        function paintCommitted() {
            travField.value = summary(committed.adults, committed.children);
            totalInput.value = committed.adults + committed.children;
            adultsInput.value = committed.adults;
            childrenInput.value = committed.children;
            resetBtn.hidden = (committed.adults === 2 && committed.children === 0);
        }

        function syncTempFromCommitted() {
            temp.adults = committed.adults;
            temp.children = committed.children;
            paintTemp();
        }

        pop.querySelectorAll('.tp-counter__btn').forEach(function (b) {
            b.addEventListener('click', function () {
                var t = b.dataset.target, d = parseInt(b.dataset.step, 10);
                var min = t === 'adults' ? 1 : 0;
                var nv = temp[t] + d;
                if (nv < min || nv > MAX_TOTAL || temp.adults + temp.children >= MAX_TOTAL && d > 0) return;
                temp[t] = nv;
                paintTemp();
            });
        });

        document.getElementById('tpDone').addEventListener('click', function () {
            committed.adults = temp.adults;
            committed.children = temp.children;
            paintCommitted();
            close();
        });

        resetBtn.addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            committed = { adults: 2, children: 0 };
            paintCommitted();
        });

        travField.addEventListener('click', function () { open('trav'); });
        travField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open('trav'); }
        });
        pop.querySelectorAll('[data-popover-close]').forEach(function (b) {
            b.addEventListener('click', function () { close(); });
        });

        /* Escape closes whichever popover is open */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && openPop) close();
        });

        /* Keep anchored on scroll / resize */
        var reposition = function () {
            if (!openPop || isMobile()) return;
            if (openPop === 'date') positionUnder(cal, dateField.closest('.afro-search__input-wrap'), cal.querySelector('.popover-pointer'), false);
            else positionUnder(pop, travField.closest('.afro-search__input-wrap'), pop.querySelector('.popover-pointer'), true);
        };
        window.addEventListener('scroll', reposition, { passive: true });
        window.addEventListener('resize', reposition);

        paintCommitted();
        paintTemp();
    })();

    /* Destination "Where To" searchable dropdown (new) */
    (function () {
        'use strict';
        <?php
            $countryNames = [
                'TZ' => 'Tanzania', 'KE' => 'Kenya', 'UG' => 'Uganda', 'RW' => 'Rwanda',
                'BW' => 'Botswana', 'ZA' => 'South Africa', 'NA' => 'Namibia', 'ZM' => 'Zambia',
                'MZ' => 'Mozambique', 'ET' => 'Ethiopia', 'CD' => 'DR Congo', 'SO' => 'Somalia',
            ];
            $destSearchData = \App\Models\Destination::orderBy('name')->get()->map(function ($d) use ($countryNames) {
                $type = $d->type ? ucwords($d->type) : 'Destination';
                $cat = $type;
                if ($d->country_code) {
                    $cn = $countryNames[$d->country_code] ?? strtoupper($d->country_code);
                    $cat .= ' (' . $cn . ')';
                }
                return ['name' => $d->name, 'slug' => $d->slug, 'cat' => $cat];
            })->values()->toArray();
        ?>
        var DESTINATIONS = <?php echo json_encode($destSearchData, 15, 512) ?>;
        var field = document.getElementById('destinationField');
        var input = document.getElementById('afro-destination');
        var hidden = document.getElementById('afro-destination-value');
        var dropdown = document.getElementById('destinationDropdown');
        var list = document.getElementById('destinationList');
        var clearBtn = document.getElementById('destinationClear');
        var closeBtn = document.getElementById('destinationClose');
        var icon = document.getElementById('destinationIcon');
        if (!field || !input) return;

        var ALL = { name: 'All Safari Destinations', slug: '', cat: 'Search Everywhere' };
        var activeIndex = -1;
        var items = [];

        function allItems() { return [ALL].concat(DESTINATIONS); }

        function filterItems(q) {
            q = (q || '').trim().toLowerCase();
            if (!q) return allItems();
            return allItems().filter(function (d) {
                return d.name.toLowerCase().indexOf(q) !== -1 || (d.cat || '').toLowerCase().indexOf(q) !== -1;
            });
        }

        function render(q) {
            items = filterItems(q);
            list.innerHTML = '';
            if (!items.length) {
                var empty = document.createElement('div');
                empty.className = 'destination-item destination-item--empty';
                empty.textContent = 'No destinations found';
                list.appendChild(empty);
                activeIndex = -1;
                return;
            }
            items.forEach(function (d, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'destination-item' + (i === activeIndex ? ' is-active' : '');
                btn.setAttribute('role', 'option');
                btn.dataset.index = i;
                var nm = document.createElement('span');
                nm.className = 'destination-item__name';
                nm.textContent = d.name;
                var ct = document.createElement('span');
                ct.className = 'destination-item__cat';
                ct.textContent = d.cat;
                btn.appendChild(nm);
                btn.appendChild(ct);
                btn.addEventListener('click', function () { select(d); });
                btn.addEventListener('mouseenter', function () { setActive(i); });
                list.appendChild(btn);
            });
        }

        function setActive(i) {
            activeIndex = i;
            var opts = list.querySelectorAll('.destination-item');
            opts.forEach(function (o) {
                o.classList.toggle('is-active', parseInt(o.dataset.index, 10) === i);
            });
            var el = opts[i];
            if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
        }

        function openDropdown() {
            render(input.value);
            dropdown.hidden = false;
            field.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        }
        function closeDropdown() {
            dropdown.hidden = true;
            field.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function select(d) {
            hidden.value = d.slug;
            input.value = d.slug === '' ? '' : d.name;
            if (d.slug === '') {
                clearBtn.hidden = true;
                icon.className = 'isax isax-location5 afro-search__icon';
            } else {
                clearBtn.hidden = false;
                icon.className = 'isax isax-close-circle5 afro-search__icon';
            }
            closeDropdown();
        }

        function clearDest() {
            hidden.value = '';
            input.value = '';
            input.placeholder = 'Where To';
            clearBtn.hidden = true;
            icon.className = 'isax isax-location5 afro-search__icon';
            input.focus();
        }

        input.addEventListener('focus', function () {
            input.setSelectionRange(input.value.length, input.value.length);
            openDropdown();
        });
        input.addEventListener('click', function (e) {
            e.stopPropagation();
            input.setSelectionRange(input.value.length, input.value.length);
            if (dropdown.hidden) openDropdown();
        });
        input.addEventListener('input', function () {
            if (dropdown.hidden) openDropdown();
            else render(input.value);
            activeIndex = 0;
            setActive(0);
        });
        input.addEventListener('keydown', function (e) {
            if (dropdown.hidden && (e.key === 'ArrowDown' || e.key === 'Enter')) { openDropdown(); return; }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeIndex < items.length - 1) setActive(activeIndex + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeIndex > 0) setActive(activeIndex - 1);
            } else if (e.key === 'Enter') {
                if (!dropdown.hidden) {
                    e.preventDefault();
                    if (items[activeIndex]) select(items[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                if (!dropdown.hidden) { e.preventDefault(); closeDropdown(); }
            }
        });

        clearBtn.addEventListener('click', function (e) { e.stopPropagation(); clearDest(); });
        closeBtn.addEventListener('click', function (e) { e.stopPropagation(); closeDropdown(); input.focus(); });
        field.addEventListener('click', function (e) {
            if (e.target === clearBtn || e.target === closeBtn) return;
            if (dropdown.hidden) openDropdown();
            if (e.target !== input) { try { input.focus(); } catch (err) {} }
        });
        dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('click', function (e) {
            if (dropdown.hidden) return;
            if (!field.contains(e.target)) closeDropdown();
        });
    })();

    /* Wishlist hearts on Featured Tours (unchanged behavior) */
    (function () {
        var KEY = 'av-favourite-tours';

        function readFavs() {
            try { return JSON.parse(localStorage.getItem(KEY)) || {}; }
            catch (e) { return {}; }
        }

        function writeFavs(favs) {
            try { localStorage.setItem(KEY, JSON.stringify(favs)); } catch (e) {}
        }

        function paint(btn, isFav) {
            btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
            btn.setAttribute('aria-label',
                (isFav ? 'Remove ' : 'Add ') + btn.dataset.tourTitle +
                (isFav ? ' from favourites' : ' to favourites'));
        }

        var favs = readFavs();

        document.querySelectorAll('.av-fav-btn').forEach(function (btn) {
            var id = btn.dataset.tourId;
            paint(btn, !!favs[id]);

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                favs = readFavs();
                if (favs[id]) { delete favs[id]; } else { favs[id] = Date.now(); }
                writeFavs(favs);
                paint(btn, !!favs[id]);
            });
        });
    })();
    </script>

    <script>
        (function () {
            var $grid   = $('#wwgt-grid');
            var $expand = $('#wwgt-expand');
            var $toggle = $('#wwgt-toggle');
            var $search = $('#wwgt-search');
            var $empty  = $('#wwgt-empty');

            if (!$grid.length) { return; }

            var expanded = false;

            function nameOf($card) {
                return ($card.data('name') || '').toLowerCase();
            }

            // Sort the complete grid alphabetically by country name.
            function sortAlphabetically() {
                var $cards = $grid.children('.wwgt-country').sort(function (a, b) {
                    return nameOf($(a)).localeCompare(nameOf($(b)));
                });
                $grid.append($cards);
            }

            // Live client-side search (only active once the list is expanded).
            function applySearch() {
                var q = $.trim($search.val().toLowerCase());
                var visible = 0;
                $grid.children('.wwgt-country').each(function () {
                    var $c = $(this);
                    var show = q === '' || nameOf($c).indexOf(q) !== -1;
                    $c.toggle(show);
                    if (show) { visible++; }
                });
                $empty.prop('hidden', visible !== 0);
            }

            $toggle.on('click', function () {
                expanded = !expanded;

                $grid.children('.wwgt-country--more').prop('hidden', !expanded);
                $expand.prop('hidden', !expanded);
                $toggle.attr('aria-expanded', expanded ? 'true' : 'false');
                $toggle.find('.wwgt__toggle-label').text(
                    expanded ? 'Show Fewer Countries' : 'View All Countries'
                );

                if (expanded) {
                    sortAlphabetically();
                    $search.val('').trigger('input');
                    $empty.prop('hidden', true);

                    var top = $grid.offset().top;
                    if (top < $(window).scrollTop() - 12) {
                        $('html, body').animate({ scrollTop: top - 24 }, 400);
                    }
                } else {
                    // Restore the always-visible cards that a search may have hidden.
                    $grid.children('.wwgt-country:not(.wwgt-country--more)').show();
                    $search.val('').trigger('input');
                    $empty.prop('hidden', true);
                }
            });

            $search.on('input', function () {
                if (expanded) { applySearch(); }
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-scripts'); ?>
<script>
    $(function () {
        var $c = $('.testi-carousel');
        if ($c.length && $.fn.owlCarousel) {
            $c.owlCarousel({
                items: 3,
                loop: false,
                margin: 28,
                nav: true,
                dots: true,
                slideBy: 1,
                navText: ['<span aria-hidden="true">&#8249;</span>', '<span aria-hidden="true">&#8250;</span>'],
                responsive: {
                    0:    { items: 1 },
                    768:  { items: 2 },
                    992:  { items: 3 }
                }
            });
        }
    });

    // ── Worldwide Group Travellers ──────────────────────────────
    (function () {
        var $grid   = $('#wwgt-grid');
        var $expand = $('#wwgt-expand');
        var $toggle = $('#wwgt-toggle');
        var $search = $('#wwgt-search');
        var $empty  = $('#wwgt-empty');

        if (!$grid.length) { return; }

        var expanded = false;

        function nameOf($card) {
            return ($card.data('name') || '').toLowerCase();
        }

        // Sort the complete grid alphabetically by country name.
        function sortAlphabetically() {
            var $cards = $grid.children('.wwgt-country').sort(function (a, b) {
                return nameOf($(a)).localeCompare(nameOf($(b)));
            });
            $grid.append($cards);
        }

        // Live client-side search (only active once the list is expanded).
        function applySearch() {
            var q = $.trim($search.val().toLowerCase());
            var visible = 0;
            $grid.children('.wwgt-country').each(function () {
                var $c = $(this);
                var show = q === '' || nameOf($c).indexOf(q) !== -1;
                $c.toggle(show);
                if (show) { visible++; }
            });
            $empty.prop('hidden', visible !== 0);
        }

        $toggle.on('click', function () {
            expanded = !expanded;

            $grid.children('.wwgt-country--more').prop('hidden', !expanded);
            $expand.prop('hidden', !expanded);
            $toggle.attr('aria-expanded', expanded ? 'true' : 'false');
            $toggle.find('.wwgt__toggle-label').text(
                expanded ? 'Show Fewer Countries' : 'View All Countries'
            );

            if (expanded) {
                sortAlphabetically();
                $search.val('').trigger('input');
                $empty.prop('hidden', true);

                var top = $grid.offset().top;
                if (top < $(window).scrollTop() - 12) {
                    $('html, body').animate({ scrollTop: top - 24 }, 400);
                }
            } else {
                // Restore the always-visible cards that a search may have hidden.
                $grid.children('.wwgt-country:not(.wwgt-country--more)').show();
                $search.val('').trigger('input');
                $empty.prop('hidden', true);
            }
        });

        $search.on('input', function () {
            if (expanded) { applySearch(); }
        });
    })();
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\home\index.blade.php ENDPATH**/ ?>