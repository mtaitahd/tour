<?php $__env->startSection('title', 'Media Library'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Media Library</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Media Library</li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if(session('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <?php echo e(session('warning')); ?>

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
      
      <div class="col-lg-3">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small mb-3">Library Stats</h6>
            <div class="d-flex justify-content-between small mb-2">
              <span>Total images</span>
              <strong><?php echo e(number_format($stats['total_images'])); ?></strong>
            </div>
            <div class="d-flex justify-content-between small mb-2">
              <span>Storage used</span>
              <strong><?php echo e($stats['total_size_bytes'] >= 1048576 ? number_format($stats['total_size_bytes'] / 1048576, 1) . ' MB' : number_format($stats['total_size_bytes'] / 1024, 1) . ' KB'); ?></strong>
            </div>
            <div class="d-flex justify-content-between small">
              <span>Unused images</span>
              <strong class="<?php echo e($stats['unused_count'] > 0 ? 'text-warning' : ''); ?>"><?php echo e(number_format($stats['unused_count'])); ?></strong>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="card-title text-uppercase text-muted small mb-0">Categories</h6>
              <a href="<?php echo e(route('admin.media.categories.index')); ?>" class="small text-decoration-none">Manage</a>
            </div>

            <?php if($categories->isEmpty()): ?>
              <p class="small text-muted mb-0">No categories yet.</p>
            <?php else: ?>
              <ul class="list-unstyled mb-0">
                <li class="mb-1">
                  <a href="<?php echo e(route('admin.media.index', array_merge(request()->query(), ['category_id' => null]))); ?>"
                     class="d-block px-2 py-1 rounded small text-decoration-none <?php echo e(empty($filters['category_id']) ? 'bg-primary text-white' : 'text-body'); ?>">
                    All images
                  </a>
                </li>
                <?php echo $__env->make('admin.media.partials.category-tree', ['categories' => $categories, 'activeCategoryId' => $filters['category_id'] ?? null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="card-title text-uppercase text-muted small mb-0">Tags</h6>
              <a href="<?php echo e(route('admin.media.tags.index')); ?>" class="small text-decoration-none">Manage</a>
            </div>

            <?php if($tags->isEmpty()): ?>
              <p class="small text-muted mb-0">No tags yet.</p>
            <?php else: ?>
              <div class="d-flex flex-wrap gap-1">
                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <a href="<?php echo e(route('admin.media.index', array_merge(request()->query(), ['tag_id' => $tag->id]))); ?>"
                     class="badge text-decoration-none <?php echo e((int) ($filters['tag_id'] ?? null) === $tag->id ? 'bg-primary' : 'bg-light text-dark'); ?>">
                    <?php echo e($tag->name); ?> <span class="opacity-75"><?php echo e($tag->media_count); ?></span>
                  </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      
      <div class="col-lg-9">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
              <h5 class="card-title mb-0">All Uploaded Media (<?php echo e($media->total()); ?>)</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-cloud-upload"></i> Upload New Media
              </button>
            </div>

            
            <form method="GET" action="<?php echo e(route('admin.media.index')); ?>" class="row g-2 mb-4">
              <?php if(!empty($filters['category_id'])): ?>
                <input type="hidden" name="category_id" value="<?php echo e($filters['category_id']); ?>">
              <?php endif; ?>
              <?php if(!empty($filters['tag_id'])): ?>
                <input type="hidden" name="tag_id" value="<?php echo e($filters['tag_id']); ?>">
              <?php endif; ?>

              <div class="col-md-5">
                <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>"
                       class="form-control" placeholder="Search by title, filename, or tag&hellip;">
              </div>
              <div class="col-md-3">
                <input type="date" name="uploaded_from" value="<?php echo e($filters['uploaded_from'] ?? ''); ?>"
                       class="form-control" title="Uploaded from">
              </div>
              <div class="col-md-3">
                <select name="sort" class="form-select">
                  <option value="newest" <?php echo e(($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : ''); ?>>Newest first</option>
                  <option value="oldest" <?php echo e(($filters['sort'] ?? '') === 'oldest' ? 'selected' : ''); ?>>Oldest first</option>
                  <option value="name" <?php echo e(($filters['sort'] ?? '') === 'name' ? 'selected' : ''); ?>>Name (A&ndash;Z)</option>
                  <option value="most_used" <?php echo e(($filters['sort'] ?? '') === 'most_used' ? 'selected' : ''); ?>>Most used</option>
                  <option value="least_used" <?php echo e(($filters['sort'] ?? '') === 'least_used' ? 'selected' : ''); ?>>Least used</option>
                </select>
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i></button>
              </div>
            </form>

            <?php if(($filters['search'] ?? null) || ($filters['category_id'] ?? null) || ($filters['tag_id'] ?? null)): ?>
              <div class="mb-3">
                <a href="<?php echo e(route('admin.media.index')); ?>" class="small text-decoration-none">
                  <i class="bi bi-x-circle"></i> Clear filters
                </a>
              </div>
            <?php endif; ?>

            
            <div class="row g-3 media-grid">
              <?php $__empty_1 = true; $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                  <div class="card media-item shadow-sm position-relative h-100">
                    <a href="<?php echo e(route('admin.media.show', $item)); ?>" class="text-decoration-none">
                      <img src="<?php echo e($item->getUrl('thumb-webp') ?: $item->getUrl('thumb') ?: $item->getUrl()); ?>"
                           class="card-img-top" alt="<?php echo e($item->meta?->alt_text ?: $item->name); ?>"
                           style="height: 160px; object-fit: cover;" loading="lazy">
                    </a>
                    <div class="card-body p-2">
                      <p class="mb-1 small text-truncate fw-medium text-body" title="<?php echo e($item->display_title); ?>">
                        <?php echo e($item->display_title); ?>

                      </p>
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size: 0.75rem;"><?php echo e($item->human_readable_size); ?></span>
                        <?php if($item->usage_count > 0): ?>
                          <span class="badge bg-light text-dark" style="font-size: 0.7rem;" title="Used in <?php echo e($item->usage_count); ?> location(s)">
                            <i class="bi bi-link-45deg"></i> <?php echo e($item->usage_count); ?>

                          </span>
                        <?php else: ?>
                          <span class="badge bg-light text-muted" style="font-size: 0.7rem;">Unused</span>
                        <?php endif; ?>
                      </div>
                      <p class="mb-0 mt-1 text-truncate text-muted" style="font-size: 0.7rem;" title="Uploaded by <?php echo e($item->uploaded_by_name); ?>">
                        <i class="bi bi-person-up me-1"></i><?php echo e($item->uploaded_by_name); ?>

                      </p>
                      <?php if($item->categories->isNotEmpty()): ?>
                        <p class="mb-0 mt-1 text-truncate" style="font-size: 0.7rem;">
                          <?php $__currentLoopData = $item->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="text-muted"><?php echo e($category->name); ?></span><?php if(!$loop->last): ?>, <?php endif; ?>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </p>
                      <?php endif; ?>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-media"
                            data-id="<?php echo e($item->id); ?>"
                            data-url="<?php echo e(route('admin.media.destroy.ajax', $item)); ?>"
                            title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                    <a href="<?php echo e(route('admin.media.edit', $item)); ?>"
                       class="btn btn-sm btn-light position-absolute top-0 start-0 m-2"
                       title="Quick edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12 text-center py-5">
                  <i class="bi bi-images" style="font-size: 2.5rem; color: #ccc;"></i>
                  <h5 class="mt-3">No images found</h5>
                  <p class="text-muted">
                    <?php if(($filters['search'] ?? null) || ($filters['category_id'] ?? null) || ($filters['tag_id'] ?? null)): ?>
                      Nothing matches these filters. <a href="<?php echo e(route('admin.media.index')); ?>">Clear filters</a> to see everything.
                    <?php else: ?>
                      Click "Upload New Media" to add your first image.
                    <?php endif; ?>
                  </p>
                </div>
              <?php endif; ?>
            </div>

            <div class="mt-4">
              <?php echo e($media->links('pagination::bootstrap-5', ['size' => 'sm'])); ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php echo $__env->make('admin.media.partials.upload-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        document.body.appendChild(toastContainer);
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        setTimeout(() => {
            toast.hide();
            setTimeout(() => toastContainer.remove(), 500);
        }, 4000);
    }

    // Event delegation on the grid container, rather than binding each .remove-media
    // button individually, so cards injected live by the upload modal (see
    // partials/upload-modal.blade.php's prependToGrid()) are automatically covered
    // with zero extra wiring — there's nothing to re-bind after an upload.
    const mediaGrid = document.querySelector('.media-grid');
    if (mediaGrid) {
        mediaGrid.addEventListener('click', function (e) {
            const button = e.target.closest('.remove-media');
            if (!button) return;

            e.preventDefault();

            if (!confirm('Delete this image permanently? This cannot be undone.')) {
                return;
            }

            const url = button.getAttribute('data-url');
            const item = button.closest('.media-item');

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
                .then(response => response.json().then(data => ({ status: response.status, data })))
                .then(({ status, data }) => {
                    if (status === 200 && data.success) {
                        item.closest('[class*="col-"]').style.transition = 'opacity 0.3s';
                        item.closest('[class*="col-"]').style.opacity = '0';
                        setTimeout(() => item.closest('[class*="col-"]').remove(), 300);
                        showToast('Image deleted.', 'success');
                    } else {
                        showToast(data.message || 'This image could not be deleted.', 'danger');
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-trash"></i>';
                    }
                })
                .catch(() => {
                    showToast('Request failed. Please try again.', 'danger');
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-trash"></i>';
                });
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\index.blade.php ENDPATH**/ ?>