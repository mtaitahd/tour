
<?php $__env->startSection('title', 'Edit Page'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Edit Page</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.pages.index')); ?>">Pages</a></li>
        <li class="breadcrumb-item active">Edit: <?php echo e(Str::limit($page->title, 30)); ?></li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit Page: <?php echo e($page->title); ?></h5>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.pages.update', $page->id)); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" 
                         value="<?php echo e(old('title', $page->title)); ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" 
                         value="<?php echo e(old('slug', $page->slug)); ?>" required>
                  <small class="text-muted">Change only if necessary — affects the public URL</small>
                </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Hero Image</label>
                  <div class="col-sm-10">
                      <div class="mb-3">
                        <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'hero_image_id','selected' => old('hero_image_id', $page->hero_image_id),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                      <?php if($page->getFirstMedia('hero')): ?>
                          <div class="mb-3">
                              <img src="<?php echo e($page->getFirstMediaUrl('hero', 'thumb')); ?>" 
                                   alt="Current Hero" 
                                   class="img-thumbnail" 
                                   style="max-height: 180px;">
                          </div>
                      <?php endif; ?>

                      <input type="file" name="hero_image" accept="image/*" class="form-control">
                      <small>Or upload a new image directly. Recommended: 1200&times;800 px</small>
                  </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Content</label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control tinymce-editor" rows="12">
                      <?php echo e(old('content', $page->content ?? '')); ?>

                  </textarea>
                </div>
              </div>

              <!-- After the main content textarea -->
              <?php if(in_array($page->slug, ['contact', 'contact-us'])): ?>
                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Contact Page Heading</label>
                      <div class="col-sm-9">
                          <input type="text" name="contact_heading" class="form-control" 
                                 value="<?php echo e(old('contact_heading', $page->contact_heading)); ?>">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Contact Subheading</label>
                      <div class="col-sm-9">
                          <input type="text" name="contact_subheading" class="form-control" 
                                 value="<?php echo e(old('contact_subheading', $page->contact_subheading)); ?>">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Google Maps Embed Code</label>
                      <div class="col-sm-9">
                          <textarea name="contact_map_embed" class="form-control" rows="6"><?php echo e(old('contact_map_embed', $page->contact_map_embed)); ?></textarea>
                          <small class="text-muted">
                              Paste the full &lt;iframe&gt; code from Google Maps → Share → Embed a map.
                              Leave blank to keep the default map.
                          </small>
                      </div>
                  </div>
              <?php endif; ?>

              <?php if(in_array($page->slug, ['about', 'about-us'])): ?>
                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Story Title</label>
                      <div class="col-sm-9">
                          <input type="text" name="story_title" class="form-control" 
                                 value="<?php echo e(old('story_title', $page->story_title)); ?>">
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Story Image</label>
                      <div class="col-sm-9">
                          <div class="mb-3">
                            <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'story_image_id','selected' => old('story_image_id', $page->story_image_id),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                          
                          <?php if($page->getFirstMedia('story')): ?>
                              <img src="<?php echo e($page->getFirstMediaUrl('story', 'thumb')); ?>" alt="Story Image" style="max-height: 150px;" class="img-thumbnail mt-2">
                          <?php endif; ?>

                          <input type="file" name="story_image" accept="image/*" class="form-control mt-2">
                          <small class="text-muted d-block mt-1">Or upload a new image directly.</small>
                      </div>
                  </div>

                  <div class="row mb-3">
                      <label class="col-sm-3 col-form-label">Why Choose Us Subtitle</label>
                      <div class="col-sm-9">
                          <input type="text" name="why_choose_subtitle" class="form-control" 
                                 value="<?php echo e(old('why_choose_subtitle', $page->why_choose_subtitle)); ?>">
                      </div>
                  </div>

                  <!-- Stats Counters -->
                  <div class="row mb-3 mt-4">
                      <label class="col-sm-3 col-form-label fw-bold">Stats Counters</label>
                      <div class="col-sm-9">
                          <div id="stats-counters-repeater">
                              <?php
                                  $statsCounters = old('stats_counters', $page->stats_counters ?? []);
                              ?>
                              <?php $__empty_1 = true; $__currentLoopData = $statsCounters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $counter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                  <div class="counter-item row g-2 mb-2 align-items-center">
                                      <div class="col-3">
                                          <input type="text" name="stats_counters[<?php echo e($index); ?>][value]" class="form-control"
                                                 placeholder="e.g. 50" value="<?php echo e($counter['value'] ?? ''); ?>">
                                      </div>
                                      <div class="col-2">
                                          <input type="text" name="stats_counters[<?php echo e($index); ?>][suffix]" class="form-control"
                                                 placeholder="+" value="<?php echo e($counter['suffix'] ?? '+'); ?>">
                                      </div>
                                      <div class="col-5">
                                          <input type="text" name="stats_counters[<?php echo e($index); ?>][label]" class="form-control"
                                                 placeholder="e.g. Destinations" value="<?php echo e($counter['label'] ?? ''); ?>">
                                      </div>
                                      <div class="col-2">
                                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                                      </div>
                                  </div>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                  <div class="counter-item row g-2 mb-2 align-items-center">
                                      <div class="col-3">
                                          <input type="text" name="stats_counters[0][value]" class="form-control" placeholder="e.g. 50">
                                      </div>
                                      <div class="col-2">
                                          <input type="text" name="stats_counters[0][suffix]" class="form-control" placeholder="+" value="+">
                                      </div>
                                      <div class="col-5">
                                          <input type="text" name="stats_counters[0][label]" class="form-control" placeholder="e.g. Destinations">
                                      </div>
                                      <div class="col-2">
                                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                                      </div>
                                  </div>
                              <?php endif; ?>
                          </div>
                          <button type="button" id="add-counter" class="btn btn-sm btn-outline-primary mt-1">
                              <i class="bi bi-plus-circle"></i> Add Counter
                          </button>
                          <small class="text-muted d-block mt-1">e.g. "50+ Destinations", "7000+ Happy Travellers".</small>
                      </div>
                  </div>

                  <!-- Team Members -->
                  <div class="row mb-3 mt-4">
                      <label class="col-sm-3 col-form-label fw-bold">Team Members</label>
                      <div class="col-sm-9">
                          <small class="text-muted d-block mb-3">
                              Up to <?php echo e($teamMemberSlots ?? 10); ?> slots below. Leave a slot's name blank to skip it —
                              blank slots are never shown on the page.
                          </small>

                          <?php
                              $teamMembers = old('team_members', $page->custom_data ?? []);
                          ?>

                          <?php for($i = 0; $i < ($teamMemberSlots ?? 10); $i++): ?>
                              <?php $member = $teamMembers[$i] ?? []; ?>
                              <div class="card mb-3 shadow-sm">
                                  <div class="card-body">
                                      <div class="row g-3 align-items-start">
                                          <div class="col-md-3">
                                              <label class="form-label small">Photo</label>
                                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'team_members['.e($i).'][photo_image_id]','selected' => $member['photo_image_id'] ?? null,'label' => 'Select Photo'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                                          <div class="col-md-4">
                                              <label class="form-label small">Name</label>
                                              <input type="text" name="team_members[<?php echo e($i); ?>][name]" class="form-control"
                                                     value="<?php echo e($member['name'] ?? ''); ?>">
                                          </div>
                                          <div class="col-md-5">
                                              <label class="form-label small">Role</label>
                                              <input type="text" name="team_members[<?php echo e($i); ?>][role]" class="form-control"
                                                     placeholder="e.g. Founder & Lead Guide" value="<?php echo e($member['role'] ?? ''); ?>">
                                          </div>
                                          <div class="col-12">
                                              <label class="form-label small">Short Bio (optional)</label>
                                              <textarea name="team_members[<?php echo e($i); ?>][bio]" class="form-control" rows="2"><?php echo e($member['bio'] ?? ''); ?></textarea>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          <?php endfor; ?>
                      </div>
                  </div>
              <?php endif; ?>

              <!-- General extra fields (usable on any page) -->
              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Extra Heading (optional)</label>
                  <div class="col-sm-9">
                      <input type="text" name="extra_heading" class="form-control" 
                             value="<?php echo e(old('extra_heading', $page->extra_heading)); ?>">
                  </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Extra Subheading (optional)</label>
                  <div class="col-sm-9">
                      <textarea name="extra_subheading" class="form-control" rows="3"><?php echo e(old('extra_subheading', $page->extra_subheading)); ?></textarea>
                  </div>
              </div>

              <!-- CTA (call to action) -->
              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">CTA Button Text</label>
                  <div class="col-sm-9">
                      <input type="text" name="cta_text" class="form-control" 
                             value="<?php echo e(old('cta_text', $page->cta_text)); ?>">
                  </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">CTA Button Link</label>
                  <div class="col-sm-9">
                      <input type="url" name="cta_link" class="form-control" 
                             value="<?php echo e(old('cta_link', $page->cta_link)); ?>">
                  </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="status" class="form-select">
                    <option value="draft" <?php echo e(old('status', $page->status) == 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="published" <?php echo e(old('status', $page->status) == 'published' ? 'selected' : ''); ?>>Published</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control" 
                         value="<?php echo e(old('order', $page->order)); ?>">
                  <small class="text-muted">Lower number = appears higher in lists/menus</small>
                </div>
              </div>

              <h5 class="card-title mt-5">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" 
                         value="<?php echo e(old('meta_title', $page->meta_title)); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3">
                    <?php echo e(old('meta_description', $page->meta_description)); ?>

                  </textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" 
                         value="<?php echo e(old('meta_keywords', $page->meta_keywords)); ?>">
                  <small class="text-muted">comma separated, e.g. safari, kilimanjaro, tanzania tours</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_edit" name="no_robots" value="1" <?php echo e(old('no_robots', $page->no_robots) ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="no_robots_edit">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this page is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>

              <?php echo $__env->make('admin.partials.mega-menu-section', [
                  'source' => $page,
                  'sourceType' => 'page',
              ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Page</button>
                  <a href="<?php echo e(route('admin.pages.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
      $(document).ready(function () {
          let counterIndex = $('#stats-counters-repeater .counter-item').length;

          $('#add-counter').on('click', function () {
              const newCounter = `
                  <div class="counter-item row g-2 mb-2 align-items-center">
                      <div class="col-3">
                          <input type="text" name="stats_counters[${counterIndex}][value]" class="form-control" placeholder="e.g. 50">
                      </div>
                      <div class="col-2">
                          <input type="text" name="stats_counters[${counterIndex}][suffix]" class="form-control" placeholder="+" value="+">
                      </div>
                      <div class="col-5">
                          <input type="text" name="stats_counters[${counterIndex}][label]" class="form-control" placeholder="e.g. Destinations">
                      </div>
                      <div class="col-2">
                          <button type="button" class="btn btn-sm btn-danger remove-counter">Remove</button>
                      </div>
                  </div>`;
              $('#stats-counters-repeater').append(newCounter);
              counterIndex++;
          });

          $(document).on('click', '.remove-counter', function () {
              $(this).closest('.counter-item').remove();
          });
      });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\pages\edit.blade.php ENDPATH**/ ?>