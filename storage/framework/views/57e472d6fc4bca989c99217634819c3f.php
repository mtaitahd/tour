

<?php $__env->startSection('title', $tour->meta_title ?? $tour->title . ' | Afro-Vertex Tours & Safaris'); ?>
<?php $__env->startSection('extra-head'); ?>
    <meta name="description" content="<?php echo e($tour->meta_description ?? Str::limit(strip_tags($tour->overview), 160)); ?>">
    <meta name="keywords" content="<?php echo e($tour->meta_keywords ?? 'safari, kilimanjaro, serengeti, tanzania tour'); ?>">
    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="<?php echo e($tour->title); ?>">
    <meta property="og:description" content="<?php echo e(Str::limit(strip_tags($tour->overview), 200)); ?>">
    <?php if($tour->hero_image): ?>
        <meta property="og:image" content="<?php echo e(asset($tour->hero_image)); ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo e(request()->url()); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <!-- Hero / Banner -->
    <?php if($tour->hero_image): ?>
        <div class="tour-hero mb-5">
            <img src="<?php echo e(asset($tour->hero_image)); ?>" alt="<?php echo e($tour->title); ?>" class="img-fluid w-100 rounded" style="height: 500px; object-fit: cover;">
            <div class="hero-overlay text-white text-center">
                <h1 class="display-4 fw-bold mb-3"><?php echo e($tour->title); ?></h1>
                <p class="lead">
                    <i class="isax isax-calendar-1 me-2"></i> <?php echo e($tour->duration_days); ?> Days / <?php echo e($tour->duration_nights ?? $tour->duration_days - 1); ?> Nights
                    <span class="mx-3">|</span>
                    <i class="isax isax-dollar-square me-2"></i> From $<?php echo e(number_format($tour->base_price, 0)); ?> <?php echo e($tour->currency); ?>

                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Overview -->
            <?php if($tour->overview): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Overview</h2>
                    <div class="prose max-w-none">
                        <?php echo $tour->overview; ?>

                    </div>
                </section>
            <?php endif; ?>

            <!-- Highlights -->
            <?php if($tour->highlights && count($tour->highlights) > 0): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Highlights</h2>
                    <ul class="list-unstyled highlight-list">
                        <?php $__currentLoopData = $tour->highlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $highlight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="d-flex align-items-start mb-3">
                                <i class="isax isax-tick-circle5 text-primary fs-24 me-3 mt-1"></i>
                                <span><?php echo e($highlight); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </section>
            <?php endif; ?>

            <!-- Itinerary (Accordion style) -->
            <?php if($tour->itinerary && count($tour->itinerary) > 0): ?>
                <section class="mb-5">
                    <h2 class="mb-4">Detailed Itinerary</h2>
                    <div class="accordion accordion-flush" id="itineraryAccordion">
                        <?php $__currentLoopData = $tour->itinerary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo e($day); ?>">
                                    <button class="accordion-button <?php echo e($day == 0 ? '' : 'collapsed'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($day); ?>" aria-expanded="<?php echo e($day == 0 ? 'true' : 'false'); ?>">
                                        Day <?php echo e($day + 1); ?>: <?php echo e($item['title'] ?? 'Day ' . ($day + 1)); ?>

                                    </button>
                                </h2>
                                <div id="collapse<?php echo e($day); ?>" class="accordion-collapse collapse <?php echo e($day == 0 ? 'show' : ''); ?>" aria-labelledby="heading<?php echo e($day); ?>">
                                    <div class="accordion-body">
                                        <?php echo $item['description'] ?? ''; ?>

                                        <?php if(!empty($item['accommodation'])): ?>
                                            <p class="mt-3 mb-1"><strong>Accommodation:</strong> <?php echo e($item['accommodation']); ?></p>
                                        <?php endif; ?>
                                        <?php if(!empty($item['meals'])): ?>
                                            <p class="mb-1"><strong>Meals:</strong> <?php echo e($item['meals']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Inclusions & Exclusions -->
            <div class="row g-4">
                <?php if($tour->inclusions && count($tour->inclusions) > 0): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="card-title mb-4">What's Included</h3>
                                <ul class="list-unstyled">
                                    <?php $__currentLoopData = $tour->inclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="mb-2"><i class="isax isax-tick-circle5 text-success me-2"></i> <?php echo e($inc); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($tour->exclusions && count($tour->exclusions) > 0): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="card-title mb-4">What's Excluded</h3>
                                <ul class="list-unstyled">
                                    <?php $__currentLoopData = $tour->exclusions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="mb-2"><i class="isax isax-close-circle5 text-danger me-2"></i> <?php echo e($exc); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map (if exists) -->
            <?php if($tour->map_data): ?>
                <section class="mt-5">
                    <h2 class="mb-4">Tour Route Map</h2>
                    <div id="tour-map" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
                </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar / CTA -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-4">
                        <h3 class="mb-3">Book This Tour</h3>
                        <p class="fs-4 fw-bold text-primary mb-1">From $<?php echo e(number_format($tour->base_price, 0)); ?> <?php echo e($tour->currency); ?></p>
                        <p class="text-muted mb-4">per person</p>

                        <a href="<?php echo e(route('booking.create', $tour->slug)); ?>" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="isax isax-reserve-ticket me-2"></i> Book Now
                        </a>

                        <a href="https://wa.me/255YOURNUMBER?text=I'm%20interested%20in%20<?php echo e(urlencode($tour->title)); ?>" target="_blank" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp Inquiry
                        </a>

                        <a href="<?php echo e(route('page.show', 'contact')); ?>" class="btn btn-outline-primary w-100">
                            Send Inquiry via Form
                        </a>

                        <!-- Quick info -->
                        <ul class="list-group list-group-flush mt-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Duration</span>
                                <strong><?php echo e($tour->duration_days); ?> Days</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Difficulty</span>
                                <strong><?php echo e(ucfirst($tour->physical_rating)); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Style</span>
                                <strong><?php echo e(ucfirst(str_replace('_', ' ', $tour->tour_level))); ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-scripts'); ?>
    <?php if($tour->map_data): ?>
        <!-- Google Maps or Leaflet script -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var mapData = <?php echo json_encode($tour->map_data, 15, 512) ?>;
                if (mapData && mapData.center && mapData.zoom) {
                    var map = L.map('tour-map').setView([mapData.center.lat, mapData.center.lng], mapData.zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // Add markers/polyline if present
                    if (mapData.markers) {
                        mapData.markers.forEach(marker => {
                            L.marker([marker.lat, marker.lng]).addTo(map)
                                .bindPopup(marker.popup || 'Destination');
                        });
                    }
                }
            });
        </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\show.blade.php ENDPATH**/ ?>