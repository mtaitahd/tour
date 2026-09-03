@php $currency = $currency ?? 'USD'; @endphp
<div class="card mt-3">
    <div class="card-header bg-light">
        <strong>Price preview</strong>
        <span class="text-muted small float-end">
            {{ ($results['meta']['duration_type'] ?? '') === 'single_day' ? 'Single-day' : 'Multi-day' }}
            &middot; {{ strtoupper($currency) }}
        </span>
    </div>
    <div class="card-body">
        @php $meta = $results['meta'] ?? []; @endphp
        <div class="mb-2 small text-muted">
            @if (! empty($meta['tour_type'])) <span class="badge bg-secondary">{{ $meta['tour_type'] }}</span> @endif
            @if (! empty($meta['package_category'])) <span class="badge bg-secondary">{{ $meta['package_category'] }}</span> @endif
            @if (($meta['tax_enabled'] ?? false)) <span class="badge bg-light text-dark border">Tax {{ $meta['tax_percentage'] ?? '0' }}%</span> @endif
            @if (! empty($meta['markup_value']) && (float) $meta['markup_value'] > 0)
                <span class="badge bg-light text-dark border">Markup {{ $meta['markup_value'] }} {{ ($meta['markup_type'] ?? '') === 'percent' ? '%' : strtoupper($currency) }}</span>
            @endif
            @if (! empty($meta['rounding_rule']) && $meta['rounding_rule'] !== 'none')
                <span class="badge bg-light text-dark border">Rounded to {{ $meta['rounding_rule'] }}</span>
            @endif
        </div>

        @forelse (($results['groups'] ?? []) as $season => $levelGroups)
            <h6 class="mt-2 border-bottom pb-1">
                {{ $season === 'HIGH' ? 'High Season' : 'Low / Wet Season' }}
                <small class="text-muted">({{ $season }})</small>
            </h6>
            @foreach ($levelGroups as $levelKey => $sizes)
                <div class="mb-2">
                    <strong class="small">{{ $levelKey }}</strong>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-1">
                            <thead class="table-light">
                                <tr>
                                    <th>Group size</th>
                                    @foreach ($sizes as $clients => $group)
                                        <th class="text-center">{{ $clients }} clients</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Per person</td>
                                    @foreach ($sizes as $clients => $group)
                                        <td class="text-end">{{ strtoupper($currency) }} {{ $group['final_per_person'] ?? '0.00' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Group total</td>
                                    @foreach ($sizes as $clients => $group)
                                        <td class="text-end">{{ strtoupper($currency) }} {{ $group['final_group_total'] ?? '0.00' }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Cost / tax / markup</td>
                                    @foreach ($sizes as $clients => $group)
                                        <td class="text-end small text-muted">
                                            cost {{ $group['cost_total'] ?? '0.00' }}
                                            &middot; tax {{ $group['tax_amount'] ?? '0.00' }}
                                            &middot; markup {{ $group['markup_amount'] ?? '0.00' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="d-none pricing-details-row">
                                    <td colspan="{{ count($sizes) + 1 }}" class="p-0">
                                        <table class="table table-sm mb-0 small">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Basis</th>
                                                    <th>Rate</th>
                                                    <th>Qty</th>
                                                    @foreach ($sizes as $clients => $group)
                                                        <th class="text-end">{{ $clients }} clients</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            @php
                                                $firstLines = [];
                                                foreach ($sizes as $clients => $group) {
                                                    if (empty($firstLines) && ! empty($group['lines'])) {
                                                        $firstLines = $group['lines'];
                                                    }
                                                }
                                                $detailKeys = array_keys($firstLines);
                                            @endphp
                                            <tbody>
                                                @foreach ($firstLines as $li => $line)
                                                    <tr>
                                                        <td>{{ $line['name'] }}</td>
                                                        <td>{{ $line['basis'] }}</td>
                                                        <td>{{ $line['rate'] }}</td>
                                                        <td>{{ $line['quantity'] }}</td>
                                                        @foreach ($sizes as $clients => $group)
                                                            <td class="text-end">{{ $group['lines'][$li]['total'] ?? '—' }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                                <tr class="fw-bold">
                                                    <td colspan="4">Final per person</td>
                                                    @foreach ($sizes as $clients => $group)
                                                        <td class="text-end">{{ $group['final_per_person'] ?? '0.00' }}</td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="{{ count($sizes) + 1 }}" class="text-center p-0 bg-transparent">
                                        <a href="javascript:void(0);" class="small pricing-details-toggle">Show item breakdown</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @empty
            <p class="text-muted mb-0">No price groups were produced.</p>
        @endforelse
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
</script>