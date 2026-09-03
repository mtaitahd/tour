<?php $__env->startSection('title', 'Sitemap'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Sitemap</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.settings.index')); ?>">Settings</a></li>
        <li class="breadcrumb-item active">Sitemap</li>
      </ol>
    </nav>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i> <?php echo e(session('error')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Sitemap Status</h5>
          <p class="text-muted">
            The sitemap lets Google find and index your tours, destinations, pages and blog posts.
            It is generated automatically when you save a tour, page or blog post — or you can rebuild it manually below.
          </p>

          <dl class="row mb-0">
            <dt class="col-sm-4">Generated file</dt>
            <dd class="col-sm-8">
              <?php if($fileExists): ?>
                <span class="badge text-bg-success">Exists</span>
                <code><?php echo e($filePath); ?></code><br>
                <small class="text-muted">Last generated: <?php echo e($lastMod); ?></small>
              <?php else: ?>
                <span class="badge text-bg-warning">Not yet generated</span>
                <code><?php echo e($filePath); ?></code>
              <?php endif; ?>
            </dd>

            <dt class="col-sm-4">Public URL</dt>
            <dd class="col-sm-8"><a href="<?php echo e($fileUrl); ?>" target="_blank"><?php echo e($fileUrl); ?></a></dd>

            <dt class="col-sm-4">XML endpoint</dt>
            <dd class="col-sm-8"><a href="<?php echo e(url('/sitemap.xml')); ?>" target="_blank"><?php echo e(url('/sitemap.xml')); ?></a>
              <small class="text-muted d-block">Served dynamically (respects the "Enable Sitemap.xml" setting).</small></dd>

            <dt class="col-sm-4">Total URLs</dt>
            <dd class="col-sm-8"><?php echo e($counts['total']); ?></dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">URL Breakdown</h5>
          <table class="table table-sm">
            <tbody>
              <tr><td>Static pages + listings</td><td class="text-end"><?php echo e($counts['total'] - ($counts['tours'] + $counts['categories'] + $counts['destinations'] + $counts['pages'] + $counts['blog'])); ?></td></tr>
              <tr><td>Tours</td><td class="text-end"><?php echo e($counts['tours']); ?></td></tr>
              <tr><td>Tour Categories</td><td class="text-end"><?php echo e($counts['categories']); ?></td></tr>
              <tr><td>Destinations</td><td class="text-end"><?php echo e($counts['destinations']); ?></td></tr>
              <tr><td>Pages</td><td class="text-end"><?php echo e($counts['pages']); ?></td></tr>
              <tr><td>Blog posts</td><td class="text-end"><?php echo e($counts['blog']); ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Actions</h5>
          <form method="POST" action="<?php echo e(route('admin.sitemap.generate')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-magic"></i> Generate Sitemap
            </button>
          </form>
          <div class="mt-2">
            <a href="<?php echo e(route('admin.settings.index')); ?>" class="btn btn-outline-secondary w-100">Open SEO Settings</a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\sitemap\index.blade.php ENDPATH**/ ?>