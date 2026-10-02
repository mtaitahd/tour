@php
    $defaultRules = ['tour_type', 'budget', 'categories'];
    $selectedRules = array_values((array) old('related_tour_rules', $tourPackage->related_tour_rules ?? $defaultRules));
@endphp

<div class="row mb-3">
  <label class="col-sm-2 col-form-label">Related tours</label>
  <div class="col-sm-10">
    <div class="border rounded p-3 bg-light">
      <div class="fw-semibold mb-1">Automatically group similar tours</div>
      <div class="text-muted small mb-3">Choose how the system finds related tours. Matching tours are shown automatically, so you do not need to select them one by one.</div>
      <input type="hidden" name="related_tour_rules_submitted" value="1">
      <div class="d-flex flex-wrap gap-3">
        <label class="form-check mb-0">
          <input class="form-check-input" type="checkbox" name="related_tour_rules[]" value="tour_type" @checked(in_array('tour_type', $selectedRules, true))>
          <span class="form-check-label">Same tour type</span>
        </label>
        <label class="form-check mb-0">
          <input class="form-check-input" type="checkbox" name="related_tour_rules[]" value="budget" @checked(in_array('budget', $selectedRules, true))>
          <span class="form-check-label">Same budget level</span>
        </label>
        <label class="form-check mb-0">
          <input class="form-check-input" type="checkbox" name="related_tour_rules[]" value="categories" @checked(in_array('categories', $selectedRules, true))>
          <span class="form-check-label">Share a category</span>
        </label>
      </div>
    </div>
  </div>
</div>
