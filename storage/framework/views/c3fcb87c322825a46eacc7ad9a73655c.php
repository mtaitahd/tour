
<?php $__env->startSection('title', 'Blog Categories'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Blog Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Blog Categories</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Categories (<?php echo e($categories->total()); ?>)</h5>
              <a href="<?php echo e(route('admin.blog-categories.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Category
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
                  <th>Slug</th>
                  <th>Posts</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($category->name); ?></td>
                    <td><?php echo e($category->slug); ?></td>
                    <td><?php echo e($category->posts()->count()); ?></td>
                    <td>
                      <a href="<?php echo e(route('admin.blog-categories.edit', $category)); ?>" class="btn btn-sm btn-warning">Edit</a>
                      <form action="<?php echo e(route('admin.blog-categories.destroy', $category)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Posts will remain but lose category.')">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="4" class="text-center py-4">No categories yet. Add your first one!</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <?php echo e($categories->links()); ?>

          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\blog-categories\index.blade.php ENDPATH**/ ?>