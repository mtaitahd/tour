
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li class="d-flex justify-content-between align-items-center py-2 border-bottom" style="padding-left: <?php echo e($depth * 24); ?>px;">
        <div>
            <?php if($depth > 0): ?>
                <i class="bi bi-arrow-return-right text-muted me-1"></i>
            <?php endif; ?>
            <span class="fw-medium"><?php echo e($category->name); ?></span>
            <span class="badge bg-light text-muted ms-2"><?php echo e($category->media_count); ?> image<?php echo e($category->media_count === 1 ? '' : 's'); ?></span>
        </div>
        <div>
            <a href="<?php echo e(route('admin.media.categories.edit', $category)); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
            </a>
            <form action="<?php echo e(route('admin.media.categories.destroy', $category)); ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete &quot;<?php echo e($category->name); ?>&quot;<?php echo e($category->children->isNotEmpty() ? ' and its ' . $category->children->count() . ' sub-categor' . ($category->children->count() === 1 ? 'y' : 'ies') : ''); ?>? Images in it will not be deleted, just unassigned.');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </li>

    <?php if($category->children->isNotEmpty()): ?>
        <?php echo $__env->make('admin.media.categories.partials.tree-row', ['categories' => $category->children, 'depth' => $depth + 1], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\categories\partials\tree-row.blade.php ENDPATH**/ ?>