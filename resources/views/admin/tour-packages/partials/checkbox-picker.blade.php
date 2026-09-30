{{--
    Generic checkbox picker, shared by the "many of these" taxonomy fields on the
    tour package form (categories, activities). Built as a partial so a new
    taxonomy only needs a model + an @include line, never a new block of markup.

    Required params:
        $pickerLabel     string  field label.
        $fieldName       string  input name, submitted as $fieldName[].
        $pickerOptions   array   list of ['value' => mixed, 'label' => string].

    Optional params:
        $pickerHelp      string|null  helper text under the grid.
        $selected        array        pre-ticked values (old() wins when present).
        $idPrefix        string|null  id namespace, default 'tour'.
        $rowClass        string|null  spacing on the wrapping row, default 'mb-3'.
        $pickerAllLabel  string|null  label for the select-all toggle, default 'Select all'.
        $showSelectAll   bool         default true.
--}}

@php
    $idPrefix       = $idPrefix ?? 'tour';
    $showSelectAll  = $showSelectAll ?? true;
    $pickerAllLabel = $pickerAllLabel ?? 'Select all';

    $pickerId = $idPrefix . '-' . $fieldName;

    // old() wins so a failed validation round-trip keeps the admin's choices.
    $selectedValues = array_map('intval', (array) old($fieldName, $selected ?? []));
    $pickerOptions  = $pickerOptions ?? [];
@endphp

<div class="row {{ $rowClass ?? 'mb-3' }} tour-picker-field" data-picker-field data-picker-id="{{ $pickerId }}">
    <label class="col-sm-2 col-form-label">{{ $pickerLabel }}</label>
    <div class="col-sm-10">
        @if ($showSelectAll && count($pickerOptions) > 1)
            <div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" id="{{ $pickerId }}-all" data-picker-select-all>
                <label class="form-check-label fw-semibold" for="{{ $pickerId }}-all">
                    {{ $pickerAllLabel }}
                </label>
            </div>
        @endif

        @if (count($pickerOptions) > 0)
            <div class="row g-2" data-picker-grid>
                @foreach ($pickerOptions as $pickerOption)
                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-check tour-picker-option">
                            <input class="form-check-input" type="checkbox"
                                   name="{{ $fieldName }}[]"
                                   value="{{ $pickerOption['value'] }}"
                                   data-picker-checkbox
                                   @checked(in_array((int) $pickerOption['value'], $selectedValues, true))>
                            <span class="form-check-label">{{ $pickerOption['label'] }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        @else
            <div class="form-text text-muted">
                Nothing to choose yet &mdash; add some from the admin menu first.
            </div>
        @endif

        @if (! empty($pickerHelp))
            <small class="form-text text-muted">{{ $pickerHelp }}</small>
        @endif

        @error($fieldName)
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error($fieldName . '.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
(function () {
  // Every picker on the page binds independently, so this partial can be included
  // any number of times without the handlers colliding.
  Array.prototype.forEach.call(document.querySelectorAll('[data-picker-field]'), function (field) {
    if (field.dataset.pickerBound === '1') return;
    field.dataset.pickerBound = '1';

    var selectAll = field.querySelector('[data-picker-select-all]');
    var boxes = Array.prototype.slice.call(field.querySelectorAll('[data-picker-checkbox]'));

    if (!boxes.length) return;

    function sync() {
      if (!selectAll) return;
      var checked = boxes.filter(function (b) { return b.checked; });

      // Tri-state: all, none, or partially selected.
      selectAll.checked = checked.length === boxes.length;
      selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
    }

    if (selectAll) {
      selectAll.addEventListener('change', function () {
        boxes.forEach(function (b) { b.checked = selectAll.checked; });
        sync();
      });
    }

    boxes.forEach(function (b) { b.addEventListener('change', sync); });
    sync();
  });
})();
</script>
@endpush
