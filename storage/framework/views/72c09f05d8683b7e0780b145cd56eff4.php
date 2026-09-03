<?php $__env->startSection('title', $page->meta_title ?? ($page->title . ' | ' . \App\Models\Setting::get('site_name', 'Afro-Vertex Tours & Safaris'))); ?>

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
                    <h1 class="breadcrumb-title mb-2"><?php echo e($page->title); ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo e($page->title); ?></li>
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
            <!-- Optional hero image — most content-only pages (Terms, Refund Policy)
                 won't set one, so this simply doesn't render for them. -->
            <?php if($page->hasHeroImage()): ?>
                <div class="text-center mb-5 h-200">
                    <img src="<?php echo e($page->heroUrl('medium')); ?>" alt="<?php echo e($page->title); ?>" class="img-fluid rounded shadow">
                </div>
            <?php endif; ?>

            <?php if($page->extra_heading): ?>
                <h1 class="mb-3"><?php echo e($page->extra_heading); ?></h1>
            <?php endif; ?>

            <?php if($page->extra_subheading): ?>
                <p class="lead text-gray-700 mb-4"><?php echo $page->extra_subheading; ?></p>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="prose text-gray-700">
                        <?php echo $page->content; ?>

                    </div>
                </div>
            </div>

            <!-- Optional CTA — uses the page's own cta_text/cta_link fields (already
                 in the admin form's "General extra fields" section) rather than
                 hardcoded copy, so it's meaningful for whichever page uses it. -->
            <?php if($page->cta_text && $page->cta_link): ?>
                <div class="text-center mt-5 pt-4 border-top">
                    <a href="<?php echo e($page->cta_link); ?>" class="btn btn-primary btn-lg px-5">
                        <?php echo e($page->cta_text); ?>

                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- /Page Wrapper -->
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\pages\show.blade.php ENDPATH**/ ?>