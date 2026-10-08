{{--
    Grouped classification picker for the Add/Edit Tour Package forms (Step 1).

    Renders one dropdown per tour_categories group (Country, Region / Continent,
    Tour Type, Duration) with an inline "+ Add …" button that opens a small modal
    and creates the missing option via AJAX — the new option is injected into the
    select and pre-selected without leaving the tour workflow.

    Physical Rating and Tour Level are rendered here too (same grid, per the
    design) but they are NOT categories: they stay hard columns on tour_packages
    driven by their existing enum option lists (Option A), so they get no
    "+ Add" button. Available Months and Activities stay separate includes.

    Optional params:
        $tourPackage     TourPackage|null  pre-selects existing group values on edit.
        $includeInactive bool              list deactivated options too (edit form),
                                           so an inactive-but-attached value still
                                           shows instead of being silently detached.
--}}

@php
    use App\Models\TourCategory;

    $tourPackage     = $tourPackage ?? null;
    $includeInactive = $includeInactive ?? ($tourPackage !== null);

    $groupTypes = TourCategory::FORM_GROUPS;

    // Existing selections: one row per group in the shared pivot. old() wins so a
    // failed validation round-trip keeps the admin's choices.
    $groupSelection = [];
    foreach ($groupTypes as $type => $label) {
        $attached = $tourPackage?->categories->firstWhere('type', $type);
        $groupSelection[$type] = (int) old($type . '_id', $attached?->id ?? 0);
    }

    // Option lists: active-only on create; on edit include inactive ones so the
    // current selection is always present in the list (same rule the Activities
    // picker follows).
    $groupOptions = [];
    foreach ($groupTypes as $type => $label) {
        $groupOptions[$type] = TourCategory::pickerOptions($type, $includeInactive);
    }

    // Existing hard-coded enum option lists (unchanged — source of truth stays
    // on tour_packages.physical_rating / tour_level).
    $ratingOptions = [
        'relaxing'     => 'Relaxing',
        'easy'         => 'Easy',
        'moderate'     => 'Moderate',
        'complex'      => 'Complex',
        'super_complex'=> 'Super Complex',
    ];
    $levelOptions = [
        'budget_camping' => 'Camping',
        'budget_lodge'   => 'Budget',
        'mid_range'      => 'Mid-Range',
        'luxury'         => 'Luxury',
    ];
    $selectedRating = old('physical_rating', $tourPackage?->physical_rating ?? 'moderate');
    $selectedLevel  = old('tour_level', $tourPackage?->tour_level ?? 'mid_range');

    // Visual order exactly as requested:
    // Country · Region · Tour Type · Duration · Physical Rating · Tour Level
    $gridOrder = [
        'country'   => ['label' => 'Country', 'options' => $groupOptions['country'], 'name' => 'country_id', 'addLabel' => 'Add Country'],
        'region'    => ['label' => 'Region / Continent', 'options' => $groupOptions['region'], 'name' => 'region_id', 'addLabel' => 'Add Region'],
        'tour_type' => ['label' => 'Tour Type', 'options' => $groupOptions['tour_type'], 'name' => 'tour_type_id', 'addLabel' => 'Add Tour Type'],
        'duration'  => ['label' => 'Duration', 'options' => $groupOptions['duration'], 'name' => 'duration_id', 'addLabel' => 'Add Duration'],
        'rating'    => ['label' => 'Physical Rating', 'enum' => $ratingOptions, 'name' => 'physical_rating', 'selected' => $selectedRating],
        'level'     => ['label' => 'Tour Level', 'enum' => $levelOptions, 'name' => 'tour_level', 'selected' => $selectedLevel],
    ];
@endphp

<div class="row mb-3 tour-classification-field" data-classification-field>
    <label class="col-sm-2 col-form-label">Classification</label>
    <div class="col-sm-10">
        <div class="row g-3">
            @foreach ($gridOrder as $key => $field)
                <div class="col-md-6 col-xl-4">
                    <label class="form-label fw-semibold">{{ $field['label'] }}</label>

                    @if (isset($field['enum']))
                        {{-- Hard column (Option A): fixed enum options, no inline add. --}}
                        <select name="{{ $field['name'] }}" class="form-select">
                            @foreach ($field['enum'] as $value => $enumLabel)
                                <option value="{{ $value }}" @selected($field['selected'] === $value)>{{ $enumLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="input-group">
                            <select name="{{ $field['name'] }}" class="form-select" data-group-select data-type="{{ $key }}">
                                <option value="">{{ $field['label'] === 'Region / Continent' ? 'Select Region' : 'Select ' . $field['label'] }}</option>
                                @foreach ($field['options'] as $option)
                                    <option value="{{ $option['value'] }}" @selected($groupSelection[$key] === (int) $option['value'])>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary"
                                    data-add-group="{{ $key }}"
                                    data-add-label="{{ $field['addLabel'] }}"
                                    title="{{ $field['addLabel'] }}">
                                <i class="bi bi-plus-circle"></i> <span class="d-none d-xl-inline">Add</span>
                            </button>
                        </div>
                    @endif

                    @error($field['name'])
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>

        <small class="form-text text-muted mt-2 d-block">
            Options come from Tours &amp; Packages &rarr; Categories. Use <strong>+ Add</strong> to create a missing
            option without leaving this form. Physical Rating and Tour Level are fixed lists.
        </small>
    </div>
</div>

{{-- The quick-add modal is pushed onto the 'scripts' stack (rendered after the
     main form closes) — a <form> nested inside the tour form would be invalid
     HTML and browsers would drop it. --}}
@push('scripts')
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="quickAddCategoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickAddCategoryTitle">Add Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" id="quickAddCategoryType" value="">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="quickAddCategoryName" class="form-control" required
                           placeholder="e.g. Tanzania" autocomplete="off">
                    <div class="text-danger small mt-1" id="quickAddCategoryError" hidden></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="quickAddCategorySubmit">
                        <span class="spinner-border spinner-border-sm me-1" hidden></span> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
  var field = document.querySelector('[data-classification-field]');
  if (!field || field.dataset.classificationBound === '1') return;
  field.dataset.classificationBound = '1';

  var modalEl = document.getElementById('quickAddCategoryModal');
  if (!modalEl) return;

  var form = document.getElementById('quickAddCategoryForm');
  var typeInput = document.getElementById('quickAddCategoryType');
  var nameInput = document.getElementById('quickAddCategoryName');
  var titleEl = document.getElementById('quickAddCategoryTitle');
  var errorEl = document.getElementById('quickAddCategoryError');
  var submitBtn = document.getElementById('quickAddCategorySubmit');
  var spinner = submitBtn.querySelector('.spinner-border');
  var currentType = null;

  // "+ Add …" buttons inside the grid.
  Array.prototype.forEach.call(field.querySelectorAll('[data-add-group]'), function (btn) {
    btn.addEventListener('click', function () {
      currentType = btn.dataset.addGroup;
      typeInput.value = currentType;
      titleEl.textContent = btn.dataset.addLabel;
      nameInput.value = '';
      errorEl.hidden = true;
      errorEl.textContent = '';
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      setTimeout(function () { nameInput.focus(); }, 200);
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!currentType) return;

    var tokenInput = document.querySelector('form input[name="_token"]');
    errorEl.hidden = true;
    spinner.hidden = false;
    submitBtn.disabled = true;

    fetch('{{ route('admin.tour-categories.quick-add') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': tokenInput ? tokenInput.value : '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ type: currentType, name: nameInput.value })
    })
      .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
      .then(function (result) {
        if (!result.ok) {
          var messages = result.data && result.data.message
            ? result.data.message
            : 'Could not add this option.';
          if (result.data && result.data.errors) {
            messages = Object.keys(result.data.errors).map(function (k) {
              return result.data.errors[k].join(' ');
            }).join(' ');
          }
          errorEl.textContent = messages;
          errorEl.hidden = false;
          return;
        }

        // Inject the new option into this group's select and pre-select it —
        // the surrounding form is untouched, so nothing else is lost.
        var select = field.querySelector('[data-group-select][data-type="' + currentType + '"]');
        if (select) {
          var option = document.createElement('option');
          option.value = result.data.id;
          option.textContent = result.data.name;
          option.selected = true;
          select.appendChild(option);
        }

        bootstrap.Modal.getInstance(modalEl).hide();
      })
      .catch(function () {
        errorEl.textContent = 'Network error — please try again.';
        errorEl.hidden = false;
      })
      .finally(function () {
        spinner.hidden = true;
        submitBtn.disabled = false;
      });
  });
})();
</script>
@endpush
