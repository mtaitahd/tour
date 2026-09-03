
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li>
        <a href="<?php echo e(route('admin.media.index', array_merge(request()->query(), ['category_id' => $category->id]))); ?>"
           class="d-flex justify-content-between align-items-center text-decoration-none px-2 py-1 rounded small <?php echo e((int) $activeCategoryId === $category->id ? 'bg-primary text-white' : 'text-body'); ?>">
            <span><?php echo e($category->name); ?></span>
            <span class="badge <?php echo e((int) $activeCategoryId === $category->id ? 'bg-white text-primary' : 'bg-light text-muted'); ?>"><?php echo e($category->media_count); ?></span>
        </a>

        <?php if($category->children->isNotEmpty()): ?>
            <ul class="list-unstyled ms-3 mt-1">
                <?php echo $__env->make('admin.media.partials.category-tree', ['categories' => $category->children, 'activeCategoryId' => $activeCategoryId], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </ul>
        <?php endif; ?>
    </li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\partials\category-tree.blade.php ENDPATH**/ ?>