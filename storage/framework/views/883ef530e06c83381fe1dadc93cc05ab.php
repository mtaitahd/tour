<?php $__env->startSection('title', 'Create Tour Category'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Create Tour Category</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(route('admin.dashboard')); ?>">Home/ </a></li>
        <li><a href="<?php echo e(route('admin.tour-categories.index')); ?>"> Tour Categories</a></li>
        <li class="breadcrumb-item active">/ Create</li>
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
            <form method="POST" action="<?php echo e(route('admin.tour-categories.store')); ?>">
              <?php echo csrf_field(); ?>

              <div class="row my-3">
                <label class="col-sm-3 col-form-label">Category Name *</label>
                <div class="col-sm-9">
                  <input type="text" name="name" class="form-control" required value="<?php echo e(old('name')); ?>"
                         placeholder="e.g. Tanzania Tours, Kilimanjaro Climbing Package">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Slug</label>
                <div class="col-sm-9">
                  <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug')); ?>">
                  <small class="text-muted">Auto-generated from name if left empty. This becomes the public URL — e.g. "Tanzania Tours" becomes <code>/tanzania-tours</code>.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                  <textarea name="description" class="form-control" rows="4"><?php echo e(old('description')); ?></textarea>
                  <small class="text-muted">Optional — not shown publicly yet, but available for a future category intro/heading.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Order</label>
                <div class="col-sm-9">
                  <input type="number" name="order" class="form-control w-25" value="<?php echo e(old('order', 999)); ?>" min="0">
                  <small class="text-muted">Lower numbers appear first in the footer's category list.</small>
                </div>
              </div>

              <!-- SEO Fields -->
              <div class="row mt-4 mb-2">
                <div class="col-12"><h6 class="fw-bold text-muted">SEO Settings</h6><hr></div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Title</label>
                <div class="col-sm-9">
                  <input type="text" name="meta_title" class="form-control" value="<?php echo e(old('meta_title')); ?>"
                         placeholder="e.g. Tanzania Tours & Safaris | Afro-Vertex" maxlength="255">
                  <small class="text-muted">Max 60 chars recommended. Shown in Google search results.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Description</label>
                <div class="col-sm-9">
                  <textarea name="meta_description" class="form-control" rows="3"
                            placeholder="Discover the best Tanzania tours, safaris, and adventures..."><?php echo e(old('meta_description')); ?></textarea>
                  <small class="text-muted">Max 155 chars recommended. Shown below the title in Google results.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Meta Keywords</label>
                <div class="col-sm-9">
                  <input type="text" name="meta_keywords" class="form-control" value="<?php echo e(old('meta_keywords')); ?>"
                         placeholder="tanzania tours, safari, kilimanjaro" maxlength="255">
                  <small class="text-muted">Comma-separated keywords.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Hide from Search Engines</label>
                <div class="col-sm-9">
                  <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="no_robots" value="1"
                           id="no_robots" <?php echo e(old('no_robots') ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="no_robots">Noindex — exclude from Google / sitemap</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label"></label>
                <div class="col-sm-9">
                  <button type="submit" class="btn btn-primary">Create Category</button>
                  <a href="<?php echo e(route('admin.tour-categories.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-categories\create.blade.php ENDPATH**/ ?>