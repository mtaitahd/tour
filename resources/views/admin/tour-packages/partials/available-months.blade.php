{{--
    Available months picker, shared by the create and edit tour package forms so the
    two can never drift apart.

    Requires:
        $tourPackage  TourPackage|null — used on edit to pre-tick existing months.
        $idPrefix     string|null      — id namespace, defaults to 'tour'. Only needed
                                         if the form ever renders this twice on one page.

    Stores month *numbers* (1-12); the month names come from TourPackage::MONTHS so
    the labels here and anywhere on the front end stay in sync.
--}}

@php
    $idPrefix = $idPrefix ?? 'tour';

    // old() wins so a failed validation round-trip keeps the admin's choices.
    $selected = array_map('intval', (array) old('available_months', $tourPackage->available_months ?? []));
    $selected = array_values(array_filter($selected, fn ($m) => $m >= 1 && $m <= 12));

    $selectAllId = $idPrefix . '-select-all-months';
@endphp

<div class="row mb-3 tour-months-field" data-months-field>
    <label class="col-sm-2 col-form-label">Available Months</label>
    <div class="col-sm-10">
        <div class="form-check form-check-inline mb-2">
            <input class="form-check-input" type="checkbox" id="{{ $selectAllId }}" data-select-all-months>
            <label class="form-check-label fw-semibold" for="{{ $selectAllId }}">
                Select all months
            </label>
        </div>

        <div class="row g-2 tour-months-grid" data-months-grid>
            @foreach (\App\Models\TourPackage::MONTHS as $number => $name)
                <div class="col-6 col-md-4 col-lg-3">
                    <label class="form-check tour-month-option">
                        <input class="form-check-input" type="checkbox"
                               name="available_months[]"
                               value="{{ $number }}"
                               data-month-checkbox
                               @checked(in_array($number, $selected, true))>
                        <span class="form-check-label">{{ $name }}</span>
                    </label>
                </div>
            @endforeach
        </div>

        <small class="form-text text-muted">
            Leave every month unticked to mean the tour runs all year round.
        </small>
        <div class="form-text" data-months-summary aria-live="polite"></div>

        @error('available_months')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
(function () {
  var field = document.querySelector('[data-months-field]');
  if (!field || field.dataset.monthsBound === '1') return;
  field.dataset.monthsBound = '1';

  var selectAll = field.querySelector('[data-select-all-months]');
  var boxes = Array.prototype.slice.call(field.querySelectorAll('[data-month-checkbox]'));
  var summary = field.querySelector('[data-months-summary]');

  function sync() {
    var checked = boxes.filter(function (b) { return b.checked; });

    // Reflect the individual boxes back onto "Select all": fully checked, fully
    // clear, or partially selected (shown via the indeterminate state).
    selectAll.checked = checked.length === boxes.length;
    selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;

    if (summary) {
      summary.textContent = checked.length === 0
        ? 'No months selected — the tour will be treated as running all year.'
        : checked.length + ' month' + (checked.length === 1 ? '' : 's') + ' selected.';
    }
  }

  selectAll.addEventListener('change', function () {
    boxes.forEach(function (b) { b.checked = selectAll.checked; });
    sync();
  });

  boxes.forEach(function (b) {
    b.addEventListener('change', sync);
  });

  sync();
})();
</script>
@endpush
