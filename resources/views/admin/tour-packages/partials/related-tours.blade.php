@php
    $relatedTours = \App\Models\TourPackage::where('status', 'published')
        ->when(isset($tourPackage) && $tourPackage, fn ($query) => $query->where('id', '!=', $tourPackage->id))
        ->orderBy('title')
        ->get();
    $selectedRelated = array_map('intval', (array) old('related_tour_ids', $tourPackage->related_tour_ids ?? []));
@endphp

@if($relatedTours->isNotEmpty())
  <div class="row mb-3">
    <label class="col-sm-2 col-form-label">Related Tours to Display</label>
    <div class="col-sm-10">
      <div class="row g-2">
        @foreach($relatedTours as $related)
          <div class="col-md-6">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" name="related_tour_ids[]" value="{{ $related->id }}" @checked(in_array($related->id, $selectedRelated, true))>
              <span class="form-check-label">{{ $related->cardTitle() }}</span>
            </label>
          </div>
        @endforeach
      </div>
      <small class="text-muted">Select the tours to show in the related tours section.</small>
    </div>
  </div>
@endif
