

<?php
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $selectedCountryCodes = collect((array) request('countries'))->filter()->values();
    $selectedCountryNames = $countries->whereIn('code', $selectedCountryCodes->all())->pluck('name')->values();
    $selectedCategorySlugs = collect((array) request('categories'))->filter()->values();
    $selectedCategoryNames = $categories->whereIn('slug', $selectedCategorySlugs->all())->pluck('name')->values();

    if (request('category')) {
        $queryCategory = $categories->firstWhere('slug', request('category'));
        if ($queryCategory && !$selectedCategoryNames->contains($queryCategory->name)) {
            $selectedCategoryNames->push($queryCategory->name);
        }
    }

    $pageSubject = $headerDestination?->name
        ?? ($selectedCountryNames->count() === 1 ? $selectedCountryNames->first() : null)
        ?? $activeCategory?->name
        ?? ($selectedCategoryNames->count() === 1 ? $selectedCategoryNames->first() : null)
        ?? 'African';

    $mainTitle = $pageSubject !== 'African'
        ? $pageSubject . ' Safari Tours & Holidays'
        : 'Our Best All Tours & Safaris Packages';
    $introSource = $headerDestination?->description
        ?? $activeCategory?->description
        ?? Setting::get('tours_listing_intro');
    $introText = $introSource
        ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($introSource))), 540)
        : 'Compare handcrafted safari tours, wildlife holidays, mountain adventures and beach escapes across East Africa. Use the filters to narrow the route, travel style, comfort level and price that fit your trip.';

    $startDateIso = request('when', '');
    $startTimestamp = $startDateIso ? strtotime($startDateIso) : false;
    $startDateDisplay = $startTimestamp ? date('j M Y', $startTimestamp) : '';

    $adults = max(1, (int) request('adults', 2));
    $children = max(0, (int) request('children', 0));
    $travellersTotal = $adults + $children;
    $travellersText = $adults . ' ' . Str::plural('Adult', $adults) . ($children ? ', ' . $children . ' ' . Str::plural('Child', $children) : '');

    $ratingDisplay = $avgRating ? number_format($avgRating, 1) : '4.8';
    $reviewDisplay = $reviewCount ?: $tours->total();
    $operatorName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $operatorLogo = Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp');

    $durationData = $durationCounts->toArray();
    $durationUpper = max(array_keys($durationData ?: [14 => 1]));
    $durationUpper = max(7, min($durationUpper, 28));
    $durationMaxCount = max(array_values($durationData ?: [1]));
    $durationMinValue = request('duration_min');
    $durationMaxValue = request('duration_max');
    $priceMinValue = request('price_min');
    $priceMaxValue = request('price_max');
    $priceSliderMin = max(0, (int) $priceMin);
    $priceSliderMax = max($priceSliderMin + 1, (int) $priceMax);

    $parkDestinations = $allDestinations->filter(function ($destination) {
        $type = strtolower((string) $destination->type);
        return $destination->tours_count > 0 && !in_array($type, ['country', 'region', 'continent'], true);
    })->values();
    if ($parkDestinations->isEmpty()) {
        $parkDestinations = $allDestinations->where('tours_count', '>', 0)->values();
    }

    $removeQueryParam = function (string $key, $value = null) {
        $query = request()->query();
        unset($query['page']);

        if ($value !== null && isset($query[$key]) && is_array($query[$key])) {
            $query[$key] = array_values(array_filter($query[$key], fn ($item) => (string) $item !== (string) $value));
            if (empty($query[$key])) {
                unset($query[$key]);
            }
        } else {
            unset($query[$key]);
        }

        return route('tours.index', $query);
    };

    $hasSelectedFilters = request()->filled('destination')
        || request()->filled('duration_min')
        || request()->filled('duration_max')
        || request()->filled('price_min')
        || request()->filled('price_max')
        || request()->filled('level')
        || request()->filled('rating')
        || request()->filled('category')
        || request()->filled('categories')
        || request()->filled('activities')
        || request()->filled('accommodation')
        || request()->filled('luxury')
        || request()->filled('countries')
        || request()->filled('parks')
        || $activeCategory;
?>

<?php $__env->startSection('body-class', 'is-tours-listing'); ?>

<?php $__env->startSection('title', $mainTitle . ' | Afro-Vertex Tours & Safaris'); ?>

<?php $__env->startSection('extra-head'); ?>
    <meta name="description" content="<?php echo e(Str::limit($introText, 160)); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div class="sfb-listing-page">
        <nav class="sfb-breadcrumb" aria-label="Breadcrumb">
            <div class="sfb-container">
                <a href="<?php echo e(route('home')); ?>">Home</a>
                <span aria-hidden="true">&rsaquo;</span>
                <a href="<?php echo e(route('tours.index')); ?>">All Tours</a>
                <?php if($pageSubject !== 'African'): ?>
                    <span aria-hidden="true">&rsaquo;</span>
                    <span><?php echo e($pageSubject); ?> Tours</span>
                <?php endif; ?>
            </div>
        </nav>

        <div class="sfb-container sfb-main-wrap">
            <button type="button" class="sfb-mobile-filter-toggle" data-sfb-open-filters aria-controls="sfbFilterDrawer" aria-expanded="false">
                <i class="isax isax-filter" aria-hidden="true"></i>
                Filter Tours
            </button>

            <div class="sfb-drawer-backdrop" data-sfb-close-filters hidden></div>

            <div class="sfb-layout">
                <aside class="sfb-sidebar" id="sfbFilterDrawer" aria-label="Tour filters" data-sfb-filter-drawer>
                    <div class="sfb-sidebar__mobile-head">
                        <strong>Filter Tours</strong>
                        <button type="button" data-sfb-close-filters aria-label="Close filters">&times;</button>
                    </div>

                    <form method="GET" action="<?php echo e(route('tours.index')); ?>" class="sfb-filter-form" data-sfb-filter-form>
                        <?php if(request('search')): ?>
                            <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                        <?php endif; ?>

                        <section class="sfb-safari-panel" aria-labelledby="your-safari-title">
                            <h2 id="your-safari-title">Your Safari</h2>

                            <div class="sfb-safari-control">
                                <i class="isax isax-location5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <div class="sfb-whereto" data-sfb-wt>
                                    <div class="sfb-safari-field sfb-safari-field--button sfb-whereto__field<?php echo e($headerDestination ? ' has-value' : ''); ?>" data-sfb-wt-field>
                                        <span class="sfb-safari-field__label">Where To</span>
                                        <input type="text"
                                               class="sfb-safari-field__input sfb-whereto__input"
                                               placeholder="Where To"
                                               autocomplete="off"
                                               role="combobox"
                                               aria-expanded="false"
                                               aria-controls="sfbWheretoListbox"
                                               aria-autocomplete="list"
                                               data-sfb-wt-input
                                               value="<?php echo e($headerDestination?->name); ?>">
                                        <span class="sfb-whereto__affix">
                                            <svg class="sfb-whereto__search" data-sfb-wt-icon<?php echo e($headerDestination ? ' hidden' : ''); ?> width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                                            <button type="button" class="sfb-whereto__remove" data-sfb-wt-remove aria-label="Remove destination"<?php echo e($headerDestination ? '' : ' hidden'); ?>>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                    <input type="hidden" name="destination" data-sfb-wt-value value="<?php echo e(request('destination')); ?>">
                                </div>
                                <a class="sfb-add-link" href="#filter-countries">+ Add country, park or highlight</a>
                            </div>

                            <div class="sfb-safari-control">
                                <i class="isax isax-calendar-15 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="date" data-safpop-target="sfb-start-date-value" aria-haspopup="dialog" aria-expanded="false" aria-controls="startDateCalendar">
                                    <span class="sfb-safari-field__label">Start Date</span>
                                    <input type="text" class="sfb-safari-field__input" value="<?php echo e($startDateDisplay); ?>" placeholder="Start Date" readonly data-safpop-display aria-label="Start Date">
                                    <i class="isax isax-arrow-right-3" aria-hidden="true"></i>
                                </button>
                                <input type="hidden" name="when" id="sfb-start-date-value" value="<?php echo e($startDateIso); ?>">
                            </div>

                            <div class="sfb-safari-control">
                                <i class="isax isax-profile-2user5 sfb-safari-control__icon" aria-hidden="true"></i>
                                <button type="button" class="sfb-safari-field sfb-safari-field--button" data-safpop="trav" data-trav-total="sfb-travellers-total" data-trav-adults="sfb-travellers-adults" data-trav-children="sfb-travellers-children" aria-haspopup="dialog" aria-expanded="false" aria-controls="travellersPopover">
                                    <span class="sfb-safari-field__label">Travelers</span>
                                    <input type="text" class="sfb-safari-field__input" value="<?php echo e($travellersText); ?>" readonly data-safpop-display aria-label="Travelers">
                                    <span class="sfb-safari-field__remove" aria-hidden="true">&times;</span>
                                </button>
                                <input type="hidden" name="travellers" id="sfb-travellers-total" value="<?php echo e($travellersTotal); ?>">
                                <input type="hidden" name="adults" id="sfb-travellers-adults" value="<?php echo e($adults); ?>">
                                <input type="hidden" name="children" id="sfb-travellers-children" value="<?php echo e($children); ?>">
                            </div>

                            <button type="submit" class="sfb-show-tours" data-sfb-show-tours data-total="<?php echo e($totalPublishedTours); ?>">
                                Show <b data-sfb-show-count><?php echo e(number_format($totalPublishedTours)); ?></b> Tours
                            </button>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-length">
                            <h3 id="filter-length">Tour Length</h3>
                            <div class="sfb-histogram" aria-hidden="true">
                                <?php for($day = 1; $day <= $durationUpper; $day++): ?>
                                    <?php
                                        $count = (int) ($durationData[$day] ?? 0);
                                        $height = $durationMaxCount ? max(8, round(($count / $durationMaxCount) * 70)) : 8;
                                    ?>
                                    <span style="height: <?php echo e($height); ?>%"></span>
                                <?php endfor; ?>
                            </div>
                            <div class="sfb-range-stack">
                                <label>
                                    <span>Min Days</span>
                                    <input type="range" min="1" max="<?php echo e($durationUpper); ?>" value="<?php echo e($durationMinValue ?: 1); ?>" data-sfb-range="duration_min">
                                </label>
                                <label>
                                    <span>Max Days</span>
                                    <input type="range" min="1" max="<?php echo e($durationUpper); ?>" value="<?php echo e($durationMaxValue ?: $durationUpper); ?>" data-sfb-range="duration_max">
                                </label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="duration_min" min="1" max="<?php echo e($durationUpper); ?>" placeholder="Min" value="<?php echo e($durationMinValue); ?>" data-sfb-auto>
                                <input type="number" name="duration_max" min="1" max="<?php echo e($durationUpper); ?>" placeholder="Max" value="<?php echo e($durationMaxValue); ?>" data-sfb-auto>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-price">
                            <h3 id="filter-price">Price Range</h3>
                            <div class="sfb-range-stack">
                                <label>
                                    <span>Min Price</span>
                                    <input type="range" min="<?php echo e($priceSliderMin); ?>" max="<?php echo e($priceSliderMax); ?>" step="50" value="<?php echo e($priceMinValue ?: $priceSliderMin); ?>" data-sfb-range="price_min">
                                </label>
                                <label>
                                    <span>Max Price</span>
                                    <input type="range" min="<?php echo e($priceSliderMin); ?>" max="<?php echo e($priceSliderMax); ?>" step="50" value="<?php echo e($priceMaxValue ?: $priceSliderMax); ?>" data-sfb-range="price_max">
                                </label>
                            </div>
                            <div class="sfb-number-pair">
                                <input type="number" name="price_min" min="<?php echo e($priceSliderMin); ?>" max="<?php echo e($priceSliderMax); ?>" placeholder="<?php echo e(number_format($priceSliderMin)); ?>" value="<?php echo e($priceMinValue); ?>" data-sfb-auto>
                                <input type="number" name="price_max" min="<?php echo e($priceSliderMin); ?>" max="<?php echo e($priceSliderMax); ?>" placeholder="<?php echo e(number_format($priceSliderMax)); ?>" value="<?php echo e($priceMaxValue); ?>" data-sfb-auto>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-type">
                            <h3 id="filter-type">Tour Type</h3>
                            <div class="sfb-check-list">
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="categories[]" value="<?php echo e($category->slug); ?>" <?php echo e($selectedCategorySlugs->contains($category->slug) || request('category') === $category->slug || ($activeCategory?->slug === $category->slug) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($category->name); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-accommodation">
                            <h3 id="filter-accommodation">Accommodation</h3>
                            <div class="sfb-check-list">
                                <?php $__currentLoopData = ['camping' => 'Camping', 'lodge' => 'Lodge & Tented Camp']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="accommodation[]" value="<?php echo e($value); ?>" <?php echo e(in_array($value, (array) request('accommodation'), true) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($label); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-countries-title" id="filter-countries">
                            <h3 id="filter-countries-title">Countries</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                <?php $__currentLoopData = $countries->filter(fn ($country) => $country->code); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="countries[]" value="<?php echo e($country->code); ?>" <?php echo e($selectedCountryCodes->contains($country->code) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($country->name); ?></span>
                                        <em><?php echo e($country->count); ?></em>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>

                        <section class="sfb-filter-section" aria-labelledby="filter-parks">
                            <h3 id="filter-parks">Parks and Reserves</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                <?php $__currentLoopData = $parkDestinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parkDestination): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="parks[]" value="<?php echo e($parkDestination->id); ?>" <?php echo e(in_array($parkDestination->id, array_map('intval', (array) request('parks')), true) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($parkDestination->name); ?></span>
                                        <em><?php echo e($parkDestination->tours_count); ?></em>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>

                        <?php if($activities->isNotEmpty()): ?>
                        <section class="sfb-filter-section" aria-labelledby="filter-activities">
                            <h3 id="filter-activities">Activities</h3>
                            <div class="sfb-check-list sfb-check-list--scroll">
                                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="activities[]" value="<?php echo e($activity->id); ?>" <?php echo e(in_array($activity->id, array_map('intval', (array) request('activities')), true) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($activity->name); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>
                        <?php endif; ?>

                        <section class="sfb-filter-section" aria-labelledby="filter-luxury">
                            <h3 id="filter-luxury">Luxury Level</h3>
                            <div class="sfb-check-list">
                                <?php $__currentLoopData = ['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label>
                                        <input type="checkbox" name="luxury[]" value="<?php echo e($value); ?>" <?php echo e(in_array($value, (array) request('luxury'), true) ? 'checked' : ''); ?> data-sfb-auto>
                                        <span><?php echo e($label); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>

                        <div class="sfb-filter-actions">
                            <button type="submit">Apply Filters</button>
                            <a href="<?php echo e(route('tours.index')); ?>">Clear All Filters</a>
                        </div>
                    </form>
                </aside>

                <main class="sfb-results" id="sfb-results-start" aria-label="Safari tour results">
                    <header class="sfb-results-header">
                        <h1><?php echo e($mainTitle); ?></h1>
                        <div class="sfb-rating-line">
                            <span class="sfb-stars" aria-label="5 star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                            <strong><?php echo e($ratingDisplay); ?> /5</strong>
                            <a href="#reviews"><?php echo e(number_format($reviewDisplay)); ?> reviews</a>
                        </div>
                        <p><?php echo e($introText); ?></p>
                    </header>

                    <div class="sfb-selected-filters" aria-label="Selected filters">
                        <span>Selected filters:</span>
                        <?php if(!$hasSelectedFilters): ?>
                            <span class="sfb-selected-chip sfb-selected-chip--muted">All safaris</span>
                        <?php else: ?>
                            <?php if($headerDestination): ?>
                                <a class="sfb-selected-chip sfb-selected-chip--blue" href="<?php echo e($removeQueryParam('destination')); ?>"><?php echo e($headerDestination->name); ?> <b>&times;</b></a>
                            <?php endif; ?>
                            <?php if($activeCategory && !request('category')): ?>
                                <a class="sfb-selected-chip" href="<?php echo e(route('tours.index', request()->except(['page']))); ?>"><?php echo e($activeCategory->name); ?> <b>&times;</b></a>
                            <?php endif; ?>
                            <?php if(request('category')): ?>
                                <?php
                                    $queryCategory = $categories->firstWhere('slug', request('category'));
                                ?>
                                <?php if($queryCategory): ?>
                                    <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('category')); ?>"><?php echo e($queryCategory->name); ?> <b>&times;</b></a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php $__currentLoopData = $selectedCategorySlugs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $category = $categories->firstWhere('slug', $slug);
                                ?>
                                <?php if($category): ?>
                                    <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('categories', $slug)); ?>"><?php echo e($category->name); ?> <b>&times;</b></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(request()->filled('duration_min') || request()->filled('duration_max')): ?>
                                <a class="sfb-selected-chip" href="<?php echo e(route('tours.index', array_diff_key(request()->except(['page']), ['duration_min' => true, 'duration_max' => true]))); ?>"><?php echo e(request('duration_min', '1')); ?>-<?php echo e(request('duration_max', $durationUpper)); ?> days <b>&times;</b></a>
                            <?php endif; ?>
                            <?php if(request()->filled('price_min') || request()->filled('price_max')): ?>
                                <a class="sfb-selected-chip" href="<?php echo e(route('tours.index', array_diff_key(request()->except(['page']), ['price_min' => true, 'price_max' => true]))); ?>">$<?php echo e(number_format((int) request('price_min', $priceSliderMin))); ?>-$<?php echo e(number_format((int) request('price_max', $priceSliderMax))); ?> <b>&times;</b></a>
                            <?php endif; ?>
                            <?php $__currentLoopData = $selectedCountryCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $country = $countries->firstWhere('code', $code);
                                ?>
                                <?php if($country): ?>
                                    <a class="sfb-selected-chip sfb-selected-chip--blue" href="<?php echo e($removeQueryParam('countries', $code)); ?>"><?php echo e($country->name); ?> <b>&times;</b></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = (array) request('parks'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parkId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $park = $allDestinations->firstWhere('id', (int) $parkId);
                                ?>
                                <?php if($park): ?>
                                    <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('parks', $parkId)); ?>"><?php echo e($park->name); ?> <b>&times;</b></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = (array) request('activities'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activityId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $activity = $activities->firstWhere('id', (int) $activityId);
                                ?>
                                <?php if($activity): ?>
                                    <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('activities', $activityId)); ?>"><?php echo e($activity->name); ?> <b>&times;</b></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = (array) request('accommodation'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('accommodation', $style)); ?>"><?php echo e($style === 'camping' ? 'Camping' : 'Lodge & Tented Camp'); ?> <b>&times;</b></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = (array) request('luxury'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $luxury): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="sfb-selected-chip" href="<?php echo e($removeQueryParam('luxury', $luxury)); ?>"><?php echo e(['budget' => 'Budget', 'mid_range' => 'Mid-Range', 'luxury' => 'Luxury'][$luxury] ?? ucfirst($luxury)); ?> <b>&times;</b></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <a class="sfb-selected-chip sfb-selected-chip--clear" href="<?php echo e(route('tours.index')); ?>">Clear All Filters</a>
                        <?php endif; ?>
                    </div>

                    <div class="sfb-results-info">
                        <strong><?php echo e($tours->firstItem() ?: 0); ?>&ndash;<?php echo e($tours->lastItem() ?: 0); ?> of <?php echo e(number_format($tours->total())); ?></strong>
                        <span>Rankings are based on performance, relevance and payment. <a href="#sfb-ranking-note">Learn more</a></span>
                    </div>

                    <div class="sfb-tour-grid">
                        <?php $__empty_1 = true; $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $SingleTour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $tourDestinations = $SingleTour->destinations->sortBy('pivot.order')->values();
                                $tourDestinationText = $tourDestinations->take(4)->pluck('name')->implode(', ');
                                $extraDestinations = max(0, $tourDestinations->count() - 4);
                                $tourLevel = trim(str_replace('_', ' ', (string) $SingleTour->tour_level));
                                $tourType = $SingleTour->categories->take(2)->pluck('name')->implode(', ');
                                $cardRating = $tourRatings->get($SingleTour->id);
                                $cardRatingValue = $cardRating ? number_format((float) $cardRating->avg_rating, 1) : null;
                                $cardReviewCount = $cardRating ? (int) $cardRating->review_count : 0;
                                $durationText = $SingleTour->duration_days
                                    ? $SingleTour->duration_days . ' ' . Str::plural('day', $SingleTour->duration_days) . ($SingleTour->duration_nights ? ' / ' . $SingleTour->duration_nights . ' ' . Str::plural('night', $SingleTour->duration_nights) : '')
                                    : 'Flexible duration';
                            ?>
                            <article class="sfb-tour-card">
                                <a class="sfb-tour-card__full-link" href="<?php echo e(route('tour.show', $SingleTour->slug)); ?>" aria-label="View <?php echo e($SingleTour->cardTitle()); ?>"></a>
                                <div class="sfb-tour-card__image-wrap">
                                    <img src="<?php echo e($SingleTour->cardImageUrl('medium')); ?>" alt="<?php echo e($SingleTour->cardTitle()); ?>" loading="<?php echo e($loop->index < 4 ? 'eager' : 'lazy'); ?>">
                                    <div class="sfb-tour-card__gradient" aria-hidden="true"></div>
                                    <button type="button" class="sfb-tour-card__heart" data-sfb-wishlist aria-label="Save tour">
                                        <i class="bi bi-heart" aria-hidden="true"></i>
                                    </button>
                                    <h2><?php echo e($SingleTour->cardTitle()); ?></h2>
                                </div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid">
                                        <div>
                                            <span>Duration</span>
                                            <strong><?php echo e($durationText); ?></strong>
                                        </div>
                                        <div>
                                            <span>Destination</span>
                                            <strong><?php echo e($tourDestinationText ?: 'East Africa'); ?><?php echo e($extraDestinations ? ' +' . $extraDestinations : ''); ?></strong>
                                        </div>
                                        <div>
                                            <span>Accommodation</span>
                                            <strong><?php echo e($tourLevel ? Str::title($tourLevel) : 'Tailor-made comfort'); ?></strong>
                                        </div>
                                        <div>
                                            <span>Tour Type</span>
                                            <strong><?php echo e($tourType ?: ($SingleTour->is_group_departure ? 'Group departure' : 'Private safari')); ?></strong>
                                        </div>
                                    </div>

                                    <div class="sfb-tour-card__operator">
                                        <img src="<?php echo e($operatorLogo); ?>" alt="" loading="lazy">
                                        <div>
                                            <span>Operated by</span>
                                            <strong><?php echo e($operatorName); ?></strong>
                                        </div>
                                    </div>

                                    <div class="sfb-tour-card__footer">
                                        <div class="sfb-tour-card__rating">
                                            <span class="sfb-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                            <?php if($cardRatingValue): ?>
                                                <strong><?php echo e($cardRatingValue); ?></strong>
                                                <span><?php echo e($cardReviewCount); ?> <?php echo e(Str::plural('review', $cardReviewCount)); ?></span>
                                            <?php else: ?>
                                                <strong>New</strong>
                                                <span>No reviews yet</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="sfb-tour-card__price">
                                            <?php $cardPrice = $fromPrices[$SingleTour->id] ?? null; ?>
                                            <?php if($cardPrice): ?>
                                                <span>From</span>
                                                <strong>$<?php echo e(number_format($cardPrice['amount'], 0)); ?></strong>
                                                <em>pp</em>
                                            <?php else: ?>
                                                <strong>Request a Quote</strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <a href="<?php echo e(route('tour.show', $SingleTour->slug)); ?>" class="sfb-tour-card__cta">View Tour</a>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="sfb-empty-results">
                                <h2>No tours found matching your filters.</h2>
                                <p>Try clearing one or more filters to see more safari options.</p>
                                <a href="<?php echo e(route('tours.index')); ?>">Clear All Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php echo $__env->make('frontend.tours.partials.pagination', ['paginator' => $tours], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <p class="sfb-ranking-note" id="sfb-ranking-note">Ranking signals combine tour relevance, destination match, current availability, editorial ordering and promotional placement.</p>

                    <?php if($activeCategory): ?>
                        <?php $__currentLoopData = $activeCategory->sectionsBelowGrid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <section class="sfb-category-section">
                                <?php if($section->image_id): ?>
                                    <img src="<?php echo e($section->imageUrl('medium')); ?>" alt="<?php echo e($section->title); ?>" loading="lazy">
                                <?php endif; ?>
                                <div>
                                    <?php if($section->title): ?>
                                        <h2><?php echo e($section->title); ?></h2>
                                    <?php endif; ?>
                                    <?php if($section->content): ?>
                                        <?php echo $section->content; ?>

                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

                    <section class="tours-faq-section">
            <div class="sfb-container">
                <?php echo $__env->make('frontend.partials.faq-section', ['faqSubject' => $pageSubject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </section>

<?php echo $__env->make('frontend.partials.safari-popovers', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('frontend.partials.whereto-popover', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-scripts'); ?>
    <script>
        (function () {
            'use strict';

            var form = document.querySelector('[data-sfb-filter-form]');
            var drawer = document.querySelector('[data-sfb-filter-drawer]');
            var backdrop = document.querySelector('[data-sfb-close-filters].sfb-drawer-backdrop');
            var openButton = document.querySelector('[data-sfb-open-filters]');
            var submitTimer = null;

            function submitSoon() {
                if (!form) return;
                window.clearTimeout(submitTimer);
                submitTimer = window.setTimeout(function () {
                    if (form.requestSubmit) form.requestSubmit();
                    else form.submit();
                }, 250);
            }

            if (form) {
                form.querySelectorAll('[data-sfb-auto]').forEach(function (field) {
                    field.addEventListener('change', submitSoon);
                });

                form.querySelectorAll('[data-sfb-range]').forEach(function (range) {
                    var target = form.querySelector('[name="' + range.dataset.sfbRange + '"]');
                    if (!target) return;

                    range.addEventListener('input', function () {
                        target.value = range.value;
                    });

                    range.addEventListener('change', submitSoon);
                });
            }

            var calendar = document.getElementById('startDateCalendar');
            if (calendar) {
                calendar.addEventListener('click', function (event) {
                    if (event.target.closest('.calendar-day[data-iso]')) {
                        window.setTimeout(submitSoon, 50);
                    }
                });
            }

            var travellersDone = document.getElementById('tpDone');
            if (travellersDone) {
                travellersDone.addEventListener('click', function () {
                    window.setTimeout(submitSoon, 50);
                });
            }

            function openDrawer() {
                if (!drawer || !backdrop || !openButton) return;
                drawer.classList.add('is-open');
                backdrop.hidden = false;
                openButton.setAttribute('aria-expanded', 'true');
                document.body.classList.add('sfb-filter-lock');
            }

            function closeDrawer() {
                if (!drawer || !backdrop || !openButton) return;
                drawer.classList.remove('is-open');
                backdrop.hidden = true;
                openButton.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('sfb-filter-lock');
            }

            if (openButton) openButton.addEventListener('click', openDrawer);
            document.querySelectorAll('[data-sfb-close-filters]').forEach(function (button) {
                button.addEventListener('click', closeDrawer);
            });

            document.querySelectorAll('[data-sfb-wishlist]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    button.classList.toggle('is-active');
                    var icon = button.querySelector('i');
                    if (icon) icon.className = button.classList.contains('is-active') ? 'bi bi-heart-fill' : 'bi bi-heart';
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeDrawer();
            });

            /* ── Pagination: loading state + smooth scroll to results ── */
            var resultsArea = document.getElementById('sfb-results-start');
            var SCROLL_OFFSET = 90;

            function scrollToResults(behavior) {
                if (!resultsArea) return;
                var top = resultsArea.getBoundingClientRect().top + window.scrollY - SCROLL_OFFSET;
                window.scrollTo({ top: Math.max(top, 0), behavior: behavior || 'auto' });
            }

            function markLoading() {
                if (resultsArea) resultsArea.classList.add('is-loading');
                document.body.classList.add('sfb-page-transition');
            }

            function clearLoading() {
                if (resultsArea) resultsArea.classList.remove('is-loading');
                document.body.classList.remove('sfb-page-transition');
            }

            document.querySelectorAll('[data-sfb-page-link]').forEach(function (link) {
                link.addEventListener('click', function () {
                    try { sessionStorage.setItem('sfbPageLoading', '1'); } catch (e) {}
                    markLoading();
                    window.setTimeout(function () { scrollToResults('smooth'); }, 0);
                });
            });

            try {
                if (sessionStorage.getItem('sfbPageLoading') === '1') {
                    sessionStorage.removeItem('sfbPageLoading');
                    scrollToResults('auto');
                }
            } catch (e) {}

            clearLoading();
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) clearLoading();
            });

            /* ── FAQ: click-scroll, scrollspy, mobile accordion ──────── */
            var faqSection = document.querySelector('[data-sfb-faq]');
            if (faqSection) {
                var faqLinks = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-link]'));
                var faqItems = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-item]'));
                var faqDesktop = window.matchMedia('(min-width: 992px)');
                var FAQ_OFFSET = 110;

                function setActive(index) {
                    var key = String(index);
                    faqItems.forEach(function (item) {
                        item.classList.toggle('is-active', item.dataset.sfbFaqItem === key);
                    });
                    faqLinks.forEach(function (link) {
                        link.classList.toggle('is-active', link.dataset.sfbFaqLink === key);
                    });
                }

                function scrollToItem(item, smooth) {
                    if (!item) return;
                    var top = item.getBoundingClientRect().top + window.scrollY - FAQ_OFFSET;
                    window.scrollTo({ top: Math.max(top, 0), behavior: smooth ? 'smooth' : 'auto' });
                }

                function openItem(item) {
                    item.classList.add('is-open');
                    item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'true');
                    var body = item.querySelector('.sfb-faq-item__body');
                    body.style.maxHeight = body.scrollHeight + 'px';
                }

                function closeItem(item) {
                    item.classList.remove('is-open');
                    item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'false');
                    item.querySelector('.sfb-faq-item__body').style.maxHeight = '';
                }

                function applyMode() {
                    if (faqDesktop.matches) {
                        faqItems.forEach(closeItem);
                    } else {
                        faqItems.forEach(function (item, i) {
                            if (i === 0) openItem(item); else closeItem(item);
                        });
                    }
                }

                faqItems.forEach(function (item) {
                    item.querySelector('.sfb-faq-item__head').addEventListener('click', function () {
                        var index = parseInt(item.dataset.sfbFaqItem, 10);
                        if (!faqDesktop.matches) {
                            var wasOpen = item.classList.contains('is-open');
                            faqItems.forEach(closeItem);
                            if (!wasOpen) openItem(item);
                        } else {
                            setActive(isNaN(index) ? 0 : index);
                        }
                    });
                });

                faqLinks.forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        var index = parseInt(link.dataset.sfbFaqLink, 10);
                        if (isNaN(index)) return;
                        setActive(index);
                        var target = faqItems[index];
                        if (faqDesktop.matches) {
                            scrollToItem(target, true);
                        } else {
                            faqItems.forEach(closeItem);
                            openItem(target);
                            requestAnimationFrame(function () { scrollToItem(target, true); });
                        }
                    });
                });

                var faqTicking = false;
                window.addEventListener('scroll', function () {
                    if (!faqDesktop.matches || faqTicking) return;
                    faqTicking = true;
                    requestAnimationFrame(function () {
                        faqTicking = false;
                        var current = 0;
                        for (var i = 0; i < faqItems.length; i++) {
                            if (faqItems[i].getBoundingClientRect().top - FAQ_OFFSET <= 140) current = i;
                        }
                        setActive(current);
                    });
                }, { passive: true });

                applyMode();
                var onModeChange = function () { applyMode(); };
                if (faqDesktop.addEventListener) faqDesktop.addEventListener('change', onModeChange);
                else faqDesktop.addListener(onModeChange);
            }
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\tours\index.blade.php ENDPATH**/ ?>