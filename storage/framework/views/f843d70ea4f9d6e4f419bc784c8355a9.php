<?php $__env->startSection('title', 'Media Categories'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Media Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.media.index')); ?>">Media Library</a></li>
        <li class="breadcrumb-item active">Categories</li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Categories</h5>
              <a href="<?php echo e(route('admin.media.categories.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Category
              </a>
            </div>

            <?php if($categories->isEmpty()): ?>
              <p class="text-muted mb-0">No categories yet. Create one to start organizing your media — for example "Destinations" with sub-categories like "Kilimanjaro" and "Serengeti".</p>
            <?php else: ?>
              <ul class="list-unstyled mb-0">
                <?php echo $__env->make('admin.media.categories.partials.tree-row', ['categories' => $categories, 'depth' => 0], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small">About Categories</h6>
            <p class="small text-muted mb-2">
              Categories are nested — a category can have sub-categories underneath it
              (for example, "Destinations" containing "Kilimanjaro", "Serengeti", and
              "Zanzibar"). An image can belong to more than one category.
            </p>
            <p class="small text-muted mb-0">
              Deleting a category removes the category itself and any sub-categories
              under it, but never deletes the images in it — they just lose that
              category assignment.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\categories\index.blade.php ENDPATH**/ ?>