
<?php $__env->startSection('title', 'Tour Packages'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Tour Packages</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item">Tours</li>
        <li class="breadcrumb-item active">All Packages</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
              <h5 class="card-title mb-0">All Tour Packages</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#packageFormModal" onclick="openPackageForm('<?php echo e(route('admin.tour-packages.create')); ?>')">
                <i class="bi bi-plus-circle"></i> Add New Package
              </button>
            </div>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <table class="table datatable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Title</th>
                  <th>Duration</th>
                  <th>Price From</th>
                  <th>Status</th>
                  <th>Featured</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $tourPackages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($package->title); ?></td>
                    <td><?php echo e($package->duration_days); ?> days</td>
                    <td><?php echo e(number_format($package->base_price)); ?> <?php echo e($package->currency); ?></td>
                    <td>
                      <?php if($package->status === 'published'): ?>
                        <span class="badge bg-success">Published</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Draft</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo e($package->is_featured ? 'Yes' : 'No'); ?></td>
                    <td>
                      <a href="<?php echo e(route('tour.show', $package->slug)); ?>" target="_blank" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#packageFormModal" onclick="openPackageForm('<?php echo e(route('admin.tour-packages.edit', $package->id)); ?>')">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form action="<?php echo e(route('admin.tour-packages.destroy', $package->id)); ?>" method="POST" class="d-inline">
                          <?php echo csrf_field(); ?>
                          <?php echo method_field('DELETE'); ?>

                          <!-- Trigger modal button -->
                          <button type="button" class="btn btn-sm btn-outline-danger" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#deleteModal<?php echo e($package->id); ?>"
                                  title="Delete">
                              <i class="fas fa-trash"></i>
                          </button>

                          <!-- Modal for this specific page -->
                          <div class="modal fade" id="deleteModal<?php echo e($package->id); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo e($package->id); ?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                  <div class="modal-content">
                                      <div class="modal-header bg-danger text-white">
                                          <h5 class="modal-title" id="deleteModalLabel<?php echo e($package->id); ?>">Confirm Deletion</h5>
                                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          Are you sure you want to delete the page:  
                                          <strong>"<?php echo e($package->title); ?>"</strong>?<br>
                                          <small class="text-muted">This action cannot be undone.</small>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                          <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr><td colspan="7" class="text-center py-4">No packages yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Add / Edit Package Modal -->
  <div class="modal fade" id="packageFormModal" tabindex="-1" aria-labelledby="packageFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="packageFormModalLabel">Tour Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="packageFormIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
    function openPackageForm(url) {
      var frame = document.getElementById('packageFormIframe');
      document.getElementById('packageFormModalLabel').textContent = 'Add Package';
      if (typeof url === 'string' && url.indexOf('/edit') !== -1) {
        document.getElementById('packageFormModalLabel').textContent = 'Edit Package';
      }
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      frame.src = url + sep + 'modal=1';
    }

    (function () {
      var modalEl = document.getElementById('packageFormModal');
      var frame = document.getElementById('packageFormIframe');
      var wasForm = false;

      modalEl.addEventListener('hidden.bs.modal', function () {
        wasForm = false;
        frame.src = 'about:blank';
      });

      frame.addEventListener('load', function () {
        try {
          var path = frame.contentWindow.location.pathname;
          var basePath = new URL("<?php echo e(route('admin.tour-packages.index')); ?>", window.location.origin).pathname;
          if (path === basePath && wasForm) {
            wasForm = false;
            bootstrap.Modal.getInstance(modalEl).hide();
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
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\index.blade.php ENDPATH**/ ?>