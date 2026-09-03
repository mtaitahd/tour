<?php $__env->startSection('title', 'Edit Accommodation'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Accommodation</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.accommodations.index')); ?>">Accommodations</a></li>
        <li class="breadcrumb-item active">Edit: <?php echo e(Str::limit($accommodation->name, 30)); ?></li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: <?php echo e($accommodation->name); ?></h5>

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

            <form method="POST" action="<?php echo e(route('admin.accommodations.update', $accommodation)); ?>">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $accommodation->name)); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" value="<?php echo e(old('slug', $accommodation->slug)); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Tier</label>
                <div class="col-sm-10">
                  <input type="text" name="tier" class="form-control" value="<?php echo e(old('tier', $accommodation->tier)); ?>"
                         placeholder="e.g. Luxury Lodge, Tented Camp, Boutique Hotel, Beach Resort">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Destination</label>
                <div class="col-sm-10">
                  <select name="destination_id" class="form-select">
                    <option value="">— None —</option>
                    <?php $__currentLoopData = $destinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $destination): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e($destination->id); ?>" <?php echo e(old('destination_id', $accommodation->destination_id) == $destination->id ? 'selected' : ''); ?>>
                        <?php echo e($destination->name); ?>

                      </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Location</label>
                <div class="col-sm-10">
                  <input type="text" name="location" class="form-control" value="<?php echo e(old('location', $accommodation->location)); ?>"
                         placeholder="e.g. Ngorongoro Crater Rim">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control tinymce-editor" rows="8"><?php echo e(old('description', $accommodation->description)); ?></textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Amenities</label>
                <div class="col-sm-10">
                  <textarea name="amenities_raw" class="form-control" rows="4"
                            placeholder="One per line, e.g.&#10;Free WiFi&#10;Swimming Pool&#10;Spa & Wellness Centre"><?php echo e(old('amenities_raw', implode("\n", $accommodation->amenities ?? []))); ?></textarea>
                  <small class="text-muted">One amenity per line.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Price From</label>
                <div class="col-sm-5">
                  <input type="number" step="0.01" min="0" name="price_from" class="form-control" value="<?php echo e(old('price_from', $accommodation->price_from)); ?>">
                </div>
                <div class="col-sm-5">
                  <input type="text" name="currency" class="form-control" maxlength="3" value="<?php echo e(old('currency', $accommodation->currency)); ?>">
                </div>
              </div>

              <!-- Hero & Gallery -->
              <div class="row mt-5">
                  <div class="col-md-6">
                      <label class="form-label fw-bold mb-3">Hero Image</label>
                      <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'hero_image_id','selected' => old('hero_image_id', $accommodation->hero_image_id),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                  <div class="col-md-6">
                      <label class="form-label fw-bold mb-3">Gallery Images</label>
                      <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'gallery_image_ids','multiple' => true,'selected' => old('gallery_image_ids', app(\App\Services\MediaLibraryService::class)->orderedImagesFor($accommodation, 'gallery')->pluck('id')->all()),'label' => 'Select Gallery Images'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                      <small class="text-muted d-block mt-1">Choose one or more images from the library. Drag to reorder.</small>
                  </div>
              </div>

              <div class="row mb-3 mt-5">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="status" class="form-select" required>
                    <option value="draft" <?php echo e(old('status', $accommodation->status) == 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="published" <?php echo e(old('status', $accommodation->status) == 'published' ? 'selected' : ''); ?>>Published</option>
                    <option value="archived" <?php echo e(old('status', $accommodation->status) == 'archived' ? 'selected' : ''); ?>>Archived</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo e(old('is_featured', $accommodation->is_featured) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show in homepage's Relaxing Accommodations section</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control w-25" value="<?php echo e(old('order', $accommodation->order)); ?>">
                  <small>Lower number = appears higher in lists</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Accommodation</button>
                  <a href="<?php echo e(route('admin.accommodations.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\accommodations\edit.blade.php ENDPATH**/ ?>