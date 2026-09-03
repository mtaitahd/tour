
<?php
    use App\Models\Setting;
?>
<?php $__env->startSection('title', 'Site Settings'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Site Settings</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Global Settings</h5>

            <?php if(session('success')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" enctype="multipart/form-data">
              <?php echo csrf_field(); ?>

              <!-- Tabs for better organization -->
              <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab">Homepage</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Header & Footer</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">Social Links</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">SEO & Advanced</button>
                  </li>
              </ul>

              <div class="tab-content" id="settingsTabContent">
                  <!-- Homepage -->
                  <div class="tab-pane fade show active" id="homepage" role="tabpanel">
                      <h5 class="mt-4 mb-3">Hero</h5>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Hero Image</label>
                          <div class="col-sm-9">
                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'settings[hero_image_id]','selected' => Setting::get('hero_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                              <small class="text-muted d-block mt-1">Background image behind the hero title/search bar.</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Hero Title</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[hero_title]" class="form-control" value="<?php echo e(Setting::get('hero_title', 'Afro Vertex Tours')); ?>">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Hero Subtitle</label>
                          <div class="col-sm-9">
                              <textarea name="settings[hero_subtitle]" class="form-control" rows="2"><?php echo e(Setting::get('hero_subtitle')); ?></textarea>
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Highlight Strip Items</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[support_points]" class="form-control" value="<?php echo e(Setting::get('support_points')); ?>">
                              <small class="text-muted">Comma-separated — the scrolling green strip (e.g. "Personalized Itineraries,Expert Guides,24/7 Support").</small>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Our Story</h5>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Image</label>
                          <div class="col-sm-9">
                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'settings[our_story_image_id]','selected' => Setting::get('our_story_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                          <label class="col-sm-3 col-form-label">Title</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[our_story_title]" class="form-control" value="<?php echo e(Setting::get('our_story_title')); ?>">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Content</label>
                          <div class="col-sm-9">
                              <textarea name="settings[our_story_content]" class="form-control tinymce-editor-mini" rows="6"><?php echo e(Setting::get('our_story_content')); ?></textarea>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Get to Know About Us</h5>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Image</label>
                          <div class="col-sm-9">
                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'settings[get_to_know_image_id]','selected' => Setting::get('get_to_know_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                          <label class="col-sm-3 col-form-label">Title</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[get_to_know_title]" class="form-control" value="<?php echo e(Setting::get('get_to_know_title')); ?>">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Content</label>
                          <div class="col-sm-9">
                              <textarea name="settings[get_to_know_content]" class="form-control tinymce-editor-mini" rows="6"><?php echo e(Setting::get('get_to_know_content')); ?></textarea>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Homepage — About Us Section</h5>
                      <p class="text-muted">The "Our Story / About Us" section shown on the homepage with the photo stack.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Eyebrow Text</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_eyebrow]" class="form-control" value="<?php echo e(Setting::get('home_about_eyebrow')); ?>"
                                     placeholder="Our Story">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Section Title</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_title]" class="form-control" value="<?php echo e(Setting::get('home_about_title')); ?>"
                                     placeholder="About Us – Afro-Vertex Tours & Safaris">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Description</label>
                          <div class="col-sm-9">
                              <textarea name="settings[home_about_text]" class="form-control tinymce-editor-mini" rows="5"><?php echo e(Setting::get('home_about_text')); ?></textarea>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Checklist Items</label>
                          <div class="col-sm-9">
                              <textarea name="settings[home_about_checklist]" class="form-control" rows="3"><?php echo e(Setting::get('home_about_checklist')); ?></textarea>
                              <small class="text-muted">One item per line. E.g.:<br>Custom Safari Itineraries<br>24/7 Customer Support<br>Licensed &amp; Insured</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Button Text</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_btn_text]" class="form-control" value="<?php echo e(Setting::get('home_about_btn_text')); ?>"
                                     placeholder="Discover More">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Button Link</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_btn_link]" class="form-control" value="<?php echo e(Setting::get('home_about_btn_link')); ?>"
                                     placeholder="/pages/about-us">
                              <small class="text-muted">Relative path or full URL.</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Polaroid 1 Caption</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_polaroid_1]" class="form-control" value="<?php echo e(Setting::get('home_about_polaroid_1')); ?>"
                                     placeholder="Wild Encounters">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Polaroid 2 Caption</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_polaroid_2]" class="form-control" value="<?php echo e(Setting::get('home_about_polaroid_2')); ?>"
                                     placeholder="Memories Forever">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Polaroid 3 Caption</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[home_about_polaroid_3]" class="form-control" value="<?php echo e(Setting::get('home_about_polaroid_3')); ?>"
                                     placeholder="Breathtaking Views">
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Why Choose Us</h5>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label fw-bold">Feature Cards</label>
                          <div class="col-sm-9">
                              <div id="why-choose-us-repeater">
                                  <?php
                                      $whyChooseUsCards = Setting::json('why_choose_us_cards');
                                  ?>

                                  <?php $__currentLoopData = $whyChooseUsCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                      <div class="card-item card mb-3 shadow-sm">
                                          <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                              <h6 class="mb-0">Card <?php echo e($loop->iteration); ?></h6>
                                              <button type="button" class="btn btn-sm btn-danger remove-card">
                                                  <i class="bi bi-trash"></i> Remove
                                              </button>
                                          </div>
                                          <div class="card-body">
                                              <div class="row g-3">
                                                  <div class="col-md-3">
                                                      <label class="form-label">Icon</label>
                                                      <input type="text" name="why_choose_us_cards[<?php echo e($index); ?>][icon]" class="form-control"
                                                             value="<?php echo e($card['icon'] ?? ''); ?>" placeholder="e.g. isax isax-star">
                                                  </div>
                                                  <div class="col-md-9">
                                                      <label class="form-label">Title</label>
                                                      <input type="text" name="why_choose_us_cards[<?php echo e($index); ?>][title]" class="form-control"
                                                             value="<?php echo e($card['title'] ?? ''); ?>">
                                                  </div>
                                                  <div class="col-12">
                                                      <label class="form-label">Description</label>
                                                      <textarea name="why_choose_us_cards[<?php echo e($index); ?>][description]" class="form-control" rows="2"><?php echo e($card['description'] ?? ''); ?></textarea>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                              </div>
                              <button type="button" id="add-card" class="btn btn-outline-primary mt-2">
                                  <i class="bi bi-plus-circle"></i> Add Card
                              </button>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Call to Action</h5>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Title</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[cta_title]" class="form-control" value="<?php echo e(Setting::get('cta_title')); ?>"
                                     placeholder="e.g. Ready to Answer Africa's Call?">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Subtitle</label>
                          <div class="col-sm-9">
                              <textarea name="settings[cta_subtitle]" class="form-control" rows="2"><?php echo e(Setting::get('cta_subtitle')); ?></textarea>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Button Text</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[cta_button_text]" class="form-control" value="<?php echo e(Setting::get('cta_button_text', 'Plan My Trip')); ?>">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Button Link</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[cta_button_link]" class="form-control" value="<?php echo e(Setting::get('cta_button_link', '/contact')); ?>">
                              <small class="text-muted">A relative path (e.g. /contact) or full URL.</small>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">YouTube</h5>
                      <p class="text-muted">The homepage's "Subscribe" button uses your YouTube URL from the <strong>Social Links</strong> tab — no separate field needed here.</p>
                  </div>
                  <!-- General -->
                  <div class="tab-pane fade" id="general" role="tabpanel">
                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Site Name</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[site_name]" class="form-control" value="<?php echo e(Setting::get('site_name')); ?>">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Site Logo</label>
                          <div class="col-sm-9">
                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'settings[logo_image_id]','selected' => Setting::get('logo_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                              <?php if(!Setting::get('logo_image_id') && Setting::hasLogo()): ?>
                                  
                                  <div class="mb-3">
                                      <img src="<?php echo e(Setting::logoUrl()); ?>" alt="Current Logo"
                                           style="max-height: 100px; max-width: 300px; object-fit: contain;">
                                      <small class="d-block text-muted mt-1">Uploaded the old way — pick a new one from the Media Library to switch over.</small>
                                  </div>
                              <?php endif; ?>
                              <small class="text-muted d-block mt-1">Recommended: transparent PNG or SVG</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Default Currency</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[default_currency]" class="form-control" value="<?php echo e(Setting::get('default_currency', 'USD')); ?>">
                              <small>e.g. USD, EUR, TZS</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Timezone</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[timezone]" class="form-control" value="<?php echo e(Setting::get('timezone', 'Africa/Dar_es_Salaam')); ?>">
                              <small>e.g. Africa/Dar_es_Salaam, UTC, Europe/London</small>
                          </div>
                      </div>
                  </div>

                  <!-- Contact & Footer -->
                  <div class="tab-pane fade" id="contact" role="tabpanel">
                      <h5 class="mb-3">Contact Details</h5>
                      <p class="text-muted">Used across the site header, footer, and Contact page.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Phone Number</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_phone]" class="form-control" value="<?php echo e(Setting::get('footer_phone')); ?>"
                                     placeholder="+255 760 096 715">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">WhatsApp Number</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[whatsapp_number]" class="form-control" value="<?php echo e(Setting::get('whatsapp_number')); ?>"
                                     placeholder="255760096715">
                              <small class="text-muted">Digits only, with country code, no "+" or spaces — used to build the wa.me chat link.</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Email Address</label>
                          <div class="col-sm-9">
                              <input type="email" name="settings[site_email]" class="form-control" value="<?php echo e(Setting::get('site_email')); ?>">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">WeChat ID (optional)</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[wechat_id]" class="form-control" value="<?php echo e(Setting::get('wechat_id')); ?>">
                              <small class="text-muted">Leave blank to hide from the header entirely.</small>
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">LINE ID (optional)</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[line_id]" class="form-control" value="<?php echo e(Setting::get('line_id')); ?>">
                              <small class="text-muted">Leave blank to hide from the header entirely.</small>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Footer Content</h5>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Footer Copyright Text</label>
                          <div class="col-sm-9">
                              <textarea name="settings[footer_copyright]" class="form-control" rows="2"><?php echo e(Setting::get('footer_copyright')); ?></textarea>
                              <small class="text-muted">Leave blank to use "Afro-Vertex Tours & Safaris" with the current year.</small>
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Footer Address</label>
                          <div class="col-sm-9">
                              <textarea name="settings[footer_address]" class="form-control" rows="3"><?php echo e(Setting::get('footer_address')); ?></textarea>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Footer — About Column</h5>
                      <p class="text-muted">The "About" column shown on the left side of the footer.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">About Heading</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_about_heading]" class="form-control" value="<?php echo e(Setting::get('footer_about_heading')); ?>"
                                     placeholder="e.g. About Afro-Vertex Tours & Safaris">
                              <small class="text-muted">Leave blank for "About {Site Name}".</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">About Text</label>
                          <div class="col-sm-9">
                              <textarea name="settings[footer_about_text]" class="form-control" rows="4"><?php echo e(Setting::get('footer_about_text')); ?></textarea>
                              <small class="text-muted">Leave blank for auto-generated text from site statistics.</small>
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">"More About Us" Link Text</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_about_link_text]" class="form-control" value="<?php echo e(Setting::get('footer_about_link_text')); ?>"
                                     placeholder="More About Us">
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Footer — Column Headings</h5>
                      <p class="text-muted">Customise the heading text for each footer link column.</p>

                      <?php
                          $footerHeadings = [
                              'footer_heading_statistics'  => ['Our Statistics', 'Statistics column heading'],
                              'footer_heading_parks'       => ['Safaris by Park', 'Parks column heading'],
                              'footer_heading_countries'   => ['Safaris by Country', 'Countries column heading'],
                              'footer_heading_types'       => ['Safaris by Type', 'Types column heading'],
                              'footer_heading_general'     => ['General', 'General links column heading'],
                              'footer_heading_partners'    => ['Our Partners', 'Partners section heading'],
                              'footer_empty_statistics'    => ['Statistics coming soon.', 'Statistics empty state'],
                              'footer_empty_parks'         => ['Coming soon.', 'Parks empty state'],
                              'footer_empty_general'       => ['Coming soon.', 'General links empty state'],
                          ];
                      ?>

                      <?php $__currentLoopData = $footerHeadings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$default, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <div class="row mb-3">
                              <label class="col-sm-3 col-form-label"><?php echo e($default); ?></label>
                              <div class="col-sm-9">
                                  <input type="text" name="settings[<?php echo e($key); ?>]" class="form-control" value="<?php echo e(Setting::get($key)); ?>"
                                         placeholder="<?php echo e($default); ?>">
                                  <small class="text-muted"><?php echo e($desc); ?>. Leave blank for "<?php echo e($default); ?>".</small>
                              </div>
                          </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                      <hr class="my-4">
                      <h5 class="mb-3">Footer — Copyright Links</h5>
                      <p class="text-muted">Labels for the links shown in the copyright bar at the bottom.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Privacy Policy Label</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_copy_privacy]" class="form-control" value="<?php echo e(Setting::get('footer_copy_privacy')); ?>"
                                     placeholder="Privacy Policy">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Terms &amp; Conditions Label</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_copy_terms]" class="form-control" value="<?php echo e(Setting::get('footer_copy_terms')); ?>"
                                     placeholder="Terms & Conditions">
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Review Links (optional)</h5>
                      <p class="text-muted">Shown in the footer's Reviews column. Any left blank are simply not shown — the whole column disappears if all three are empty.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Google Reviews URL</label>
                          <div class="col-sm-9">
                              <input type="url" name="settings[google_reviews_url]" class="form-control" value="<?php echo e(Setting::get('google_reviews_url')); ?>">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">TripAdvisor URL</label>
                          <div class="col-sm-9">
                              <input type="url" name="settings[tripadvisor_url]" class="form-control" value="<?php echo e(Setting::get('tripadvisor_url')); ?>">
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Trustpilot URL</label>
                          <div class="col-sm-9">
                              <input type="url" name="settings[trustpilot_url]" class="form-control" value="<?php echo e(Setting::get('trustpilot_url')); ?>">
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Navigation "All Tours" Menu Image</h5>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">Menu Image</label>
                          <div class="col-sm-9">
                              <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'settings[activities_menu_image_id]','selected' => Setting::get('activities_menu_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                              <small class="text-muted d-block mt-1">Shown inside the "All Tours" dropdown in the main navigation.</small>
                          </div>
                      </div>

                      <hr class="my-4">
                      <h5 class="mb-3">Partner Logos (optional)</h5>
                      <p class="text-muted">Up to 6 slots below. Leave a slot's name blank to skip it — the whole footer section disappears if all slots are empty.</p>

                      <?php
                          $partnerLogos = old('partner_logos', Setting::json('partner_logos'));
                      ?>

                      <?php for($i = 0; $i < 6; $i++): ?>
                          <?php $partner = $partnerLogos[$i] ?? []; ?>
                          <div class="row g-3 align-items-center mb-3 border-bottom pb-3">
                              <div class="col-md-3">
                                  <label class="form-label small">Logo</label>
                                  <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'partner_logos['.e($i).'][logo_image_id]','selected' => $partner['logo_image_id'] ?? null,'label' => 'Select Logo'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                                  <input type="text" name="partner_logos[<?php echo e($i); ?>][name]" class="form-control" value="<?php echo e($partner['name'] ?? ''); ?>">
                              </div>
                              <div class="col-md-5">
                                  <label class="form-label small">Link (optional)</label>
                                  <input type="url" name="partner_logos[<?php echo e($i); ?>][link]" class="form-control" value="<?php echo e($partner['link'] ?? ''); ?>">
                              </div>
                          </div>
                      <?php endfor; ?>
                  </div>

                  <!-- Social Links -->
                  <div class="tab-pane fade" id="social" role="tabpanel">
                      <?php $__currentLoopData = [
                          'facebook' => 'Facebook URL',
                          'instagram' => 'Instagram URL',
                          'twitter' => 'X / Twitter URL',
                          'youtube' => 'YouTube URL',
                          'linkedin' => 'LinkedIn URL',
                      ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <div class="row mb-3">
                              <label class="col-sm-3 col-form-label"><?php echo e($label); ?></label>
                              <div class="col-sm-9">
                                  <input type="url" name="settings[social_<?php echo e($platform); ?>]" class="form-control" 
                                         value="<?php echo e(Setting::get('social_' . $platform)); ?>">
                              </div>
                          </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>

                  <!-- SEO & Advanced -->
                  <div class="tab-pane fade" id="seo" role="tabpanel">
                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Google Analytics ID</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[ga_id]" class="form-control" value="<?php echo e(Setting::get('ga_id')); ?>">
                              <small>e.g. G-XXXXXXXXXX or UA-XXXXXXXXX-X</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">robots.txt</label>
                          <div class="col-sm-9">
                              <div class="alert alert-info mb-0 py-2">
                                  <i class="bi bi-info-circle me-1"></i>
                                  <strong>Auto-generated</strong> — robots.txt is served automatically by <code>robots.php</code> with the correct Disallow rules and Sitemap directive. No manual editing needed.
                              </div>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Enable Sitemap.xml</label>
                          <div class="col-sm-9">
                              <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="settings[enable_sitemap]" value="1" 
                                         <?php echo e(Setting::get('enable_sitemap') ? 'checked' : ''); ?>>
                                  <label class="form-check-label">Generate basic sitemap.xml</label>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Submit -->
              <div class="row mt-5">
                  <div class="col-12 text-end">
                      <button type="submit" class="btn btn-primary btn-lg px-5">
                          <i class="bi bi-save me-2"></i> Save All Settings
                      </button>
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
          let cardIndex = $('#why-choose-us-repeater .card-item').length;

          $('#add-card').click(function () {
              cardIndex++;

              let newCard = `
                  <div class="card-item card mb-3 shadow-sm">
                      <div class="card-header d-flex justify-content-between align-items-center bg-light">
                          <h6 class="mb-0">Card ${cardIndex}</h6>
                          <button type="button" class="btn btn-sm btn-danger remove-card">
                              <i class="bi bi-trash"></i> Remove
                          </button>
                      </div>
                      <div class="card-body">
                          <div class="row g-3">
                              <div class="col-md-3">
                                  <label class="form-label">Icon</label>
                                  <input type="text" name="why_choose_us_cards[${cardIndex}][icon]" class="form-control" placeholder="e.g. isax isax-star">
                              </div>
                              <div class="col-md-9">
                                  <label class="form-label">Title</label>
                                  <input type="text" name="why_choose_us_cards[${cardIndex}][title]" class="form-control">
                              </div>
                              <div class="col-12">
                                  <label class="form-label">Description</label>
                                  <textarea name="why_choose_us_cards[${cardIndex}][description]" class="form-control" rows="2"></textarea>
                              </div>
                          </div>
                      </div>
                  </div>`;

              $('#why-choose-us-repeater').append(newCard);
          });

          $(document).on('click', '.remove-card', function () {
              $(this).closest('.card-item').remove();
          });
      });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\settings\index.blade.php ENDPATH**/ ?>