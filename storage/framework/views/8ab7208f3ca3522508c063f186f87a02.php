<?php $currency = $currency ?? 'USD'; ?>
<div class="card mt-3">
    <div class="card-header bg-light">
        <strong>Price preview</strong>
        <span class="text-muted small float-end">
            <?php echo e(($results['meta']['duration_type'] ?? '') === 'single_day' ? 'Single-day' : 'Multi-day'); ?>

            &middot; <?php echo e(strtoupper($currency)); ?>

        </span>
    </div>
    <div class="card-body">
        <?php $meta = $results['meta'] ?? []; ?>
        <div class="mb-2 small text-muted">
            <?php if(! empty($meta['tour_type'])): ?> <span class="badge bg-secondary"><?php echo e($meta['tour_type']); ?></span> <?php endif; ?>
            <?php if(! empty($meta['package_category'])): ?> <span class="badge bg-secondary"><?php echo e($meta['package_category']); ?></span> <?php endif; ?>
            <?php if(($meta['tax_enabled'] ?? false)): ?> <span class="badge bg-light text-dark border">Tax <?php echo e($meta['tax_percentage'] ?? '0'); ?>%</span> <?php endif; ?>
            <?php if(! empty($meta['markup_value']) && (float) $meta['markup_value'] > 0): ?>
                <span class="badge bg-light text-dark border">Markup <?php echo e($meta['markup_value']); ?> <?php echo e(($meta['markup_type'] ?? '') === 'percent' ? '%' : strtoupper($currency)); ?></span>
            <?php endif; ?>
            <?php if(! empty($meta['rounding_rule']) && $meta['rounding_rule'] !== 'none'): ?>
                <span class="badge bg-light text-dark border">Rounded to <?php echo e($meta['rounding_rule']); ?></span>
            <?php endif; ?>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = ($results['groups'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season => $levelGroups): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <h6 class="mt-2 border-bottom pb-1">
                <?php echo e($season === 'HIGH' ? 'High Season' : 'Low / Wet Season'); ?>

                <small class="text-muted">(<?php echo e($season); ?>)</small>
            </h6>
            <?php $__currentLoopData = $levelGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $levelKey => $sizes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-2">
                    <strong class="small"><?php echo e($levelKey); ?></strong>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-1">
                            <thead class="table-light">
                                <tr>
                                    <th>Group size</th>
                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center"><?php echo e($clients); ?> clients</th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Per person</td>
                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-end"><?php echo e(strtoupper($currency)); ?> <?php echo e($group['final_per_person'] ?? '0.00'); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <tr>
                                    <td>Group total</td>
                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-end"><?php echo e(strtoupper($currency)); ?> <?php echo e($group['final_group_total'] ?? '0.00'); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <tr>
                                    <td>Cost / tax / markup</td>
                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-end small text-muted">
                                            cost <?php echo e($group['cost_total'] ?? '0.00'); ?>

                                            &middot; tax <?php echo e($group['tax_amount'] ?? '0.00'); ?>

                                            &middot; markup <?php echo e($group['markup_amount'] ?? '0.00'); ?>

                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <tr class="d-none pricing-details-row">
                                    <td colspan="<?php echo e(count($sizes) + 1); ?>" class="p-0">
                                        <table class="table table-sm mb-0 small">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Basis</th>
                                                    <th>Rate</th>
                                                    <th>Qty</th>
                                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <th class="text-end"><?php echo e($clients); ?> clients</th>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tr>
                                            </thead>
                                            <?php
                                                $firstLines = [];
                                                foreach ($sizes as $clients => $group) {
                                                    if (empty($firstLines) && ! empty($group['lines'])) {
                                                        $firstLines = $group['lines'];
                                                    }
                                                }
                                                $detailKeys = array_keys($firstLines);
                                            ?>
                                            <tbody>
                                                <?php $__currentLoopData = $firstLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $li => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($line['name']); ?></td>
                                                        <td><?php echo e($line['basis']); ?></td>
                                                        <td><?php echo e($line['rate']); ?></td>
                                                        <td><?php echo e($line['quantity']); ?></td>
                                                        <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <td class="text-end"><?php echo e($group['lines'][$li]['total'] ?? '—'); ?></td>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="fw-bold">
                                                    <td colspan="4">Final per person</td>
                                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clients => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <td class="text-end"><?php echo e($group['final_per_person'] ?? '0.00'); ?></td>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="<?php echo e(count($sizes) + 1); ?>" class="text-center p-0 bg-transparent">
                                        <a href="javascript:void(0);" class="small pricing-details-toggle">Show item breakdown</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted mb-0">No price groups were produced.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    (function () {
        var root = document.currentScript.parentElement || document.body;
        var target = root.closest ? root.closest('#pricing-preview-target') || root : root;

        function wire(t) {
            Array.prototype.forEach.call(t.querySelectorAll('.pricing-details-toggle'), function (toggle) {
                toggle.addEventListener('click', function () {
                    var row = toggle.closest('tr').previousElementSibling;
                    var hidden = !row.classList.contains('d-none');
                    row.classList.toggle('d-none', hidden);
                    toggle.textContent = hidden ? 'Show item breakdown' : 'Hide item breakdown';
                });
            });
        }

        wire(target);
    })();
</script><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\partials\pricing\preview.blade.php ENDPATH**/ ?>