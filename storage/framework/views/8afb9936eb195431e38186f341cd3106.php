<?php $__env->startSection('title', 'Edit Image'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Image</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.show', $image)); ?>"><?php echo e($image->display_title); ?></a></li>
        <li class="breadcrumb-item active">Edit</li>
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
      <div class="col-lg-3 text-center">
        <img src="<?php echo e($image->getUrl('thumb-webp') ?: $image->getUrl()); ?>"
             alt="<?php echo e($image->meta?->alt_text ?: $image->name); ?>"
             class="img-fluid rounded shadow-sm mb-3">
        <p class="text-muted small"><?php echo e($image->file_name); ?></p>
      </div>

      <div class="col-lg-9">
        <div class="card">
          <div class="card-body">
            <form action="<?php echo e(route('admin.media.update', $image)); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title</label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" value="<?php echo e(old('title', $image->meta?->title)); ?>"
                         placeholder="<?php echo e($image->name); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Alt Text</label>
                <div class="col-sm-10">
                  <input type="text" name="alt_text" class="form-control" value="<?php echo e(old('alt_text', $image->meta?->alt_text)); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Caption</label>
                <div class="col-sm-10">
                  <input type="text" name="caption" class="form-control" value="<?php echo e(old('caption', $image->meta?->caption)); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $image->meta?->description)); ?></textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Categories</label>
                <div class="col-sm-10">
                  <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                    <?php echo $__env->make('admin.media.partials.category-checkboxes', [
                        'categories' => $categories,
                        'selectedIds' => old('category_ids', $image->categories->pluck('id')->all()),
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Tags</label>
                <div class="col-sm-10">
                  <div class="d-flex flex-wrap gap-2 border rounded p-3">
                    <?php $__empty_1 = true; $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                      <?php $checked = in_array($tag->id, old('tag_ids', $image->tags->pluck('id')->all())); ?>
                      <label class="badge <?php echo e($checked ? 'bg-primary' : 'bg-light text-dark'); ?> text-decoration-none" style="cursor: pointer;">
                        <input type="checkbox" name="tag_ids[]" value="<?php echo e($tag->id); ?>" class="d-none tag-toggle" <?php echo e($checked ? 'checked' : ''); ?>>
                        <?php echo e($tag->name); ?>

                      </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                      <span class="text-muted small">No tags yet.</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <a href="<?php echo e(route('admin.media.show', $image)); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tag-toggle').forEach(function (input) {
        input.addEventListener('change', function () {
            const badge = this.closest('label');
            badge.classList.toggle('bg-primary', this.checked);
            badge.classList.toggle('text-dark', !this.checked);
            badge.classList.toggle('bg-light', !this.checked);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\edit.blade.php ENDPATH**/ ?>