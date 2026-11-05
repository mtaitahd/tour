@php
    $savedTransport = $tourPackage->transport_types ?? [];
    $legacyTransport = old('transport_type', $tourPackage->transport_type ?? null);
    $selectedTransport = old('transport_types_present')
        ? old('transport_types', [])
        : array_keys($tourPackage->transport_types ?? ($legacyTransport ? [$legacyTransport => true] : []));
@endphp
<div class="row mb-3">
  <div class="col-sm-2 col-form-label" id="transport-types-label">Transport types</div>
  <div class="col-sm-10" role="group" aria-labelledby="transport-types-label">
    <input type="hidden" name="transport_types_present" value="1">
    @foreach (\App\Models\TourPackage::TRANSPORT_TYPES as $value => $label)
      <label class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="transport_types[]" value="{{ $value }}" @checked(in_array($value, (array) $selectedTransport, true))>
        {{ $label }}
      </label>
    @endforeach
    <div class="mt-2 transport-other">
      <label class="form-label">Other transport type
        <input type="text" name="transport_other" class="form-control" maxlength="80" value="{{ old('transport_other', $savedTransport['other'] ?? '') }}">
      </label>
    </div>
    <style>
      [aria-labelledby="transport-types-label"]:not(:has([value="other"]:checked)) .transport-other { display: none; }
    </style>
    <div><small class="text-muted">Choose up to 10 transport photos below, in display order.</small></div>
    @error('transport_types.*')<div class="text-danger">{{ $message }}</div>@enderror
    @error('transport_other')<div class="text-danger">{{ $message }}</div>@enderror
  </div>
</div>
