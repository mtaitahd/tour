
<?php $__env->startSection('title', 'Create Page'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Create New Page</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.pages.index')); ?>">Pages</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Page Information</h5>

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

            <form method="POST" action="<?php echo e(route('admin.pages.store')); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>

              <!-- Title & Slug -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" required value="<?php echo e(old('title')); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug</label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug')); ?>">
                  <small class="text-muted">Leave empty to auto-generate from title</small>
                </div>
              </div>

              <!-- Content (TinyMCE) -->
              <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Content</label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control tinymce-editor" rows="12"><?php echo e(old('content')); ?></textarea>
                </div>
              </div>

              <!-- Hero Image Upload -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Hero Image</label>
                <div class="col-sm-10">
                  <input type="file" name="hero_image" accept="image/*" class="form-control">
                  <small class="text-muted">Recommended: 1200×800 px, max 5MB. Used as cover in page header and social sharing.</small>
                </div>
              </div>

              <!-- Status & Order -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-5">
                  <select name="status" class="form-select">
                    <option value="draft" <?php echo e(old('status', 'draft') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="published" <?php echo e(old('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                  </select>
                </div>

                <div class="col-sm-5">
                  <label class="form-label">Order (position in menu)</label>
                  <input type="number" name="order" class="form-control" value="<?php echo e(old('order', 999)); ?>">
                  <small>Lower number = appears higher</small>
                </div>
              </div>

              <!-- SEO -->
              <h5 class="mt-5 mb-3">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" value="<?php echo e(old('meta_title')); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3"><?php echo e(old('meta_description')); ?></textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" value="<?php echo e(old('meta_keywords')); ?>">
                  <small class="text-muted">comma separated</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_create" name="no_robots" value="1" <?php echo e(old('no_robots') ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="no_robots_create">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this page is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>

              <?php echo $__env->make('admin.partials.mega-menu-section', [
                  'source' => null,
                  'sourceType' => 'page',
              ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

              <!-- Submit -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Create Page</button>
                  <a href="<?php echo e(route('admin.pages.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\pages\create.blade.php ENDPATH**/ ?>