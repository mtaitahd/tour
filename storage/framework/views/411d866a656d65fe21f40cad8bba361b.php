<div id="pricing-calculator-pane" class="pricing-pane" style="display:none;">
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Settings</label>
        <div class="col-sm-10">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="pricing-tax-enabled" checked>
                        <label class="form-check-label" for="pricing-tax-enabled">Apply tax (VAT)</label>
                    </div>
                    <div class="input-group input-group-sm" style="max-width:220px;">
                        <span class="input-group-text">Rate</span>
                        <input type="number" step="0.01" min="0" max="100" class="form-control"
                               id="pricing-tax-percent" value="18">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label small">Markup</label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" id="pricing-markup-type" style="max-width:150px;">
                            <option value="percent" selected>Percent (%)</option>
                            <option value="fixed">Fixed amount</option>
                        </select>
                        <input type="number" step="0.01" min="0" class="form-control"
                               id="pricing-markup-value" value="0">
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label small">Round per-person price to nearest</label>
                    <select class="form-select form-select-sm" id="pricing-rounding">
                        <option value="none" selected>No rounding</option>
                        <option value="1">$1</option>
                        <option value="5">$5</option>
                        <option value="10">$10</option>
                        <option value="50">$50</option>
                        <option value="100">$100</option>
                    </select>
                </div>
                <div class="col-lg-4 pricing-calculator-multi">
                    <label class="form-label small">Duration</label>
                    <div class="input-group input-group-sm">
                        <input type="number" min="1" class="form-control" id="pricing-days" value="3" placeholder="Days">
                        <span class="input-group-text">days</span>
                        <input type="number" min="0" class="form-control" id="pricing-nights" value="2" placeholder="Nights">
                        <span class="input-group-text">nights</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Cost items</strong>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pricing-add-item">Add cost item</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pricing-load-predefined">
                        Load predefined (<?php echo e(count($predefinedCosts) > 0 ? implode(' / ', array_keys($predefinedCosts)) : '—'); ?>)
                    </button>
                </div>
            </div>
            <div id="pricing-items-list"></div>
            <p class="form-text text-muted mb-2">
                Rates are per the chosen charging basis. Rows with an empty level are shared across all levels;
                level-specific rows only affect that level. Single-day tours only have the STANDARD level.
            </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary" id="pricing-preview-btn">Preview prices</button>
            <span class="form-text text-muted ms-2" id="pricing-preview-status"></span>
        </div>
    </div>

    <input type="hidden" name="calculator_payload" id="pricing-calculator-payload" value="">
</div><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\partials\pricing\calculator.blade.php ENDPATH**/ ?>