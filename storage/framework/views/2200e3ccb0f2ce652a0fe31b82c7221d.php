
<?php
    $paginationCurrent = $paginator->currentPage();
    $paginationLast    = $paginator->lastPage();

    // Smart window: current page ±2, always showing first & last page,
    // one ellipsis per skipped group, all pages when seven or fewer.
    $windowStart = max(1, min($paginationCurrent - 2, max(1, $paginationLast - 4)));
    $windowEnd   = min($paginationLast, $windowStart + 4);
    $desktopPaginationPages = range($windowStart, $windowEnd);
?>

<?php if($paginator->hasPages()): ?>
    <nav class="sfb-pagination" aria-label="Tour results pages">
        <?php if($paginator->onFirstPage()): ?>
            <span class="sfb-page-btn sfb-page-btn--wide is-disabled" aria-disabled="true">
                <span aria-hidden="true">&lsaquo;</span>
                <span class="sfb-page-btn__word">Prev</span>
            </span>
        <?php else: ?>
            <a class="sfb-page-btn sfb-page-btn--wide" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" data-sfb-page-link aria-label="Go to previous page">
                <span aria-hidden="true">&lsaquo;</span>
                <span class="sfb-page-btn__word">Prev</span>
            </a>
        <?php endif; ?>

        <div class="sfb-pagination__pages sfb-pagination__pages--desktop" aria-label="Page numbers">
            <?php if(! in_array(1, $desktopPaginationPages, true)): ?>
                <a class="sfb-page-btn" href="<?php echo e($paginator->url(1)); ?>" data-sfb-page-link aria-label="Go to page 1">1</a>
                <?php if($windowStart > 2): ?>
                    <span class="sfb-page-ellipsis" aria-hidden="true">&hellip;</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php $__currentLoopData = $desktopPaginationPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pageNumber): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($pageNumber === $paginationCurrent): ?>
                    <span class="sfb-page-btn is-active" aria-current="page" aria-label="Current page, page <?php echo e($pageNumber); ?>"><?php echo e($pageNumber); ?></span>
                <?php else: ?>
                    <a class="sfb-page-btn" href="<?php echo e($paginator->url($pageNumber)); ?>" data-sfb-page-link aria-label="Go to page <?php echo e($pageNumber); ?>"><?php echo e($pageNumber); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if(! in_array($paginationLast, $desktopPaginationPages, true)): ?>
                <?php if($windowEnd < $paginationLast - 1): ?>
                    <span class="sfb-page-ellipsis" aria-hidden="true">&hellip;</span>
                <?php endif; ?>
                <a class="sfb-page-btn" href="<?php echo e($paginator->url($paginationLast)); ?>" data-sfb-page-link aria-label="Go to page <?php echo e($paginationLast); ?>"><?php echo e($paginationLast); ?></a>
            <?php endif; ?>
        </div>

        
        <div class="sfb-pagination__summary">
            Page <?php echo e($paginationCurrent); ?> of <?php echo e($paginationLast); ?>

        </div>

        <?php if($paginator->hasMorePages()): ?>
            <a class="sfb-page-btn sfb-page-btn--wide" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" data-sfb-page-link aria-label="Go to next page">
                <span class="sfb-page-btn__word">Next</span>
                <span aria-hidden="true">&rsaquo;</span>
            </a>
        <?php else: ?>
            <span class="sfb-page-btn sfb-page-btn--wide is-disabled" aria-disabled="true">
                <span class="sfb-page-btn__word">Next</span>
                <span aria-hidden="true">&rsaquo;</span>
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\tours\partials\pagination.blade.php ENDPATH**/ ?>