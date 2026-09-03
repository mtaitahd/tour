
<?php $__env->startSection('title', 'Edit Destination'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Destination</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.destinations.index')); ?>">Destinations</a></li>
        <li class="breadcrumb-item active">Edit: <?php echo e(Str::limit($destination->name, 30)); ?></li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: <?php echo e($destination->name); ?></h5>

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

            <form method="POST" action="<?php echo e(route('admin.destinations.update', $destination)); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="name" class="form-control" 
                         value="<?php echo e(old('name', $destination->name)); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" 
                         value="<?php echo e(old('slug', $destination->slug)); ?>" required>
                  <small class="text-muted">Used in public URLs (e.g. /destinations/serengeti)</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Country <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <select name="country_code" class="form-select" required>
                    <option value="TZ" <?php echo e(old('country_code', $destination->country_code) == 'TZ' ? 'selected' : ''); ?>>Tanzania (TZ)</option>
                    <option value="KE" <?php echo e(old('country_code', $destination->country_code) == 'KE' ? 'selected' : ''); ?>>Kenya (KE)</option>
                    <option value="UG" <?php echo e(old('country_code', $destination->country_code) == 'UG' ? 'selected' : ''); ?>>Uganda (UG)</option>
                    <option value="RW" <?php echo e(old('country_code', $destination->country_code) == 'RW' ? 'selected' : ''); ?>>Rwanda (RW)</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Type</label>
                <div class="col-sm-10">
                  <input type="text" name="type" class="form-control" 
                         value="<?php echo e(old('type', $destination->type)); ?>" 
                         placeholder="e.g. national_park, beach, mountain, city, lake">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control tinymce-editor" rows="10">
                    <?php echo e(old('description', $destination->description)); ?>

                  </textarea>
                </div>
              </div>

              <!-- Hero & Gallery Upload - Native HTML5 with Live Preview -->
              <div class="row mt-5">
                  <!-- Hero Image -->
                  <div class="col-md-6">
                      <label class="form-label fw-bold mb-3">Hero Image (main cover - 1200&times;800 recommended)</label>

                      <!-- Media Library selection (preferred path going forward) -->
                      <div class="mb-3">
                        <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'hero_image_id','selected' => old('hero_image_id', $destination->hero_image_id),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                        <small class="text-muted d-block mt-1">Choose an existing image from the library, or upload a new one below.</small>
                      </div>

                      <!-- Existing Hero (legacy direct-upload path — still supported) -->
                      <?php if($destination->getFirstMedia('hero')): ?>
                          <div class="existing-hero mb-3 position-relative d-inline-block">
                              <img src="<?php echo e($destination->getFirstMediaUrl('hero', 'thumb')); ?>"
                                   alt="Current Hero Image"
                                   class="img-thumbnail rounded shadow-sm"
                                   style="max-height: 250px; object-fit: cover;">
                              <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-hero">
                                  <i class="bi bi-trash"></i> Remove
                              </button>
                              <input type="hidden" name="delete_hero" value="0" class="delete-hero-flag">
                          </div>
                      <?php endif; ?>

                      <!-- Upload + Preview -->
                      <div class="border rounded p-4 text-center bg-light">
                          <input type="file" name="hero_image" class="form-control mb-2" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                          <small class="text-muted d-block">Or upload a new image directly (max 5MB)</small>
                          <div class="hero-preview mt-3 text-center"></div>
                      </div>
                  </div>

                  <!-- Gallery Images -->
                  <div class="col-md-6">
                      <label class="form-label fw-bold mb-3">Gallery Images (multiple allowed)</label>

                      
                      <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'gallery_image_ids','multiple' => true,'selected' => old('gallery_image_ids', app(\App\Services\MediaLibraryService::class)->orderedImagesFor($destination, 'gallery')->pluck('id')->all()),'label' => 'Select Gallery Images'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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


              <h5 class="card-title mt-5">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" 
                         value="<?php echo e(old('meta_title', $destination->meta_title)); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3">
                    <?php echo e(old('meta_description', $destination->meta_description)); ?>

                  </textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" 
                         value="<?php echo e(old('meta_keywords', $destination->meta_keywords)); ?>">
                  <small>comma separated</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" 
                           <?php echo e(old('is_featured', $destination->is_featured) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Show in featured destinations</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control w-25" 
                         value="<?php echo e(old('order', $destination->order)); ?>">
                  <small>Lower number = appears higher in lists</small>
                </div>
              </div>

              <!-- FAQs -->
              <div class="row mb-5 mt-5">
                <label class="col-sm-2 col-form-label"><strong>FAQs</strong></label>
                <div class="col-sm-10">
                  <div id="faqs-wrapper">
                    <?php
                      $existingFaqs = old('faqs', $destination->faqs ?? []);
                    ?>
                    <?php if(!empty($existingFaqs)): ?>
                      <?php $__currentLoopData = $existingFaqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faqIdx => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="faq-item row mb-3 align-items-start">
                          <div class="col-md-5">
                            <input type="text" name="faqs[<?php echo e($faqIdx); ?>][question]" class="form-control"
                                   placeholder="Question"
                                   value="<?php echo e(old("faqs.$faqIdx.question", $faq['question'] ?? '')); ?>">
                          </div>
                          <div class="col-md-5">
                            <textarea name="faqs[<?php echo e($faqIdx); ?>][answer]" class="form-control" rows="3"
                                      placeholder="Answer"><?php echo e(old("faqs.$faqIdx.answer", $faq['answer'] ?? '')); ?></textarea>
                          </div>
                          <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
                          </div>
                        </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                      <div class="faq-item row mb-3 align-items-start">
                        <div class="col-md-5">
                          <input type="text" name="faqs[0][question]" class="form-control" placeholder="Question">
                        </div>
                        <div class="col-md-5">
                          <textarea name="faqs[0][answer]" class="form-control" rows="3" placeholder="Answer"></textarea>
                        </div>
                        <div class="col-md-2">
                          <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <button type="button" id="add-faq" class="btn btn-sm btn-primary mt-2">Add FAQ</button>
                </div>
              </div>

              <!-- Reviews -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Reviews</label>
                <div class="col-sm-10">
                  <textarea name="reviews_embed" class="form-control" rows="4"
                            placeholder="Paste a review page URL, or a review widget embed code (e.g. Elfsight)"><?php echo e(old('reviews_embed', $destination->reviews_embed)); ?></textarea>
                  <small class="text-muted">A plain link renders as a "Read Reviews" button. An embed snippet renders as-is.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary" id="submit-form">Update Destination</button>
                  <a href="<?php echo e(route('admin.destinations.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Live preview for new hero image (direct upload still supported for hero —
        // only gallery became picker-only per the decision to fully replace upload
        // with the picker for galleries specifically, not hero).
        document.querySelector('input[name="hero_image"]').addEventListener('change', function(e) {
            const preview = document.querySelector('.hero-preview');
            preview.innerHTML = '';
            if (e.target.files[0]) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(e.target.files[0]);
                img.className = 'img-thumbnail mt-3';
                img.style.maxHeight = '200px';
                preview.appendChild(img);
            }
        });

        // Remove existing hero image
        document.querySelectorAll('.remove-hero').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remove current hero image?')) {
                    this.closest('.existing-hero').remove();
                    document.querySelector('.delete-hero-flag').value = '1';
                }
            });
        });
    });
</script>

<!-- FAQs repeater -->
<script>
  $(document).ready(function () {
    let faqIndex = $('#faqs-wrapper .faq-item').length;

    $('#add-faq').on('click', function () {
      const newFaq = `
        <div class="faq-item row mb-3 align-items-start">
          <div class="col-md-5">
            <input type="text" name="faqs[${faqIndex}][question]" class="form-control" placeholder="Question">
          </div>
          <div class="col-md-5">
            <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3" placeholder="Answer"></textarea>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
          </div>
        </div>`;
      $('#faqs-wrapper').append(newFaq);
      faqIndex++;
    });

    $(document).on('click', '.remove-faq', function () {
      $(this).closest('.faq-item').remove();
    });
  });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\destinations\edit.blade.php ENDPATH**/ ?>