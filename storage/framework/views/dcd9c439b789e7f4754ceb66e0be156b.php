
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(isset($excludeId) && $category->id === $excludeId) continue; ?>

    <option value="<?php echo e($category->id); ?>" <?php echo e((string) $selectedId === (string) $category->id ? 'selected' : ''); ?>>
        <?php echo e(str_repeat('— ', $depth)); ?><?php echo e($category->name); ?>

    </option>

    <?php if($category->children->isNotEmpty()): ?>
        <?php echo $__env->make('admin.media.categories.partials.parent-options', [
            'categories' => $category->children,
            'depth' => $depth + 1,
            'selectedId' => $selectedId,
            'excludeId' => $excludeId ?? null,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\categories\partials\parent-options.blade.php ENDPATH**/ ?>