<div id="pricing-manual-pane" class="pricing-pane" style="display:none;">
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Currency</label>
        <div class="col-sm-10">
            <input type="text" name="manual_currency" id="pricing-manual-currency"
                   class="form-control" style="max-width:120px;"
                   placeholder="USD" maxlength="3"
                   value="{{ $currentManualCurrency }}" autocomplete="off">
            <small class="form-text text-muted">3-letter ISO code, e.g. USD.</small>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-12">
            <p class="form-text text-muted mb-2">
                Enter the <strong>per-person</strong> price for each level and season (charge is per person).
                Group totals (2 / 4 / 6 persons) are computed automatically on save and never taken from the browser.
            </p>
            <div id="pricing-manual-grid"></div>
        </div>
    </div>
</div>