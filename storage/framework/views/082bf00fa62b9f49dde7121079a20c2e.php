<?php
    $megaService = app(\App\Services\NavigationMegaMenuService::class);
    $parents = $megaService->parentDefinitions();
    $megaDefaults = $megaService->formValuesFor($source ?? null);
?>

<div class="card mt-4" id="mega-menu-card">
  <div class="card-header d-flex justify-content-between align-items-center bg-info text-white"
       data-bs-toggle="collapse" data-bs-target="#mega-menu-body"
       role="button" aria-expanded="<?php echo e(old('mega_menu.enabled', $megaDefaults['mega_menu.enabled']) ? 'true' : 'false'); ?>">
    <h6 class="mb-0"><i class="bi bi-menu-button-wide me-2"></i>Navigation Mega Menu</h6>
    <i class="bi bi-chevron-down"></i>
  </div>

  <div id="mega-menu-body" class="collapse <?php echo e(old('mega_menu.enabled', $megaDefaults['mega_menu.enabled']) ? 'show' : ''); ?>">
    <div class="card-body">
      <p class="text-muted small mb-3">
        Configure how this <?php echo e($sourceType ?? 'item'); ?> appears in the site's three-column navigation mega menu.
      </p>

      
      <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Show in Mega Menu</label>
        <div class="col-sm-9">
          <select name="mega_menu[enabled]" class="form-select mega-toggle">
            <option value="0" <?php echo e(!old('mega_menu.enabled', $megaDefaults['mega_menu.enabled']) ? 'selected' : ''); ?>>No</option>
            <option value="1" <?php echo e(old('mega_menu.enabled', $megaDefaults['mega_menu.enabled']) ? 'selected' : ''); ?>>Yes</option>
          </select>
        </div>
      </div>

      <div class="mega-fields" style="<?php echo e(!old('mega_menu.enabled', $megaDefaults['mega_menu.enabled']) ? 'display:none;' : ''); ?>">

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Parent Menu <span class="text-danger">*</span></label>
          <div class="col-sm-9">
            <select name="mega_menu[parent_key]" class="form-select">
              <option value="">— Select parent —</option>
              <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $def): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php echo e(old('mega_menu.parent_key', $megaDefaults['mega_menu.parent_key']) === $key ? 'selected' : ''); ?>>
                  <?php echo e($def['label']); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Mega Menu Label <span class="text-danger">*</span></label>
          <div class="col-sm-9">
            <input type="text" name="mega_menu[label]" class="form-control" maxlength="120"
                   placeholder="e.g. 4-Day Tanzania Safari"
                   value="<?php echo e(old('mega_menu.label', $megaDefaults['mega_menu.label'])); ?>">
            <small class="text-muted">Shown in the left column of the mega menu.</small>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Short Description</label>
          <div class="col-sm-9">
            <textarea name="mega_menu[description]" class="form-control" rows="3" maxlength="500"
                      placeholder="20–45 words describing this item for the middle column. Leave blank to auto-generate from the overview/content."><?php echo e(old('mega_menu.description', $megaDefaults['mega_menu.description'])); ?></textarea>
            <small class="text-muted">Shown in the middle column. Leave blank to use the tour/page overview.</small>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">CTA Button Label</label>
          <div class="col-sm-9">
            <input type="text" name="mega_menu[button_label]" class="form-control" maxlength="120"
                   placeholder="e.g. View This Tour"
                   value="<?php echo e(old('mega_menu.button_label', $megaDefaults['mega_menu.button_label'])); ?>">
            <small class="text-muted">Defaults to "View Tour Details" or "Read More" if left blank.</small>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Mega Menu Image</label>
          <div class="col-sm-9">
            <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'mega_menu[image_id]','selected' => old('mega_menu.image_id', $megaDefaults['mega_menu.image_id'] ?: null),'label' => 'Select Mega Menu Image'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
            <small class="text-muted d-block mt-1">Large image shown in the right column. Falls back to the tour/page hero image.</small>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Badge</label>
          <div class="col-sm-9">
            <input type="text" name="mega_menu[badge_text]" class="form-control" maxlength="40"
                   placeholder="e.g. Popular, New, Free PDF"
                   value="<?php echo e(old('mega_menu.badge_text', $megaDefaults['mega_menu.badge_text'])); ?>">
            <small class="text-muted">Optional small pill shown next to the label.</small>
          </div>
        </div>

        
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Display Order</label>
          <div class="col-sm-9">
            <input type="number" name="mega_menu[display_order]" class="form-control" min="0" max="9999"
                   value="<?php echo e(old('mega_menu.display_order', $megaDefaults['mega_menu.display_order'])); ?>">
            <small class="text-muted">Lower number = appears higher in the menu.</small>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php if (! $__env->hasRenderedOnce('7319a95e-bd56-4fda-b188-39badff3bdf1')): $__env->markAsRenderedOnce('7319a95e-bd56-4fda-b188-39badff3bdf1'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.mega-toggle');
  var fields = document.querySelector('.mega-fields');
  if (!toggle || !fields) return;
  toggle.addEventListener('change', function () {
    fields.style.display = toggle.value === '1' ? '' : 'none';
  });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\partials\mega-menu-section.blade.php ENDPATH**/ ?>