<?php $__env->startSection('title', 'Editor Dashboard'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Editor Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Package Editor Panel</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mt-3">Welcome, <?php echo e($editor->name); ?></h5>
            <p class="mb-0">
              Your Package Editor workspace is ready. Package management tools arrive in the
              next phase — for now you can sign out safely.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\editor\dashboard\index.blade.php ENDPATH**/ ?>