
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="form-check">
        <input type="checkbox" name="category_ids[]" value="<?php echo e($category->id); ?>"
               class="form-check-input" id="cat-<?php echo e($category->id); ?>"
               <?php echo e(in_array($category->id, $selectedIds) ? 'checked' : ''); ?>>
        <label class="form-check-label" for="cat-<?php echo e($category->id); ?>"><?php echo e($category->name); ?></label>
    </div>

    <?php if($category->children->isNotEmpty()): ?>
        <div class="ms-4">
            <?php echo $__env->make('admin.media.partials.category-checkboxes', ['categories' => $category->children, 'selectedIds' => $selectedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\partials\category-checkboxes.blade.php ENDPATH**/ ?>