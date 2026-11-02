<div class="row mb-3">
  <label for="transport-type" class="col-sm-2 col-form-label">Transport type</label>
  <div class="col-sm-10">
    <select id="transport-type" name="transport_type" class="form-select">
      <option value="">Select transport type</option>
      @foreach (\App\Models\TourPackage::TRANSPORT_TYPES as $value => $label)
        <option value="{{ $value }}" @selected(old('transport_type', $tourPackage->transport_type ?? '') === $value)>{{ $label }}</option>
      @endforeach
    </select>
    <small class="text-muted">Choose up to 10 transport photos below, in display order.</small>
    @error('transport_type')<div class="text-danger">{{ $message }}</div>@enderror
    @error('safari_car_image_ids')<div class="text-danger">{{ $message }}</div>@enderror
  </div>
</div>
