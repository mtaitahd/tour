<?php $__env->startSection('title', 'Media Tags'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Media Tags</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item active">Tags</li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-4">All Tags</h5>

            <?php if($tags->isEmpty()): ?>
              <p class="text-muted mb-0">No tags yet. Add one to start tagging images — for example "Lion", "Safari", "Sunrise".</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Used by</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <tr>
                        <td>
                          <form action="<?php echo e(route('admin.media.tags.update', $tag)); ?>" method="POST" class="d-flex align-items-center gap-2">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <input type="text" name="name" value="<?php echo e($tag->name); ?>" class="form-control form-control-sm" style="max-width: 200px;">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                          </form>
                        </td>
                        <td><?php echo e($tag->media_count); ?> image<?php echo e($tag->media_count === 1 ? '' : 's'); ?></td>
                        <td class="text-end">
                          <form action="<?php echo e(route('admin.media.tags.destroy', $tag)); ?>" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete tag &quot;<?php echo e($tag->name); ?>&quot;? Images tagged with it will not be deleted, just untagged.');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-3">Add Tag</h6>
            <form action="<?php echo e(route('admin.media.tags.store')); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Tag name" required>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Tag
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\tags\index.blade.php ENDPATH**/ ?>