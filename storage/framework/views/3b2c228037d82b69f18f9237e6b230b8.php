<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Activities</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(route('admin.dashboard')); ?>">Home/</a></li>
        <li class="breadcrumb-item active">Activities</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Activities</h5>
          <a href="<?php echo e(route('admin.activities.create')); ?>" class="btn btn-primary btn-sm my-3">
            <i class="bi bi-plus-circle"></i> Add New Activity
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
              <th>Tours</th>
              <th>Active</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($activity->name); ?></td>
                <td><?php echo e($activity->tour_count); ?></td>
                <td><?php echo e($activity->is_active ? 'Yes' : 'No'); ?></td>
                <td><?php echo e($activity->is_featured ? 'Yes' : 'No'); ?></td>
                <td><?php echo e($activity->order); ?></td>
                <td>
                  <a href="<?php echo e(route('admin.activities.edit', $activity)); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="<?php echo e(route('admin.activities.destroy', $activity)); ?>" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete <?php echo e(addslashes($activity->name)); ?>? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr><td colspan="6" class="text-center py-4">No activities yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php echo e($activities->links()); ?>

      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\activities\index.blade.php ENDPATH**/ ?>