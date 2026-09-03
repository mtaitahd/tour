<?php $__env->startSection('title', 'Image Details'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Image Details</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item active"><?php echo e($image->display_title); ?></li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo e(session('error')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <section class="section">
    <div class="row">
      
      <div class="col-lg-5">
        <div class="card">
          <div class="card-body text-center">
            <img src="<?php echo e($image->getUrl('medium-webp') ?: $image->getUrl()); ?>"
                 alt="<?php echo e($image->meta?->alt_text ?: $image->name); ?>"
                 class="img-fluid rounded">
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small mb-3">File Information</h6>
            <table class="table table-sm mb-0">
              <tbody>
                <tr><th class="text-muted" style="width: 40%;">File name</th><td><?php echo e($image->file_name); ?></td></tr>
                <tr><th class="text-muted">File size</th><td><?php echo e($image->human_readable_size); ?></td></tr>
                <tr><th class="text-muted">Dimensions</th><td><?php echo e($image->width && $image->height ? "{$image->width} &times; {$image->height} px" : 'Unknown'); ?></td></tr>
                <tr><th class="text-muted">MIME type</th><td><?php echo e($image->mime_type); ?></td></tr>
                <tr><th class="text-muted">Uploaded by</th><td><i class="bi bi-person-up me-1 text-muted"></i><?php echo e($image->uploaded_by_name); ?></td></tr>
                <tr><th class="text-muted">Uploaded</th><td><?php echo e($image->created_at->format('M j, Y \a\t g:ia')); ?></td></tr>
                <tr>
                  <th class="text-muted">Used in</th>
                  <td>
                    <?php if($image->usage_count > 0): ?>
                      <span class="badge bg-primary"><?php echo e($image->usage_count); ?> location<?php echo e($image->usage_count === 1 ? '' : 's'); ?></span>
                    <?php else: ?>
                      <span class="badge bg-light text-muted">Not used anywhere</span>
                    <?php endif; ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <?php if(!empty($usageLocations)): ?>
          <div class="card">
            <div class="card-body">
              <h6 class="card-title text-uppercase text-muted small mb-3">Used In</h6>
              <ul class="list-unstyled mb-0 small">
                <?php $__currentLoopData = $usageLocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li class="d-flex justify-content-between align-items-center mb-2">
                    <span><?php echo e($usage['label']); ?></span>
                    <form action="<?php echo e(route('admin.media.detach', $image)); ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('Unlink this image from here? The image stays in the library.');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="model_type" value="<?php echo e(\App\Http\Controllers\Admin\MediaLibraryController::aliasForModel($usage['model_type'])); ?>">
                      <input type="hidden" name="model_id" value="<?php echo e($usage['model_id']); ?>">
                      <input type="hidden" name="context" value="<?php echo e($usage['context']); ?>">
                      <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Unlink">
                        <i class="bi bi-x"></i>
                      </button>
                    </form>
                  </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>
      </div>

      
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">Details</h5>
              <div>
                <?php if($image->usage_count === 0): ?>
                  <form action="<?php echo e(route('admin.media.destroy', $image)); ?>" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this image permanently? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                <?php else: ?>
                  <button type="button" class="btn btn-outline-danger btn-sm" disabled
                          title="Used in <?php echo e($image->usage_count); ?> location(s) — unlink it from those first">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                <?php endif; ?>
              </div>
            </div>

            <form action="<?php echo e(route('admin.media.update', $image)); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo e(old('title', $image->meta?->title)); ?>"
                       placeholder="<?php echo e($image->name); ?>">
                <small class="text-muted">Shown throughout the admin area if set; falls back to the file name.</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Alt Text</label>
                <input type="text" name="alt_text" class="form-control" value="<?php echo e(old('alt_text', $image->meta?->alt_text)); ?>"
                       placeholder="Describe the image for accessibility and SEO">
              </div>

              <div class="mb-3">
                <label class="form-label">Caption</label>
                <input type="text" name="caption" class="form-control" value="<?php echo e(old('caption', $image->meta?->caption)); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $image->meta?->description)); ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                  <?php echo $__env->make('admin.media.partials.category-checkboxes', [
                      'categories' => $categories,
                      'selectedIds' => old('category_ids', $image->categories->pluck('id')->all()),
                  ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label">Tags</label>
                <div class="d-flex flex-wrap gap-2 border rounded p-3">
                  <?php $__empty_1 = true; $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $checked = in_array($tag->id, old('tag_ids', $image->tags->pluck('id')->all())); ?>
                    <label class="badge <?php echo e($checked ? 'bg-primary' : 'bg-light text-dark'); ?> text-decoration-none" style="cursor: pointer;">
                      <input type="checkbox" name="tag_ids[]" value="<?php echo e($tag->id); ?>" class="d-none tag-toggle" <?php echo e($checked ? 'checked' : ''); ?>>
                      <?php echo e($tag->name); ?>

                    </label>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span class="text-muted small">No tags yet — <a href="<?php echo e(route('admin.media.tags.index')); ?>">create one</a>.</span>
                  <?php endif; ?>
                </div>
              </div>

              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="<?php echo e(route('admin.media.index')); ?>" class="btn btn-secondary ms-2">Back to Library</a>
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

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\show.blade.php ENDPATH**/ ?>