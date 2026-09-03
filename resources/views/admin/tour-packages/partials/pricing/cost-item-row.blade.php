<div class="card mb-2 pricing-item-row" data-row="{{ $itemIndex }}">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Item name</label>
                <input type="text" class="form-control form-control-sm ci-name" placeholder="e.g. Safari Car Transportation"
                       value="{{ $item['name'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Charging basis</label>
                <select class="form-select form-select-sm ci-basis">
                    @foreach ($basisLabels as $basisValue => $basisLabel)
                        <option value="{{ $basisValue }}" data-help="{{ $basisHelp[$basisValue] ?? '' }}"
                            {{ ($item['charging_basis'] ?? '') === $basisValue ? 'selected' : '' }}>
                            {{ $basisLabel }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted ci-basis-help"></small>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Quantity</label>
                <input type="text" class="form-control form-control-sm ci-quantity" value="{{ $item['quantity'] ?? '1' }}"
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
                       value="{{ $item['rate_HIGH'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Low Wet Season rate</label>
                <input type="text" class="form-control form-control-sm ci-rate-low" placeholder="0.00" autocomplete="off"
                       value="{{ $item['rate_LOW_WET'] ?? '' }}">
            </div>
            <div class="col-md-2 ci-cluster-night">
                <label class="form-label small text-muted mb-1">Nights</label>
                <input type="number" min="0" class="form-control form-control-sm ci-nights" value="{{ $item['nights'] ?? '1' }}">
            </div>
            <div class="col-md-2 ci-cluster-occupancy">
                <label class="form-label small text-muted mb-1">Room occupancy</label>
                <input type="number" min="1" class="form-control form-control-sm ci-occupancy" value="{{ $item['room_occupancy'] ?? '2' }}">
            </div>
            <div class="col-md-2 ci-cluster-capacity">
                <label class="form-label small text-muted mb-1">Vehicle capacity</label>
                <input type="number" min="1" class="form-control form-control-sm ci-vehicle-capacity" value="{{ $item['vehicle_capacity'] ?? '6' }}">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input ci-included" {{ ($item['included'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label small">Included</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input ci-taxable" {{ ($item['taxable'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label small">Taxable</label>
                </div>
            </div>
        </div>
        <small class="form-text text-muted d-block mt-1 ci-notes"></small>
    </div>
</div>