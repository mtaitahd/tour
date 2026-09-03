<?php $__env->startSection('title', 'Tour Categories'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Tour Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Tour Categories</li>
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
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTourCategoryModal">
                <i class="bi bi-plus-circle"></i> Add New Category
              </button>
            </div>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <p class="text-muted small">
              These power the public tour-listing pages linked from the footer (e.g.
              Tanzania Tours, Kenya Safari, Kilimanjaro Climbing Package) — each
              category's public URL is just its slug, e.g. <code>/<?php echo e($categories->first()->slug ?? 'category-slug'); ?></code>.
            </p>

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Order</th>
                  <th>Name</th>
                  <th>Slug</th>
                  <th>Tours</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($category->order); ?></td>
                    <td><?php echo e($category->name); ?></td>
                    <td><code><?php echo e($category->slug); ?></code></td>
                    <td><?php echo e($category->tour_packages_count); ?></td>
                    <td>
                      <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTourCategoryModal" onclick="openEditTourCategory('<?php echo e(route('admin.tour-categories.edit', $category)); ?>')">Edit</a>
                      <form action="<?php echo e(route('admin.tour-categories.destroy', $category)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Tour packages will remain but lose this category assignment.')">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="5" class="text-center py-4">No categories yet. Add your first one!</td>
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

  
  <div class="modal fade" id="addTourCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--primary);"></i>Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="<?php echo e(route('admin.tour-categories.store')); ?>" id="addTourCategoryForm">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="window" value="add-tour-category-modal">

          <div class="modal-body">
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

            <div class="mb-3">
              <label class="form-label">Category Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                     value="<?php echo e(old('name')); ?>" required placeholder="e.g. Tanzania Tours, Kilimanjaro Climbing Package">
              <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" name="slug" class="form-control <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                     value="<?php echo e(old('slug')); ?>" placeholder="Auto-generated from name if left empty">
              <small class="text-muted">This becomes the public URL, e.g. "Tanzania Tours" &rarr; <code>/tanzania-tours</code>.</small>
              <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?php echo e(old('description')); ?></textarea>
              <small class="text-muted">Optional — not shown publicly yet.</small>
            </div>

            <div class="mb-1">
              <label class="form-label">Order</label>
              <input type="number" name="order" class="form-control w-25" value="<?php echo e(old('order', 999)); ?>" min="0">
              <small class="text-muted">Lower numbers appear first in the footer's category list.</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  
  <div class="modal fade" id="editTourCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1000px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="editTourCategoryModalLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="editTourCategoryIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditTourCategory(url) {
  var frame = document.getElementById('editTourCategoryIframe');
  document.getElementById('editTourCategoryModalLabel').textContent = 'Edit Category';
  var sep = url.indexOf('?') === -1 ? '?' : '&';
  frame.src = url + sep + 'modal=1';
}

document.addEventListener('DOMContentLoaded', function () {
  var reopened = <?php echo e(old('window') === 'add-tour-category-modal' ? 'true' : 'false'); ?>;
  if (reopened) {
    var modalEl = document.getElementById('addTourCategoryModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
});

(function () {
  var modalEl = document.getElementById('editTourCategoryModal');
  var frame = document.getElementById('editTourCategoryIframe');
  var wasForm = false;

  modalEl.addEventListener('hidden.bs.modal', function () {
    wasForm = false;
    frame.src = 'about:blank';
  });

  frame.addEventListener('load', function () {
    try {
      var path = frame.contentWindow.location.pathname;
      var basePath = new URL("<?php echo e(route('admin.tour-categories.index')); ?>", window.location.origin).pathname;
      if (path === basePath && wasForm) {
        wasForm = false;
        var inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
        window.location.reload();
        return;
      }
      if (path !== basePath && path.indexOf(basePath + '/') === 0) {
        wasForm = true;
      } else {
        wasForm = false;
      }
    } catch (e) {
      wasForm = false;
    }
  });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-categories\index.blade.php ENDPATH**/ ?>