<?php $__env->startSection('title', 'Edit Category'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Category</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.categories.index')); ?>">Categories</a></li>
        <li class="breadcrumb-item active">Edit: <?php echo e($category->name); ?></li>
      </ol>
    </nav>
  </div>

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
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <form action="<?php echo e(route('admin.media.categories.update', $category)); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $category->name)); ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                  <option value="">None (top-level category)</option>
                  <?php echo $__env->make('admin.media.categories.partials.parent-options', [
                      'categories' => $categories,
                      'depth' => 0,
                      'selectedId' => old('parent_id', $category->parent_id),
                      'excludeId' => $category->id,
                  ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </select>
                <small class="text-muted">A category can't be made its own parent — it won't appear in this list.</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $category->description)); ?></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Order</label>
                <input type="number" name="order" class="form-control w-25" value="<?php echo e(old('order', $category->order)); ?>" min="0">
              </div>

              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="<?php echo e(route('admin.media.categories.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\categories\edit.blade.php ENDPATH**/ ?>