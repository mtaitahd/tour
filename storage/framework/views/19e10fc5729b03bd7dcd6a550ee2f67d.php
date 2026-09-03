
<?php $__env->startSection('title', 'Inquiries'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Inquiries & Booking Requests</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Inquiries</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Inquiries (<?php echo e($inquiries->total()); ?>)</h5>
              <div>
                <!-- Status filters -->
                <a href="<?php echo e(route('admin.inquiries.index')); ?>" class="btn btn-outline-secondary btn-sm <?php echo e(!request('status') ? 'active' : ''); ?>">All</a>
                <a href="<?php echo e(route('admin.inquiries.index', ['status' => 'pending'])); ?>" class="btn btn-warning btn-sm <?php echo e(request('status') == 'pending' ? 'active' : ''); ?>">Pending</a>
                <a href="<?php echo e(route('admin.inquiries.index', ['status' => 'contacted'])); ?>" class="btn btn-info btn-sm <?php echo e(request('status') == 'contacted' ? 'active' : ''); ?>">Contacted</a>
                <a href="<?php echo e(route('admin.inquiries.index', ['status' => 'confirmed'])); ?>" class="btn btn-success btn-sm <?php echo e(request('status') == 'confirmed' ? 'active' : ''); ?>">Confirmed</a>
                <a href="<?php echo e(route('admin.inquiries.index', ['status' => 'cancelled'])); ?>" class="btn btn-danger btn-sm <?php echo e(request('status') == 'cancelled' ? 'active' : ''); ?>">Cancelled</a>
              </div>
            </div>

            <!-- Type filters -->
            <div class="mb-3">
                <a href="<?php echo e(route('admin.inquiries.index', request()->only('status'))); ?>" class="btn btn-outline-dark btn-sm <?php echo e(!request('type') ? 'active' : ''); ?>">All Types</a>
                <a href="<?php echo e(route('admin.inquiries.index', array_merge(request()->only('status'), ['type' => 'tour_booking']))); ?>" class="btn btn-outline-dark btn-sm <?php echo e(request('type') == 'tour_booking' ? 'active' : ''); ?>">Tour Bookings</a>
                <a href="<?php echo e(route('admin.inquiries.index', array_merge(request()->only('status'), ['type' => 'contact']))); ?>" class="btn btn-outline-dark btn-sm <?php echo e(request('type') == 'contact' ? 'active' : ''); ?>">General Contact</a>
            </div>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <table class="table table-striped datatable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Tour</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $inquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inquiry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <tr>
                    <td><?php echo e($inquiry->id); ?></td>
                    <td>
                      <?php if($inquiry->isTourBooking()): ?>
                        <span class="badge bg-primary">Tour Booking</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Contact</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo e($inquiry->name); ?></td>
                    <td><?php echo e($inquiry->email); ?></td>
                    <td><?php echo e($inquiry->phone); ?></td>
                    <td>
                      <?php if($inquiry->tour): ?>
                        <a href="<?php echo e(route('tour.show', $inquiry->tour->slug)); ?>" target="_blank">
                          <?php echo e(Str::limit($inquiry->tour->title, 40)); ?>

                        </a>
                      <?php else: ?>
                        <span class="text-muted">General Inquiry</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge bg-<?php echo e($inquiry->status == 'pending' ? 'warning' : 
                        ($inquiry->status == 'contacted' ? 'info' : 
                        ($inquiry->status == 'confirmed' ? 'success' : 'danger'))); ?>">
                        <?php echo e(ucfirst($inquiry->status)); ?>

                      </span>
                    </td>
                    <td><?php echo e($inquiry->created_at->format('d M Y H:i')); ?></td>
                    <td>
                      <a href="<?php echo e(route('admin.inquiries.show', $inquiry)); ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                      </a>
                      <form action="<?php echo e(route('admin.inquiries.destroy', $inquiry)); ?>" method="POST" class="d-inline"
                            onsubmit="return confirm('Delete this inquiry from <?php echo e(addslashes($inquiry->name)); ?>? This cannot be undone.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="9" class="text-center py-5">No inquiries yet.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <?php echo e($inquiries->links()); ?>

          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\inquiries\index.blade.php ENDPATH**/ ?>