@php
    $mountains = \App\Models\Mountain::where('is_active', true)->with('routes')->orderBy('name')->get();
    $relatedTours = \App\Models\TourPackage::where('status', 'published')->when(isset($tourPackage) && $tourPackage, fn ($q) => $q->where('id', '!=', $tourPackage->id))->orderBy('title')->get();
    $currentMountain = old('mountain_id', $tourPackage->mountain_id ?? null);
    $selectedRoutes = array_map('intval', (array) old('mountain_route_ids', $tourPackage->mountain_route_ids ?? []));
    $selectedRelated = array_map('intval', (array) old('related_tour_ids', $tourPackage->related_tour_ids ?? []));
@endphp
<div class="row mb-3"><label class="col-sm-2 col-form-label">Tour Format</label><div class="col-sm-10"><div class="d-flex gap-4"><label class="form-check"><input class="form-check-input" type="radio" name="tour_format" value="private" @checked(old('tour_format', $tourPackage->tour_format ?? 'private') === 'private')><span class="form-check-label">Private</span></label><label class="form-check"><input class="form-check-input" type="radio" name="tour_format" value="group" @checked(old('tour_format', $tourPackage->tour_format ?? '') === 'group')><span class="form-check-label">Group</span></label></div><small class="text-muted">This format is shown with the tour details.</small></div></div>
<div class="row mb-3" id="mountain-selection" style="display:none"><label class="col-sm-2 col-form-label">Mountain &amp; Routes</label><div class="col-sm-10">
@if($mountains->isEmpty())<div class="text-muted">Add mountains and routes from Tours &amp; Packages → Mountains.</div>@else
<div class="mb-2 fw-semibold">Choose a mountain</div><div class="d-flex flex-wrap gap-3 mb-3">@foreach($mountains as $mountain)<label class="form-check"><input class="form-check-input mountain-choice" type="radio" name="mountain_id" value="{{ $mountain->id }}" data-mountain-id="{{ $mountain->id }}" @checked((int)$currentMountain === $mountain->id)><span class="form-check-label">{{ $mountain->name }}</span></label>@endforeach</div>
<div class="mountain-route-options">@foreach($mountains as $mountain)<div class="mountain-route-group border rounded p-3 mb-2" data-route-mountain="{{ $mountain->id }}" style="display:none"><div class="fw-semibold mb-2">Routes for {{ $mountain->name }}</div>@forelse($mountain->routes as $route)<label class="form-check mb-1"><input class="form-check-input" type="checkbox" name="mountain_route_ids[]" value="{{ $route->id }}" @checked(in_array($route->id,$selectedRoutes,true))><span class="form-check-label">{{ $route->name }}</span></label>@empty<div class="small text-muted">No routes added for this mountain yet.</div>@endforelse</div>@endforeach</div>
@endif
</div></div>
@if($relatedTours->isNotEmpty())<div class="row mb-3"><label class="col-sm-2 col-form-label">Related Tours to Display</label><div class="col-sm-10"><div class="row g-2">@foreach($relatedTours as $related)<div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="related_tour_ids[]" value="{{ $related->id }}" @checked(in_array($related->id,$selectedRelated,true))><span class="form-check-label">{{ $related->cardTitle() }}</span></label></div>@endforeach</div><small class="text-muted">Select the tours to show in the related tours section.</small></div></div>@endif
@push('scripts')
<script>
(function(){
  var section=document.getElementById('mountain-selection'); if(!section)return;
  var activityField=document.querySelector('[data-picker-id$="-activities"]');
  function syncMountainVisibility(){
    if(!activityField){section.style.display='';return;}
    var enabled=Array.from(activityField.querySelectorAll('[data-picker-checkbox]:checked')).some(function(box){var label=box.closest('label');return /mountain|trekking|climbing/i.test(label?label.textContent:'');});
    section.style.display=enabled?'':'none';
    if(!enabled){section.querySelectorAll('input').forEach(function(input){input.checked=false;});}
  }
  function syncRoutes(){var choice=section.querySelector('.mountain-choice:checked');var id=choice?choice.value:'';section.querySelectorAll('[data-route-mountain]').forEach(function(group){group.style.display=group.dataset.routeMountain===id?'':'none';if(group.dataset.routeMountain!==id)group.querySelectorAll('input[type=checkbox]').forEach(function(box){box.checked=false;});});}
  document.querySelectorAll('[data-picker-id$="-activities"] [data-picker-checkbox]').forEach(function(box){box.addEventListener('change',syncMountainVisibility);});
  section.querySelectorAll('.mountain-choice').forEach(function(radio){radio.addEventListener('change',syncRoutes);});
  syncMountainVisibility();syncRoutes();
})();
</script>
@endpush
