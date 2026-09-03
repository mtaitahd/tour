<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Testimonials</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(route('admin.dashboard')); ?>">Home/</a></li>
        <li class="breadcrumb-item active">Testimonials</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Testimonials</h5>
          <a href="<?php echo e(route('admin.testimonials.create')); ?>" class="btn btn-primary btn-sm my-3">
            <i class="bi bi-plus-circle"></i> Add New Testimonial
          </a>
        </div>

        <?php if(session('success')): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Rating</th>
              <th>Context</th>
              <th>Status</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($testimonial->name); ?></td>
                <td><?php echo e(str_repeat('★', $testimonial->rating)); ?><?php echo e(str_repeat('☆', 5 - $testimonial->rating)); ?></td>
                <td>
                  <?php if($testimonial->tourPackage): ?>
                    Tour: <?php echo e(Str::limit($testimonial->tourPackage->title, 30)); ?>

                  <?php elseif($testimonial->destination): ?>
                    Destination: <?php echo e($testimonial->destination->name); ?>

                  <?php else: ?>
                    <span class="text-muted">General (homepage)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($testimonial->status === 'published'): ?>
                    <span class="badge bg-success">Published</span>
                  <?php elseif($testimonial->status === 'archived'): ?>
                    <span class="badge bg-secondary">Archived</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Draft</span>
                  <?php endif; ?>
                </td>
                <td><?php echo e($testimonial->is_featured ? 'Yes' : 'No'); ?></td>
                <td><?php echo e($testimonial->order); ?></td>
                <td>
                  <a href="<?php echo e(route('admin.testimonials.edit', $testimonial)); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="<?php echo e(route('admin.testimonials.destroy', $testimonial)); ?>" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this testimonial from <?php echo e(addslashes($testimonial->name)); ?>? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="7" class="text-center py-4">No testimonials yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php echo e($testimonials->links()); ?>

      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\testimonials\index.blade.php ENDPATH**/ ?>