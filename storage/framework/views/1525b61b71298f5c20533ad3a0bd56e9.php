

<?php $__env->startSection('title', 'Group Departures & Fixed-Date Tours | Afro-Vertex'); ?>

<?php $__env->startSection('page-content'); ?>
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <h1 class="breadcrumb-title mb-2">Group Departures & Fixed-Date Tours 2026–2027</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i class="isax isax-home5"></i></a></li>
                    <li class="breadcrumb-item active">Group Departures</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <!-- Filters -->
            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('group-departures.index')); ?>" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Month / Year</label>
                            <input type="month" name="month" class="form-control" 
                                   value="<?php echo e(request('month') ?? now()->format('Y-m')); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tour</label>
                            <select name="tour" class="form-select">
                                <option value="">All Tours</option>
                                <?php $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tourItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tourItem->id); ?>" <?php echo e(request('tour') == $tourItem->id ? 'selected' : ''); ?>>
                                        <?php echo e($tourItem->title); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 mt-4">
                                Filter Departures
                            </button>
                        </div>

                        <div class="col-md-2 text-end mt-4">
                            <a href="<?php echo e(route('group-departures.index')); ?>" class="btn btn-outline-secondary w-100">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grid View -->
            <?php if($departures->count() > 0): ?>
                <div class="row g-4">
                    <?php $__currentLoopData = $departures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card shadow-sm h-100 border-0 departure-card hover-lift">
                                <div class="card-body d-flex flex-column p-4">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="mb-0 fw-bold fs-20">
                                            <?php echo e($dep->tour?->title ?? 'Tour not found'); ?>

                                        </h5>
                                        <?php if($dep->is_featured): ?>
                                            <span class="badge bg-success">Featured</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Dates -->
                                    <p class="mb-2 text-primary fw-medium">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?php echo e($dep->departure_date->format('d M Y')); ?>

                                        <?php if($dep->return_date): ?>
                                            – <?php echo e($dep->return_date->format('d M Y')); ?>

                                        <?php endif; ?>
                                    </p>

                                    <!-- Spots -->
                                    <p class="mb-2">
                                        <strong>Spots:</strong> 
                                        <?php if($dep->isSoldOut()): ?>
                                            <span class="badge bg-danger">Sold Out</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e($dep->spotsLeft()); ?> / <?php echo e($dep->total_spots); ?></span>
                                        <?php endif; ?>
                                    </p>

                                    <!-- Price -->
                                    <?php if($dep->group_price): ?>
                                        <p class="mb-3 fs-18 fw-bold text-primary">
                                            From $<?php echo e(number_format($dep->group_price, 0)); ?> <?php echo e($dep->currency); ?>

                                        </p>
                                    <?php else: ?>
                                        <p class="mb-3 text-muted">Price as per tour package</p>
                                    <?php endif; ?>

                                    <!-- Status Badges -->
                                    <?php if($dep->status == 'guaranteed'): ?>
                                        <span class="badge bg-success mb-3">Guaranteed Departure</span>
                                    <?php elseif($dep->status == 'limited'): ?>
                                        <span class="badge bg-secondary mb-3">Limited Seats</span>
                                    <?php endif; ?>

                                    <!-- Action Button -->
                                    <div class="mt-auto d-grid">
                                        <a href="<?php echo e(route('tour.show', $dep->tour->slug)); ?>" 
                                           class="btn btn-outline-primary">
                                            View Tour Details →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Pagination -->
                <div class="mt-5">
                    <?php echo e($departures->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h4 class="mb-3">No upcoming group departures</h4>
                    <p class="text-muted mb-4">
                        Check back soon or browse our regular tours below.
                    </p>
                    <a href="<?php echo e(route('tours.index')); ?>" class="btn btn-primary">
                        View All Tours
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-styles'); ?>
    <style>
        .departure-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .departure-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem !important;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontend.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\group-departures\index.blade.php ENDPATH**/ ?>