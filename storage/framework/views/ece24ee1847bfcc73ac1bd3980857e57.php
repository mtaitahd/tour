<?php if($fallbackUrl): ?>
  <picture>
    <?php if($srcset): ?>
      <source type="image/webp" srcset="<?php echo e($srcset); ?>" sizes="<?php echo e($sizes); ?>">
    <?php endif; ?>
    <img
      src="<?php echo e($fallbackUrl); ?>"
      alt="<?php echo e($alt); ?>"
      <?php if($class): ?> class="<?php echo e($class); ?>" <?php endif; ?>
      loading="<?php echo e($loading); ?>"
      decoding="async"
    >
  </picture>
<?php else: ?>
  
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\components\media-image.blade.php ENDPATH**/ ?>