<script>
window.__pricing = {
    levelCatalog: <?php echo json_encode($levelCatalog, 15, 512) ?>,
    predefinedCosts: <?php echo json_encode($predefinedCosts, 15, 512) ?>,
    basisLabels: <?php echo json_encode($basisLabels, 15, 512) ?>,
    singleDayLevel: <?php echo json_encode($singleDayLevel, 15, 512) ?>,
    seasons: ['HIGH', 'LOW_WET'],
    manualRows: <?php echo json_encode($manualRows ?? [], 15, 512) ?>,
    existingPayload: <?php echo json_encode($existingCalculatorPayload, 15, 512) ?>,
    source: <?php echo json_encode($currentSource, 15, 512) ?>,
    duration: <?php echo json_encode($currentDuration, 15, 512) ?>,
    category: <?php echo json_encode($currentCategory, 15, 512) ?>,
    tourType: <?php echo json_encode($currentTourType, 15, 512) ?>,
    previewUrl: <?php echo json_encode(route('admin.tour-price-calculator.preview'), 15, 512) ?>,
    baseCurrency: <?php echo json_encode($currentManualCurrency ?? 'USD', 15, 512) ?>,
    items: [],
    previewDebounce: null,
    lastDigest: null,
};

(function () {
    'use strict';
    var P = window.__pricing;

    function money(n) { return (parseFloat(n) || 0).toFixed(2); }

    // ── Manual grid ──────────────────────────────────────────────────────────
    function manualCombos() {
        var levels = P.duration === 'single_day'
            ? P.singleDayLevel
            : (P.levelCatalog[P.category] ? P.levelCatalog[P.category].levels : {});
        var combos = [];
        P.seasons.forEach(function (season) {
            Object.keys(levels).forEach(function (lvl) {
                combos.push({ level_key: lvl, level_name: levels[lvl], season_code: season });
            });
        });
        return combos;
    }

    function manualRowHtml(i, combo, row) {
        return '<tr>'
            + '<td style="min-width:210px;">'
            + '<input type="hidden" name="manual_prices[' + i + '][level_key]" value="' + combo.level_key + '">'
            + '<input type="hidden" name="manual_prices[' + i + '][season_code]" value="' + combo.season_code + '">'
            + '<span class="badge bg-light text-dark">' + combo.season_code + '</span> '
            + '<strong>' + combo.level_name + '</strong></td>'
            + '<td><input type="text" name="manual_prices[' + i + '][price_2p]" '
            + 'class="form-control form-control-sm mp-price" data-size="2p" '
            + 'value="' + (row.price_2p || '') + '" autocomplete="off"></td>'
            + '<td><input type="text" name="manual_prices[' + i + '][price_4p]" '
            + 'class="form-control form-control-sm mp-price" data-size="4p" '
            + 'value="' + (row.price_4p || '') + '" autocomplete="off"></td>'
            + '<td><input type="text" name="manual_prices[' + i + '][price_6p]" '
            + 'class="form-control form-control-sm mp-price" data-size="6p" '
            + 'value="' + (row.price_6p || '') + '" autocomplete="off"></td>'
            + '<td class="align-middle text-muted small mp-totals"></td>'
            + '</tr>';
    }

    function renderManualGrid() {
        var combos = manualCombos();
        var existing = {};
        (P.manualRows || []).forEach(function (r) { existing[r.level_key + '|' + r.season_code] = r; });

        if (!combos.length) {
            $('#pricing-manual-grid').html('<p class="form-text text-muted mb-0">Select a category to configure manual prices.</p>');
            return;
        }

        var rows = '';
        combos.forEach(function (combo, i) {
            rows += manualRowHtml(i, combo, existing[combo.level_key + '|' + combo.season_code] || {});
        });

        $('#pricing-manual-grid').html(
            '<div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">'
            + '<thead class="table-light"><tr><th>Season / Level</th><th style="min-width:120px;">2 persons</th>'
            + '<th style="min-width:120px;">4 persons</th><th style="min-width:120px;">6 persons</th>'
            + '<th>Group totals</th></tr></thead>'
            + '<tbody>' + rows + '</tbody></table>'
            + '<p class="form-text text-muted mt-1 mb-0">Group totals are computed server-side on save.</p></div>'
        );
        updateManualTotals();
    }

    function updateManualTotals() {
        $('#pricing-manual-grid tbody tr').each(function () {
            var $row = $(this), totals = [];
            $row.find('.mp-price').each(function () {
                var size = $(this).data('size'), factor = parseInt(size, 10);
                totals.push(size.replace('p', ' clients: ') + money(parseFloat($(this).val() || 0) * factor));
            });
            $row.find('.mp-totals').html(totals.join(' &middot; '));
        });
    }

    // ── Calculator items ─────────────────────────────────────────────────────
    function basisFields(basis) {
        return {
            nights: basis === 'PER_PERSON_PER_NIGHT' || basis === 'PER_ROOM_PER_NIGHT',
            occupancy: basis === 'PER_ROOM_PER_NIGHT',
            capacity: basis === 'PER_VEHICLE_PER_DAY' || basis === 'PER_VEHICLE_PER_TRIP',
        };
    }

    function levelOptions(categoryItem, duration) {
        var levels = (duration === 'single_day')
            ? P.singleDayLevel
            : (P.levelCatalog[categoryItem] ? P.levelCatalog[categoryItem].levels : {});
        var opts = '<option value="">(shared)</option>';
        Object.keys(levels).forEach(function (l) { opts += '<option value="' + l + '">' + levels[l] + '</option>'; });
        return opts;
    }

    function populateLevelSelect($select, selected) {
        $select.html(levelOptions(P.category, P.duration));
        if (selected) $select.val(selected);
        if (!$select.val()) $select.val('');
    }

    function applyBasisVisibility($row) {
        var basis = $row.find('.ci-basis').val();
        var f = basisFields(basis);

        $row.find('.ci-cluster-night').toggle(f.nights);
        $row.find('.ci-cluster-occupancy').toggle(f.occupancy);
        $row.find('.ci-cluster-capacity').toggle(f.capacity);
        $row.find('.ci-basis-help').text(
            $row.find('.ci-basis option:selected').data('help') || ''
        );
    }

    function syncRowsFromDom() {
        P.items = [];
        $('#pricing-items-list').children('.pricing-item-row').each(function () {
            var $r = $(this);
            P.items.push({
                key: $r.data('row'),
                name: $r.find('.ci-name').val() || '',
                charging_basis: $r.find('.ci-basis').val() || '',
                quantity: $r.find('.ci-quantity').val() || '1',
                rates: { HIGH: $r.find('.ci-rate-high').val() || '', LOW_WET: $r.find('.ci-rate-low').val() || '' },
                taxable: $r.find('.ci-taxable').is(':checked') ? 1 : 0,
                included: $r.find('.ci-included').is(':checked') ? 1 : 0,
                shared_across_levels: $r.find('.ci-level').val() === '' ? 1 : 0,
                level_key: $r.find('.ci-level').val() || null,
                nights: $r.find('.ci-nights').val() || null,
                room_occupancy: $r.find('.ci-occupancy').val() || null,
                vehicle_capacity: $r.find('.ci-vehicle-capacity').val() || null,
                notes: $r.find('.ci-notes').text() || '',
            });
        });
    }

    function renderItems() {
        var $list = $('#pricing-items-list');
        var $tpl = $('#pricing-cost-item-row-template').html() || '';
        $list.empty();
        P.items.forEach(function (item) {
            var html = $tpl.replace(/__ROW__/g, item.key);
            var $row = $(html);

            $row.find('.ci-name').val(item.name || '');
            $row.find('.ci-basis').val(item.charging_basis || 'PER_PERSON_PER_DAY');
            $row.find('.ci-rate-high').val(item.rates.HIGH || '');
            $row.find('.ci-rate-low').val(item.rates.LOW_WET || '');
            $row.find('.ci-quantity').val(item.quantity || '1');
            populateLevelSelect($row.find('.ci-level'), item.level_key || '');
            $row.find('.ci-included').prop('checked', item.included !== 0 && item.included !== false);
            $row.find('.ci-taxable').prop('checked', !!(item.taxable));
            $row.find('.ci-nights').val(item.nights || '1');
            $row.find('.ci-occupancy').val(item.room_occupancy || '2');
            $row.find('.ci-vehicle-capacity').val(item.vehicle_capacity || '6');
            if (item.notes) $row.find('.ci-notes').text(item.notes);
            applyBasisVisibility($row);
            $list.append($row);
        });
    }

    function blankItem() {
        return {
            key: Date.now().toString(36) + Math.floor(Math.random() * 1e6).toString(36),
            name: '', charging_basis: 'PER_PERSON_PER_DAY', quantity: '1',
            rates: { HIGH: '', LOW_WET: '' }, taxable: 0, included: 1, shared_across_levels: 1,
            level_key: null, nights: '1', room_occupancy: '2', vehicle_capacity: '6',
            notes: '', predefined_key: null,
        };
    }

    // ── Payload ──────────────────────────────────────────────────────────────
    function buildCalculatorPayload() {
        syncRowsFromDom();
        var items = P.items.map(function (it, idx) {
            return {
                predefined_key: it.predefined_key || null,
                name: it.name,
                charging_basis: it.charging_basis,
                quantity: it.quantity,
                currency: 'USD',
                rates: { HIGH: it.rates.HIGH || '', LOW_WET: it.rates.LOW_WET || '' },
                taxable: !!it.taxable,
                included: !!it.included,
                shared_across_levels: !!it.shared_across_levels,
                level_key: it.level_key || null,
                nights: it.nights || null,
                room_occupancy: it.room_occupancy || null,
                vehicle_capacity: it.vehicle_capacity || null,
                notes: it.notes || '',
                position: idx,
            };
        });

        return {
            package_duration_type: P.duration,
            tour_type: P.tourType,
            package_category: P.duration === 'single_day' ? null : P.category,
            currency: ($('#pricing-manual-currency').val() || P.baseCurrency).toUpperCase(),
            tax_enabled: $('#pricing-tax-enabled').is(':checked') ? 1 : 0,
            tax_percentage: $('#pricing-tax-percent').val() || '18',
            markup_type: $('#pricing-markup-type').val() || 'percent',
            markup_value: $('#pricing-markup-value').val() || '0',
            rounding_rule: $('#pricing-rounding').val() || 'none',
            days: P.duration === 'single_day' ? 1 : parseInt($('#pricing-days').val() || '0', 10),
            nights: P.duration === 'single_day' ? 0 : parseInt($('#pricing-nights').val() || '0', 10),
            items: items,
        };
    }

    function setPreviewStatus(text, isError) {
        $('#pricing-preview-status')
            .text(text)
            .toggleClass('text-danger', !!isError)
            .toggleClass('text-muted', !isError);
    }

    function previewPrices(manual) {
        if (P.duration === 'single_day') {
            $('#pricing-days, #pricing-nights').attr('disabled', true);
        } else {
            $('#pricing-days, #pricing-nights').attr('disabled', false);
        }
        syncRowsFromDom();

        if (!P.tourType) {
            setPreviewStatus('Tour type is required.', true);
            return;
        }
        duringPreview(true);
        setPreviewStatus('Calculating &hellip;');

        var payload = buildCalculatorPayload();
        $.ajax({
            url: P.previewUrl,
            method: 'POST',
            data: { calculator_payload: payload, _token: $('input[name="_token"]').first().val() },
            success: function (res) {
                if (res.ok) {
                    $('#pricing-preview-target').html(res.html);
                    P.lastDigest = res.payload_digest;
                    $('#pricing-calculator-payload').val(JSON.stringify(payload));
                    setPreviewStatus('Preview ready — includes per-person and group totals.');
                }
            },
            error: function (xhr) {
                var msg = 'Preview failed. Please review the fields above.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.calculator_payload) {
                        msg = xhr.responseJSON.errors.calculator_payload[0];
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                setPreviewStatus(msg, true);
            },
            complete: function () { duringPreview(false); }
        });
    }

    function duringPreview(on) {
        $('#pricing-preview-btn').prop('disabled', on);
        $('button[type="submit"]').prop('disabled', on);
    }

    function schedulePreview() {
        clearTimeout(P.previewDebounce);
        P.previewDebounce = setTimeout(function () { previewPrices(); }, 750);
    }

    // ── Editing calculator state from an existing payload ───────────────────
    function restoreFromPayload(payload) {
        if (!payload || !payload.items) return;
        P.duration = payload.package_duration_type || 'multi_day';
        P.category = payload.package_category || (P.duration === 'single_day' ? '' : (Object.keys(P.levelCatalog)[0]));
        P.tourType = payload.tour_type || P.tourType;

        $('#pricing-duration').val(P.duration);
        refreshDurationUI();
        if (payload.package_category) $('#pricing-category').val(payload.package_category);
        if (payload.tour_type) $('#pricing-tour-type').val(payload.tour_type);

        $('#pricing-tax-enabled').prop('checked', payload.tax_enabled !== 0 && payload.tax_enabled !== false);
        $('#pricing-tax-percent').val(payload.tax_percentage || '18');
        $('#pricing-markup-type').val(payload.markup_type || 'percent');
        $('#pricing-markup-value').val(payload.markup_value || '0');
        $('#pricing-rounding').val(payload.rounding_rule || 'none');
        if (payload.currency) $('#pricing-manual-currency').val(payload.currency);
        if (payload.days) $('#pricing-days').val(payload.days);
        if (typeof payload.nights !== 'undefined') $('#pricing-nights').val(payload.nights);

        P.items = [];
        var lastKey = 0;
        payload.items.forEach(function (it, idx) {
            var item = blankItem();
            item.key = 'restore' + (++lastKey) + '_' + idx;
            item.predefined_key = it.predefined_key || null;
            item.name = it.name || '';
            item.charging_basis = it.charging_basis || 'PER_PERSON_PER_DAY';
            item.quantity = it.quantity != null ? it.quantity : '1';
            item.rates = { HIGH: it.rates ? (it.rates.HIGH || '') : '', LOW_WET: it.rates ? (it.rates.LOW_WET || '') : '' };
            item.taxable = it.taxable ? 1 : 0;
            item.included = it.included === false ? 0 : 1;
            item.shared_across_levels = (it.shared_across_levels === true || it.level_key == null) ? 1 : 0;
            item.level_key = it.level_key || null;
            item.nights = it.nights != null ? it.nights : '1';
            item.room_occupancy = it.room_occupancy != null ? it.room_occupancy : '2';
            item.vehicle_capacity = it.vehicle_capacity != null ? it.vehicle_capacity : '6';
            item.notes = it.notes || '';
            P.items.push(item);
        });
        renderItems();
    }

    // ── Duration / category / tour-type UI ───────────────────────────────────
    function refreshDurationUI() {
        var single = P.duration === 'single_day';
        $('.pricing-multi-day-only').toggle(!single);
        $('.pricing-calculator-multi').toggle(!single);
        $('.pricing-calculator-only').toggle(P.source === 'calculator');
    }

    // ── Source panes ─────────────────────────────────────────────────────────
    function setSource(source) {
        P.source = source;
        var none = source === 'none', manual = source === 'manual', calc = source === 'calculator';
        $('#pricing-config-row').toggle(!none);
        $('#pricing-manual-pane').toggle(manual);
        $('#pricing-calculator-pane').toggle(calc);
        $('.pricing-calculator-only').toggle(calc);
        refreshDurationUI();

        if (manual) {
            renderManualGrid();
        }
        if (calc) {
            if (!P.items.length) {
                if (P.existingPayload) {
                    restoreFromPayload(P.existingPayload);
                } else {
                    renderItems();
                }
            }
            schedulePreview();
        }
    }

    function loadPredefined() {
        var list = (P.predefinedCosts[P.tourType] || []);
        if (!list.length) {
            setPreviewStatus('No predefined template for this tour type (select tour type first).', true);
            return;
        }
        if (!confirm('Replace current cost items with the predefined ' + P.tourType + ' template? Rates are blank until you enter them.')) return;

        syncRowsFromDom();
        var cleared = P.items.filter(function (it) { return !!it.name; });
        P.items = (cleared.length ? cleared : []).concat(list.map(function (tpl, i) {
            var item = blankItem();
            item.key = 'pre' + i + '_' + Date.now().toString(36);
            item.predefined_key = tpl.key || null;
            item.name = tpl.name || '';
            item.charging_basis = tpl.charging_basis || 'PER_PERSON_PER_DAY';
            item.quantity = tpl.quantity != null ? tpl.quantity : '1';
            item.taxable = tpl.taxable ? 1 : 0;
            item.included = tpl.included === false ? 0 : 1;
            item.notes = tpl.notes || '';
            return item;
        }));
        renderItems();
        schedulePreview();
    }

    // ── Events ───────────────────────────────────────────────────────────────
    function bind() {
        $('input[name="pricing_source"]').on('change', function () { setSource($(this).val()); });

        $('#pricing-duration').on('change', function () {
            P.duration = $(this).val();
            var categoryEls = $('#pricing-category');
            if (P.duration === 'single_day' && !categoryEls.val()) categoryEls.val('');
            refreshDurationUI();
            if (P.source === 'manual') renderManualGrid();
            if (P.source === 'calculator') {
                syncRowsFromDom();
                renderItems();
                schedulePreview();
            }
        });

        $('#pricing-category').on('change', function () {
            P.category = $(this).val();
            if (P.source === 'manual') renderManualGrid();
            if (P.source === 'calculator') {
                syncRowsFromDom();
                renderItems();
                schedulePreview();
            }
        });

        $('#pricing-tour-type').on('change', function () {
            P.tourType = $(this).val();
            if (P.source === 'calculator') schedulePreview();
        });

        $('#pricing-manual-grid').on('input', '.mp-price', updateManualTotals);

        $('#pricing-items-list')
            .on('input', 'input, select', function () { schedulePreview(); })
            .on('change', 'select.ci-basis', function () {
                applyBasisVisibility($(this).closest('.pricing-item-row'));
                schedulePreview();
            })
            .on('click', '.ci-remove', function () {
                syncRowsFromDom();
                var $row = $(this).closest('.pricing-item-row');
                var key = $row.data('row');
                P.items = P.items.filter(function (it) { return it.key !== key; });
                renderItems();
            });

        $('#pricing-add-item').on('click', function () {
            syncRowsFromDom();
            P.items.push(blankItem());
            renderItems();
            schedulePreview();
        });

        $('#pricing-load-predefined').on('click', loadPredefined);
        $('#pricing-preview-btn').on('click', function () { previewPrices(true); });

        $('#pricing-tax-percent, #pricing-markup-value, #pricing-markup-type, #pricing-rounding, #pricing-days, #pricing-nights')
            .on('input change', function () {
                if (P.source === 'calculator') schedulePreview();
            });

        $('#pricing-tax-enabled').on('change', function () {
            if (P.source === 'calculator') schedulePreview();
        });
    }

    $(function () {
        bind();
        setSource(P.source);
    });
})();
</script><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\partials\pricing\scripts.blade.php ENDPATH**/ ?>