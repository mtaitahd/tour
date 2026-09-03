<div class="card mb-2 pricing-item-row" data-row="<?php echo e($itemIndex); ?>">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Item name</label>
                <input type="text" class="form-control form-control-sm ci-name" placeholder="e.g. Safari Car Transportation"
                       value="<?php echo e($item['name'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Charging basis</label>
                <select class="form-select form-select-sm ci-basis">
                    <?php $__currentLoopData = $basisLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $basisValue => $basisLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($basisValue); ?>" data-help="<?php echo e($basisHelp[$basisValue] ?? ''); ?>"
                            <?php echo e(($item['charging_basis'] ?? '') === $basisValue ? 'selected' : ''); ?>>
                            <?php echo e($basisLabel); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small class="form-text text-muted ci-basis-help"></small>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Quantity</label>
                <input type="text" class="form-control form-control-sm ci-quantity" value="<?php echo e($item['quantity'] ?? '1'); ?>"
                       autocomplete="off">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Level</label>
                <select class="form-select form-select-sm ci-level">
                    <option value="">(shared)</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger ci-remove">Remove</button>
            </div>
        </div>
        <div class="row g-2 align-items-end mt-0">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">High Season rate</label>
                <input type="text" class="form-control form-control-sm ci-rate-high" placeholder="0.00" autocomplete="off"
                       value="<?php echo e($item['rate_HIGH'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Low Wet Season rate</label>
                <input type="text" class="form-control form-control-sm ci-rate-low" placeholder="0.00" autocomplete="off"
                       value="<?php echo e($item['rate_LOW_WET'] ?? ''); ?>">
            </div>
            <div class="col-md-2 ci-cluster-night">
                <label class="form-label small text-muted mb-1">Nights</label>
                <input type="number" min="0" class="form-control form-control-sm ci-nights" value="<?php echo e($item['nights'] ?? '1'); ?>">
            </div>
            <div class="col-md-2 ci-cluster-occupancy">
                <label class="form-label small text-muted mb-1">Room occupancy</label>
                <input type="number" min="1" class="form-control form-control-sm ci-occupancy" value="<?php echo e($item['room_occupancy'] ?? '2'); ?>">
            </div>
            <div class="col-md-2 ci-cluster-capacity">
                <label class="form-label small text-muted mb-1">Vehicle capacity</label>
                <input type="number" min="1" class="form-control form-control-sm ci-vehicle-capacity" value="<?php echo e($item['vehicle_capacity'] ?? '6'); ?>">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input ci-included" <?php echo e(($item['included'] ?? true) ? 'checked' : ''); ?>>
                    <label class="form-check-label small">Included</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input ci-taxable" <?php echo e(($item['taxable'] ?? false) ? 'checked' : ''); ?>>
                    <label class="form-check-label small">Taxable</label>
                </div>
            </div>
        </div>
        <small class="form-text text-muted d-block mt-1 ci-notes"></small>
    </div>
</div><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\partials\pricing\cost-item-row.blade.php ENDPATH**/ ?>