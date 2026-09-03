<?php $__env->startSection('title', 'Edit Activity'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Activity</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.activities.index')); ?>">Activities</a></li>
        <li class="breadcrumb-item active">Edit: <?php echo e(Str::limit($activity->name, 30)); ?></li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: <?php echo e($activity->name); ?></h5>
            <p class="text-muted">Linked to <?php echo e($activity->tour_count); ?> published tour(s). Manage which tours include this activity from each tour's edit page.</p>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

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

            <form method="POST" action="<?php echo e(route('admin.activities.update', $activity)); ?>">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $activity->name)); ?>" required>
                  <small class="text-muted">Current slug: <?php echo e($activity->slug); ?> — regenerated automatically if you change the name.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control" rows="4"><?php echo e(old('description', $activity->description)); ?></textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Icon</label>
                <div class="col-sm-10">
                  <input type="text" name="icon" class="form-control" value="<?php echo e(old('icon', $activity->icon)); ?>" placeholder="e.g. isax isax-airplane">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Image</label>
                <div class="col-sm-10">
                  <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'hero_image_id','selected' => old('hero_image_id', $activity->hero_image_id),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Active</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $activity->is_active) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Visible on the site</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo e(old('is_featured', $activity->is_featured) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show in homepage's "What We Offer" section</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control w-25" value="<?php echo e(old('order', $activity->order)); ?>">
                  <small>Lower number = appears higher in lists</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Activity</button>
                  <a href="<?php echo e(route('admin.activities.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\activities\edit.blade.php ENDPATH**/ ?>