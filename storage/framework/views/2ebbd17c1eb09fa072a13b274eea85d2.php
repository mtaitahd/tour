<?php $__env->startSection('title', $post->meta_title ?? $post->title . ' | Afro-Vertex Tours Blog'); ?>

<?php $__env->startSection('extra-head'); ?>
    <meta name="description" content="<?php echo e($post->meta_description ?? Str::limit(strip_tags($post->content), 160)); ?>">
    <meta name="keywords" content="<?php echo e($post->meta_keywords ?? 'travel blog, safari tips, kilimanjaro, zanzibar, africa travel'); ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo e($post->title); ?>">
    <meta property="og:description" content="<?php echo e(Str::limit(strip_tags($post->content), 200)); ?>">
    <?php if($post->hasFeaturedImage()): ?>
        <meta property="og:image" content="<?php echo e($post->featuredImageUrl()); ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo e(request()->url()); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
<!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2"><?php echo e($post->title); ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?php echo e(route('blog.index')); ?>">Blog</a></li>
                            <li class="breadcrumb-item active"><?php echo e(Str::limit($post->title, 40)); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container">
            <!-- Blog Details -->
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card blog-details mb-4 mb-lg-0">
                        <div class="card-body">
                            <div class="blog-content">
                                <!-- Featured Image -->
                                <?php if($post->hasFeaturedImage()): ?>
                                    <div class="blog-image mb-3">
                                        <img src="<?php echo e($post->featuredImageUrl()); ?>" 
                                             alt="<?php echo e($post->title); ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                <?php endif; ?>

                                <!-- Meta -->
                                <div class="d-flex align-items-center flex-wrap row-gap-2 mb-3">
                                    <a href="javascript:void(0);" class="d-flex align-items-center fs-16 text-gray-9 pe-3 border-end me-3">
                                        <!-- Author avatar (placeholder for now – can link to staff later) -->
                                        <img src="<?php echo e(asset('front-end/html/assets/img/users/user-01.jpg')); ?>" alt="Author" 
                                             class="img-fluid avatar avatar-sm rounded-circle me-2">
                                        <?php echo e(Auth::user()->name ?? 'Admin'); ?> <!-- Replace with real author later -->
                                    </a>
                                    <div class="pe-3 border-end me-3">
                                        <span class="d-flex align-items-center fs-16 text-gray-9">
                                            <i class="isax isax-calendar-2 me-1"></i>
                                            <?php echo e($post->published_at ? $post->published_at->format('d M Y') : 'Draft'); ?>

                                        </span>
                                    </div>
                                    <div>
                                        <?php if($post->category): ?>
                                            <span class="badge badge-sm badge-primary">
                                                <?php echo e($post->category->name); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Title -->
                                

                                <!-- Content -->
                                <div class="mb-3 lh-base">
                                    <?php echo $post->content; ?>

                                </div>

                                <!-- Tags & Share -->
                                <div class="mt-3 pb-3 border-bottom d-flex flex-wrap align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <p class="fs-16 text-gray-9 mb-0 me-2">Tags :</p>
                                        <!-- Static tags for now – later replace with dynamic tags -->
                                        <a href="#" class="badge badge-sm badge-secondary me-2">Travels</a>
                                        <a href="#" class="badge badge-sm badge-secondary me-2">Tips</a>
                                        <a href="#" class="badge badge-sm badge-secondary">Guide</a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <p class="fs-16 text-gray-9 mb-0 me-2">Share On :</p>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(request()->url())); ?>" target="_blank" class="me-2">
                                            <img src="<?php echo e(asset('front-end/html/assets/img/icons/facebook.svg')); ?>" alt="Facebook">
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode(request()->url())); ?>&text=<?php echo e(urlencode($post->title)); ?>" target="_blank" class="me-2">
                                            <img src="<?php echo e(asset('front-end/html/assets/img/icons/twitter.svg')); ?>" alt="Twitter">
                                        </a>
                                        <a href="https://wa.me/?text=<?php echo e(urlencode($post->title . ' ' . request()->url())); ?>" target="_blank">
                                            <img src="<?php echo e(asset('front-end/html/assets/img/icons/whatsapp.svg')); ?>" alt="WhatsApp">
                                        </a>
                                    </div>
                                </div>

                                <!-- Author Bio -->
                                <div class="my-3">
                                    <div class="border border-light br-10 p-3 d-md-flex align-items-center">
                                        <div class="blog-user-image me-md-3 mb-3 mb-md-0 flex-shrink-0">
                                            <img src="<?php echo e(asset('front-end/html/assets/img/users/user-01.jpg')); ?>" alt="Author" class="img-fluid rounded">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fs-14 text-primary mb-1">About Author</h6>
                                            <p class="fs-16 text-gray-6">
                                                Hi, I’m the team at Afro-Vertex Tours. We live and breathe East Africa travel — from Serengeti sunrises to Kilimanjaro summits. Our blog shares real stories, insider tips, and inspiration to help you plan your perfect adventure.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comments Section (static for now – can make dynamic later) -->
                                
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-md-12 theiaStickySidebar">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="pb-3 border-bottom mb-3">
                                <h5 class="d-flex align-items-center">
                                    <span class="me-1 fs-16"><i class="isax isax-search-normal text-primary"></i></span> Search
                                </h5>
                            </div>
                            <div class="blog-search">
                                <div class="search-content">
                                    <div class="search-feild position-relative">
                                        <span><i class="isax isax-search-normal"></i></span>
                                        <input type="text" class="form-control" placeholder="Search">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-candle text-primary fs-16 me-2"></i>Categories</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-medium mb-0"><a href="#">Travel</a></h6>
                                <p>(12)</p>
                            </div>
                            <!-- Add dynamic categories later -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-medium mb-0"><a href="#">Guide</a></h6>
                                <p>(10)</p>
                            </div>
                            <!-- ... more categories ... -->
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="ti ti-brand-blogger text-primary fs-16 me-2"></i>Related Posts</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <?php $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="blog-post mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php if($related->hasFeaturedImage()): ?>
                                            <div class="d-flex">
                                                <a href="<?php echo e(route('blog.show', $related->slug)); ?>" class="avatar avatar-xxl me-2">
                                                    <img src="<?php echo e($related->featuredImageUrl('thumb')); ?>" 
                                                         class="rounded" alt="<?php echo e($related->title); ?>">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?php echo e(route('blog.show', $related->slug)); ?>" 
                                               class="two-line-ellipsis fs-14 fw-medium">
                                                <?php echo e($related->title); ?>

                                            </a>
                                            <div class="d-flex align-items-center mt-2">
                                                <a href="#" class="d-flex align-items-center border-end pe-2 me-2">
                                                    <span class="avatar avatar-xs me-1">
                                                        <img src="<?php echo e(asset('front-end/html/assets/img/users/user-01.jpg')); ?>" 
                                                             class="blog-user-img rounded-circle border border-light" alt="img">
                                                    </span>
                                                    <p class="fs-14 text-truncate">Admin</p>
                                                </a>
                                                <p class="fs-14 text-truncate">
                                                    <i class="isax isax-calendar-2 me-2"></i>
                                                    <?php echo e($related->published_at ? $related->published_at->format('d M Y') : 'Draft'); ?>

                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Popular Tags (static for now) -->
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
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\blog\show.blade.php ENDPATH**/ ?>