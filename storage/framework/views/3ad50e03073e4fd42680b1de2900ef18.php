<?php $__env->startSection('title', 'Blog List | Afro-Vertex Tours & Safaris'); ?>

<?php $__env->startSection('page-content'); ?>
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h2 class="breadcrumb-title mb-2">Blog List</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item">Pages</li>
                            <li class="breadcrumb-item active" aria-current="page">Blog List</li>
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

            <div class="row">
                <div class="col-xl-8 col-lg-8">
                    <div class="row">
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $SinglePost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="col-xl-12">
                                <div class="blog-item mb-4 wow fadeInUp" data-wow-delay="0.2s">
                                    <a href="<?php echo e(route('blog.show', $SinglePost->slug)); ?>" class="blog-img">
                                        <?php if($SinglePost->hasFeaturedImage()): ?>
                                            <img src="<?php echo e($SinglePost->featuredImageUrl('medium')); ?>" 
                                                 class="w-100" 
                                                 alt="<?php echo e($SinglePost->title); ?>">
                                        <?php else: ?>
                                            <img src="<?php echo e(asset('front-end/html/assets/img/blog/blog-placeholder.jpg')); ?>" 
                                                 class="w-100" 
                                                 alt="<?php echo e($SinglePost->title); ?>">
                                        <?php endif; ?>
                                    </a>

                                    <span class="badge bg-primary fs-13 fw-medium">
                                        <?php echo e($SinglePost->category->name ?? 'Uncategorized'); ?>

                                    </span>

                                    <div class="blog-info text-center">
                                        <div class="d-inline-flex align-items-center justify-content-center">
                                            <div class="d-inline-flex align-items-center border-end pe-3 me-3 mb-2">
                                                <a href="javascript:void(0);" class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm me-2">
                                                        <img src="<?php echo e(asset('front-end/html/assets/img/users/user-01.jpg')); ?>" 
                                                             class="rounded-circle border border-white" 
                                                             alt="Author">
                                                    </span>
                                                    <p>Admin</p> <!-- Replace with real author later -->
                                                </a>
                                            </div>
                                            <p class="text-white mb-2">
                                                <i class="isax isax-calendar-2 me-2"></i>
                                                <?php echo e($SinglePost->published_at ? $SinglePost->published_at->format('d M Y') : 'Draft'); ?>

                                            </p>
                                        </div>

                                        <h5>
                                            <a href="<?php echo e(route('blog.show', $SinglePost->slug)); ?>">
                                                <?php echo e($SinglePost->title); ?>

                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12 text-center py-5">
                                <h4>No blog posts found</h4>
                                <p class="text-muted">Check back soon or browse other sections.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <nav class="pagination-nav mb-4 mb-lg-0">
                        <?php echo e($posts->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                    </nav>
                    <!-- /Pagination -->

                </div>
                <!-- Sidebar -->
                <div class="col-xl-4 col-lg-4 theiaStickySidebar">

                    <!-- Search -->
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-search-normal-1 text-primary fs-16 me-2"></i>Search</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <form action="<?php echo e(route('blog.index')); ?>" method="GET">
                                <div class="bg-light-100 p-3 rounded border">
                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="isax isax-search-normal-1 fs-14"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Search" 
                                               value="<?php echo e(request('search')); ?>">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /Search -->

                    <!-- Categories -->
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-candle text-primary fs-16 me-2"></i>Categories</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-medium mb-0">
                                        <a href="<?php echo e(route('blog.index')); ?>?category=<?php echo e($cat->slug); ?>">
                                            <?php echo e($cat->name); ?>

                                        </a>
                                    </h6>
                                    <p>(<?php echo e($cat->posts()->where('status', 'published')->count()); ?>)</p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <!-- /Categories -->

                    <!-- Related Posts / Recent Posts -->
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="ti ti-brand-blogger text-primary fs-16 me-2"></i>Recent Posts</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <?php $__currentLoopData = $posts->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="blog-post mb-3">
                                    <div class="d-flex align-items-center">
                                        <a href="<?php echo e(route('blog.show', $recent->slug)); ?>" class="avatar avatar-xxl flex-shrink-0 me-2">
                                            <?php if($recent->hasFeaturedImage()): ?>
                                                <img src="<?php echo e($recent->featuredImageUrl('thumb')); ?>" 
                                                     class="rounded" alt="<?php echo e($recent->title); ?>">
                                            <?php else: ?>
                                                <img src="<?php echo e(asset('front-end/html/assets/img/blog/blog-placeholder.jpg')); ?>" 
                                                     class="rounded" alt="<?php echo e($recent->title); ?>">
                                            <?php endif; ?>
                                        </a>
                                        <div>
                                            <a href="<?php echo e(route('blog.show', $recent->slug)); ?>" class="two-line-ellipsis fs-14 fw-medium">
                                                <?php echo e($recent->title); ?>

                                            </a>
                                            <div class="d-flex align-items-center mt-2">
                                                <a href="javascript:void(0);" class="d-flex align-items-center border-end pe-2 me-2">
                                                    <span class="avatar avatar-xs me-1">
                                                        <img src="<?php echo e(asset('front-end/html/assets/img/users/user-01.jpg')); ?>" 
                                                             class="blog-user-img rounded-circle border border-light" alt="img">
                                                    </span>
                                                    <p class="fs-14 text-truncate">Admin</p>
                                                </a>
                                                <p class="fs-14 text-truncate">
                                                    <i class="isax isax-calendar-2 me-2"></i>
                                                    <?php echo e($recent->published_at ? $recent->published_at->format('d M Y') : 'Draft'); ?>

                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <!-- /Related Posts -->

                    <!-- Popular Tags (static for now – can make dynamic later) -->
                    <div class="card mb-0">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-tag text-primary fs-16 me-2"></i>Popular Tags</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3 pb-2">
                            <div class="d-flex align-items-center flex-wrap category-tag">
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Luxury</a>
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Travel</a>
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Nature</a>
                                <a href="#" class="badge badge-md fw-normal mb-2">Photography</a>
                            </div>
                        </div>
                    </div>
                    <!-- /Popular Tags -->

                </div>
                <!-- /Sidebar -->
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\blog\index.blade.php ENDPATH**/ ?>