<div id="pricing-none-pane" class="pricing-pane" style="display:none;">
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Total Price</label>
        <div class="col-sm-10">
            <div class="d-flex gap-2 align-items-start flex-wrap">
                <input type="number" name="base_price" id="pricing-none-base-price"
                       class="form-control" style="max-width:180px;"
                       min="0" step="0.01" inputmode="decimal"
                       value="{{ old('base_price', $tourPackage->base_price ?? '') }}">
                <input type="text" name="currency" id="pricing-none-currency"
                       class="form-control" style="max-width:110px;"
                       placeholder="USD" maxlength="3"
                       value="{{ old('currency', $tourPackage->currency ?? 'USD') }}" autocomplete="off">
            </div>
            <small class="form-text text-muted">
                Single total price for the whole tour (3-letter ISO currency, e.g. USD).
                Leave empty to show &ldquo;Price on Request&rdquo;.
            </small>
        </div>
    </div>
</div>
