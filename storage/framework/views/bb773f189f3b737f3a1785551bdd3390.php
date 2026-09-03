
<div class="media-picker" data-picker-id="<?php echo e($pickerId); ?>" data-input-name="<?php echo e($name); ?>" data-multiple="<?php echo e($multiple ? '1' : '0'); ?>">

  <?php if($multiple): ?>
    <div class="media-picker-preview media-picker-sortable d-flex flex-wrap gap-2 mb-2">
      <?php $__currentLoopData = $selectedImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="position-relative media-picker-thumb" data-id="<?php echo e($image->id); ?>" style="cursor: grab;">
          <img src="<?php echo e($image->getUrl('thumb-webp') ?: $image->getUrl()); ?>" class="img-thumbnail" style="width: 90px; height: 90px; object-fit: cover;">
          <input type="hidden" name="<?php echo e($name); ?>[]" value="<?php echo e($image->id); ?>">
          <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 media-picker-remove" style="padding: 0 4px;">&times;</button>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php if($selectedImages): ?>
      <small class="text-muted d-block mb-2"><i class="bi bi-arrows-move"></i> Drag images to reorder.</small>
    <?php endif; ?>
  <?php else: ?>
    <div class="media-picker-preview mb-2">
      <?php if($selectedImage): ?>
        <div class="position-relative d-inline-block media-picker-thumb" data-id="<?php echo e($selectedImage->id); ?>">
          <img src="<?php echo e($selectedImage->getUrl('thumb-webp') ?: $selectedImage->getUrl()); ?>" class="img-thumbnail" style="max-height: 160px; object-fit: cover;">
          <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 media-picker-remove">&times;</button>
        </div>
      <?php endif; ?>
    </div>
    <input type="hidden" name="<?php echo e($name); ?>" value="<?php echo e($selectedImage?->id); ?>" class="media-picker-input">
  <?php endif; ?>

  <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#picker-<?php echo e($pickerId); ?>">
    <i class="bi bi-images"></i> <?php echo e($label); ?>

  </button>

  <?php echo $__env->make('admin.media.partials.modal-picker', [
      'pickerId' => $pickerId,
      'inputName' => $name,
      'multiple' => $multiple,
      'categories' => app(\App\Services\MediaLibraryService::class)->categoryTree(),
  ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>

<?php if (! $__env->hasRenderedOnce('4c2695b2-d6e7-4594-81b9-cc211e56dee8')): $__env->markAsRenderedOnce('4c2695b2-d6e7-4594-81b9-cc211e56dee8'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
/**
 * Listens for the 'media-picker:selected' event dispatched by modal-picker.blade.php
 * and applies the selection to whichever media picker instance it belongs to,
 * matched by pickerId. One listener handles every picker on the page.
 */
document.addEventListener('media-picker:selected', function (e) {
    const { pickerId, images } = e.detail;
    const widget = document.querySelector(`.media-picker[data-picker-id="${pickerId}"]`);
    if (!widget) return;

    const multiple = widget.dataset.multiple === '1';
    const inputName = widget.dataset.inputName;
    const preview = widget.querySelector('.media-picker-preview');

    if (multiple) {
        images.forEach(function (image) {
            if (preview.querySelector(`.media-picker-thumb[data-id="${image.id}"]`)) {
                return; // already added
            }
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative media-picker-thumb';
            wrapper.style.cursor = 'grab';
            wrapper.dataset.id = image.id;
            wrapper.innerHTML = `
                <img src="${image.thumb_url}" class="img-thumbnail" style="width: 90px; height: 90px; object-fit: cover;">
                <input type="hidden" name="${inputName}[]" value="${image.id}">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 media-picker-remove" style="padding: 0 4px;">&times;</button>
            `;
            preview.appendChild(wrapper);
        });
    } else {
        const image = images[0];
        if (!image) return;
        preview.innerHTML = `
            <div class="position-relative d-inline-block media-picker-thumb" data-id="${image.id}">
                <img src="${image.thumb_url}" class="img-thumbnail" style="max-height: 160px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 media-picker-remove">&times;</button>
            </div>`;
        const input = widget.querySelector('.media-picker-input');
        if (input) input.value = image.id;
    }
});

// Delegated remove-button handler for every picker on the page.
document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('media-picker-remove')) return;

    const thumb = e.target.closest('.media-picker-thumb');
    const widget = e.target.closest('.media-picker');
    if (!thumb || !widget) return;

    if (widget.dataset.multiple === '1') {
        thumb.remove();
    } else {
        thumb.remove();
        const input = widget.querySelector('.media-picker-input');
        if (input) input.value = '';
    }
});

/**
 * Drag-to-reorder for every multi-select picker's preview. Sortable.js is already
 * loaded globally by admin.layouts.app (confirmed in Phase 7), so no extra script
 * include is needed here. Re-ordering the DOM elements is all that's required — the
 * hidden inputs move along with their wrapper <div>, and form submission reads them
 * in their current DOM order, which is exactly the order
 * MediaLibraryService::setOrderedUsages() will store.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.media-picker-sortable').forEach(function (container) {
        if (typeof Sortable === 'undefined') return;
        new Sortable(container, {
            animation: 150,
            ghostClass: 'bg-light',
        });
    });
});

// New galleries created entirely via the modal (no images at page load) only get a
// .media-picker-sortable container once an image is first added — handled by always
// rendering the container (see the multiple-mode block above, which renders it
// regardless of whether the initial selection is empty), so Sortable.js above already
// covers it on page load. No additional init call is needed when images are added
// later via media-picker:selected, since Sortable.js attaches to the container
// element itself, not to its children individually.
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\components\media-picker.blade.php ENDPATH**/ ?>