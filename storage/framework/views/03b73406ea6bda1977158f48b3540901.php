

<?php $__env->startSection('title', $page->meta_title ?? 'About Us | Afro-Vertex Tours & Safaris'); ?>

<?php $__env->startSection('extra-head'); ?>
    <?php if($page->meta_description): ?>
        <meta name="description" content="<?php echo e($page->meta_description); ?>">
    <?php endif; ?>
    <?php if($page->meta_keywords): ?>
        <meta name="keywords" content="<?php echo e($page->meta_keywords); ?>">
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2"><?php echo e($page->title ?? 'About Us'); ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo e($page->title ?? 'About Us'); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumb -->

    <!-- Page Wrapper -->
    <div class="content">
        <div class="container">
            <!-- Hero / Intro Section -->
            <section class="about-hero mb-5">
                <div class="about-hero-img d-none justify-content-center align-items-center mb-4">
                    <?php if($page->hasHeroImage()): ?>
                        <img src="<?php echo e($page->heroUrl('medium')); ?>" 
                             alt="<?php echo e($page->title); ?>" 
                             class="img-fluid rounded shadow">
                    <?php else: ?>
                        <img src="<?php echo e(asset('assets/img/placeholder-page-hero.jpg')); ?>" alt="Default">
                    <?php endif; ?>
                </div>

                <div class="row justify-content-center">
                    <div>
                        <h1 class="display-5 fw-bold mb-4">
                            <?php echo e($page->extra_heading ?? 'Discover the Heart of East Africa with Afro-Vertex Tours'); ?>

                        </h1>
                        <p class="lead text-gray-700 mb-4">
                            <?php echo $page->extra_subheading ?? 'We are passionate local experts dedicated to creating authentic, responsible, and unforgettable travel experiences across Tanzania, Kenya, Uganda, and Rwanda.'; ?>

                        </p>
                    </div>
                </div>
            </section>

            <!-- Our Story -->
            <section class="section about-story bg-light-100 py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="about-img">
                            <?php if($page->hasStoryImage()): ?>
                                <img src="<?php echo e($page->storyUrl('medium')); ?>" 
                                     alt="<?php echo e($page->story_title ?? 'Our Story'); ?>" 
                                     class="img-fluid rounded shadow">
                            <?php else: ?>
                                <img src="<?php echo e(asset('assets/img/placeholder-about.jpg')); ?>" alt="About Us">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="about-content lh-lg">
                            <h2 class="mb-4"><?php echo e($page->story_title ?? 'Our Story & Passion'); ?></h2>
                            <div class="prose text-gray-700">
                                <?php echo $page->content; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Counters -->
            <?php if(!empty($page->stats_counters)): ?>
                <section class="section stats-counters-sec py-4">
                    <div class="row text-center g-4">
                        <?php $__currentLoopData = $page->stats_counters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $counter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6 col-md-3">
                                <h3 class="display-6 mb-1">
                                    <span class="counter"><?php echo e($counter['value'] ?? ''); ?></span><?php echo e($counter['suffix'] ?? '+'); ?>

                                </h3>
                                <p class="text-gray-600 mb-0"><?php echo e($counter['label'] ?? ''); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Why Choose Us -->
            <section class="section why-choose py-5">
                <div class="row justify-content-center">
                    <div class="col-xl-8 text-center mb-5">
                        <h2 class="mb-3">Why Travelers Choose Afro-Vertex</h2>
                        <p class="lead text-gray-600">
                            <?php echo e($page->why_choose_subtitle ?? 'We don’t just organize trips — we create lifelong memories with safety, authenticity, and care.'); ?>

                        </p>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Benefit 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-location-tick fs-32"></i>
                            </div>
                            <h5 class="mb-3">Local Expertise</h5>
                            <p class="text-gray-700 mb-0">
                                Born and raised in Tanzania — we know every trail, every camp, every hidden gem.
                            </p>
                        </div>
                    </div>

                    <!-- Benefit 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-shield-tick fs-32"></i>
                            </div>
                            <h5 class="mb-3">Safety First</h5>
                            <p class="text-gray-700 mb-0">
                                Fully licensed, insured, and trained guides — your well-being is our top priority.
                            </p>
                        </div>
                    </div>

                    <!-- Benefit 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-tree fs-32"></i>
                            </div>
                            <h5 class="mb-3">Responsible Travel</h5>
                            <p class="text-gray-700 mb-0">
                                We support local communities and protect wildlife — sustainable tourism is in our DNA.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Team -->
            <?php if(!empty($page->custom_data)): ?>
                <section class="section our-team py-5 bg-light-100">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 text-center mb-5">
                            <h2 class="mb-2">Our <span class="text-primary">Team</span></h2>
                            <p class="lead text-gray-600">The people behind every journey we plan.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <?php $__currentLoopData = $page->custom_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 text-center">
                                <div class="card h-100">
                                    <?php
                                        $memberPhoto = null;
                                        if (!empty($member['photo_image_id'])) {
                                            $memberPhoto = \App\Models\GalleryImage::find($member['photo_image_id']);
                                        }
                                    ?>
                                    <?php if($memberPhoto): ?>
                                        <img src="<?php echo e($memberPhoto->getUrl('medium')); ?>" class="card-img-top" alt="<?php echo e($member['name'] ?? ''); ?>" style="height: 240px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="mb-1"><?php echo e($member['name'] ?? ''); ?></h5>
                                        <?php if(!empty($member['role'])): ?>
                                            <p class="text-primary mb-2"><?php echo e($member['role']); ?></p>
                                        <?php endif; ?>
                                        <?php if(!empty($member['bio'])): ?>
                                            <p class="text-gray-600 mb-0 small"><?php echo e($member['bio']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Testimonials -->
            <?php if($testimonials->count() > 0): ?>
                <section class="section about-testimonials py-5">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 text-center mb-5">
                            <h2 class="mb-2">What's Our <span class="text-primary text-decoration-underline">User</span> Says</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center fs-12 mb-3">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="ti ti-star-filled <?php echo e($i <= $testimonial->rating ? 'text-warning' : 'text-gray-3'); ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-4">"<?php echo e($testimonial->content); ?>"</p>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avtar-lg me-2">
                                                <?php if($testimonial->hasAvatar()): ?>
                                                    <img src="<?php echo e($testimonial->avatarUrl('thumb')); ?>" class="rounded-circle" alt="<?php echo e($testimonial->name); ?>">
                                                <?php else: ?>
                                                    <img src="<?php echo e(asset('front-end/html/assets/img/users/user-28.jpg')); ?>" class="rounded-circle" alt="<?php echo e($testimonial->name); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo e($testimonial->name); ?></h6>
                                                <?php if($testimonial->location): ?>
                                                    <span class="d-block text-muted"><?php echo e($testimonial->location); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Call to Action -->
            <section class="section cta-section bg-primary text-white text-center py-5">
                <div class="container">
                    <h2 class="mb-4  text-white">Ready for Your Next Adventure?</h2>
                    <p class="lead mb-4">Let us craft the perfect journey for you — from Kilimanjaro summit to Zanzibar beaches.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="<?php echo e(route('tours.index')); ?>" class="btn btn-light btn-lg px-5">
                            Explore Tours
                        </a>
                        <a href="<?php echo e(route('page.show', 'contact')); ?>" class="btn btn-outline-light text-white btn-lg px-5">
                            Contact Us
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /Page Wrapper -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\pages\about.blade.php ENDPATH**/ ?>