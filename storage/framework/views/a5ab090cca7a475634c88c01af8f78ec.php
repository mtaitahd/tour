<?php
    $legacySeasons = $tourPackage->season_pricing ?? [];
    $legacyCurrency = $tourPackage->currency ?? 'USD';
?>
<div class="alert alert-info">
    <strong>Legacy pricing preserved.</strong> This tour was created before the new pricing system and carries
    SILVER / GOLD / PLATINUM season pricing in <?php echo e($legacyCurrency); ?>. It is kept intact and continues to drive
    customer-facing display until the reader-side phase. Choosing a new-system pricing source below adds the
    new package prices alongside it without touching the legacy data.
</div>
<?php if(! empty($legacySeasons)): ?>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Season</th>
                    <th class="text-end">2 persons</th>
                    <th class="text-end">4 persons</th>
                    <th class="text-end">6 persons</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $legacySeasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($sp['season'] ?? '—'); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) ($sp['price_2p'] ?? 0), 2)); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) ($sp['price_4p'] ?? 0), 2)); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) ($sp['price_6p'] ?? 0), 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-muted">No legacy season pricing recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\partials\pricing\legacy.blade.php ENDPATH**/ ?>