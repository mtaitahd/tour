<?php $__env->startSection('title', 'Accommodations | Afro-Vertex Tours & Safaris'); ?>
<?php $__env->startSection('extra-head'); ?>
    <meta name="description" content="Browse every boutique lodge, tented camp, and beach resort handpicked by Afro-Vertex Tours & Safaris. Comfort, character, and a front-row seat to the wild.">
    <meta name="keywords" content="east africa accommodation, safari lodges, tented camps, beach resorts, tanzania hotels, kenya safari lodges, zanzibar resorts">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">All Accommodations</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active">Accommodations</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Accommodations Grid -->
    <div class="content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="fs-24 fw-bold mb-0">Where You'll Stay</h2>
                <span class="text-muted">Showing <?php echo e($accommodations->firstItem()); ?>-<?php echo e($accommodations->lastItem()); ?> of <?php echo e($accommodations->total()); ?></span>
            </div>

            <div class="featured-tours-grid">
                <?php $__empty_1 = true; $__currentLoopData = $accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-center py-5">
                        <h4>No accommodations available yet.</h4>
                        <a href="<?php echo e(route('home')); ?>" class="btn btn-primary mt-3">Back to Home</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="mt-5">
                <?php echo e($accommodations->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>

    <!-- Accommodation Detail Modals -->
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\accommodations\index.blade.php ENDPATH**/ ?>