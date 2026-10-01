@php
    $mountains = \App\Models\Mountain::where('is_active', true)
        ->with('routes')
        ->orderBy('name')
        ->get();
    $currentMountain = old('mountain_id', $tourPackage->mountain_id ?? null);
    $selectedRoutes = array_map('intval', (array) old('mountain_route_ids', $tourPackage->mountain_route_ids ?? []));
@endphp

<div class="row mb-3" id="mountain-selection" style="display:none">
  <label class="col-sm-2 col-form-label">Mountain &amp; Routes</label>
  <div class="col-sm-10">
    @if($mountains->isEmpty())
      <div class="text-muted">Add mountains and routes from Tours &amp; Packages → Mountains.</div>
    @else
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="mountain-choice">Choose a mountain</label>
          <select class="form-select" id="mountain-choice" name="mountain_id">
            <option value="">Select a mountain</option>
            @foreach($mountains as $mountain)
              <option value="{{ $mountain->id }}" @selected((int)$currentMountain === $mountain->id)>{{ $mountain->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6" id="mountain-route-field" style="display:none">
          <label class="form-label" for="mountain-route-toggle">Choose route(s)</label>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="mountain-route-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" disabled>Select route(s)</button>
            <div class="dropdown-menu w-100 p-2" aria-labelledby="mountain-route-toggle">
              @foreach($mountains as $mountain)
                @foreach($mountain->routes as $route)
                  <label class="dropdown-item d-flex align-items-center gap-2" data-route-mountain="{{ $mountain->id }}" style="cursor:pointer">
                    <input class="form-check-input mt-0 mountain-route-choice" type="checkbox" name="mountain_route_ids[]" value="{{ $route->id }}" @checked((int)$currentMountain === $mountain->id && in_array($route->id, $selectedRoutes, true))>
                    <span>{{ $route->name }}</span>
                  </label>
                @endforeach
              @endforeach
            </div>
          </div>
          <small class="text-muted" id="mountain-route-empty" style="display:none">No routes have been added for this mountain yet.</small>
        </div>
      </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
(function () {
  var section = document.getElementById('mountain-selection');
  if (!section) return;

  var mountainSelect = section.querySelector('#mountain-choice');
  var routeField = section.querySelector('#mountain-route-field');
  var routeToggle = section.querySelector('#mountain-route-toggle');
  var routeChoices = Array.from(section.querySelectorAll('.mountain-route-choice'));
  var routeEmpty = section.querySelector('#mountain-route-empty');

  function activitySelected() {
    var field = document.querySelector('[data-picker-id$="-activities"]');
    if (!field) return false;

    return Array.from(field.querySelectorAll('[data-picker-checkbox]:checked')).some(function (box) {
      var label = box.closest('label');
      var name = (label ? label.textContent : '')
        .replace(/\s*\(inactive\)\s*$/i, '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
      return name === 'mountain climbing & trekking';
    });
  }

  function syncRoutes(reset) {
    if (!mountainSelect || !routeToggle || !routeField) return;

    var mountainId = mountainSelect.value;
    var available = routeChoices.filter(function (choice) {
      return choice.closest('[data-route-mountain]').dataset.routeMountain === mountainId;
    });

    routeField.style.display = mountainId ? '' : 'none';
    routeToggle.disabled = !mountainId || available.length === 0;
    routeChoices.forEach(function (choice) {
      var row = choice.closest('[data-route-mountain]');
      var belongsToMountain = row.dataset.routeMountain === mountainId;
      row.hidden = !belongsToMountain;
      choice.disabled = !belongsToMountain;
      if (reset || !belongsToMountain) choice.checked = false;
    });

    if (routeEmpty) routeEmpty.style.display = mountainId && available.length === 0 ? '' : 'none';
    var selected = available.filter(function (choice) { return choice.checked; })
      .map(function (choice) { return choice.nextElementSibling.textContent.trim(); });
    routeToggle.textContent = selected.length ? selected.join(', ') : 'Select route(s)';
  }

  function syncMountainVisibility() {
    var enabled = activitySelected();
    section.style.display = enabled ? '' : 'none';
    if (!enabled && mountainSelect) {
      mountainSelect.value = '';
      syncRoutes(true);
    }
  }

  document.addEventListener('change', function (event) {
    if (event.target.matches('[data-picker-id$="-activities"] [data-picker-checkbox]')) {
      syncMountainVisibility();
    }
  });
  if (mountainSelect) mountainSelect.addEventListener('change', function () { syncRoutes(true); });
  routeChoices.forEach(function (choice) { choice.addEventListener('change', function () { syncRoutes(false); }); });

  syncMountainVisibility();
  syncRoutes();
})();
</script>
@endpush
