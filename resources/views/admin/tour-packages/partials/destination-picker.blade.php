{{--
    Destinations picker: a collapsed checkbox panel, shared by the create and edit
    tour package forms so the two cannot drift apart. Destinations are the one
    taxonomy that grows without bound, so they stay behind a toggle instead of
    expanding into the checkbox grid the other pickers use.

    Optional params:
        $selected   array   pre-ticked destination ids (old() wins when present).
        $rowClass   string  spacing on the wrapping row, default 'mb-3'. The edit form
                            passes 'mb-4 mt-5' to keep its section break.
--}}

@php
    // old() wins so a failed validation round-trip keeps the admin's choices.
    $selected     = array_map('intval', (array) old('destinations', $selected ?? []));
    $destinations = \App\Models\Destination::orderBy('name')->get();
@endphp

<div class="row {{ $rowClass ?? 'mb-3' }} tour-destinations-field">
    <label class="col-sm-2 col-form-label">Destinations</label>
    <div class="col-sm-10">
        @if ($destinations->isNotEmpty())
            <div class="destination-checkbox-dropdown">
                <button type="button" class="form-select text-start destinations-dropdown-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span class="destinations-selection-label">Select destinations this tour visits…</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="destination-checkbox-panel">
                    @foreach ($destinations as $destination)
                        <label class="form-check destination-checkbox-item">
                            <input type="checkbox" class="form-check-input destination-checkbox"
                                   name="destinations[]" value="{{ $destination->id }}"
                                   @checked(in_array($destination->id, $selected, true))>
                            <span class="form-check-label">
                                {{ $destination->name }} <small class="text-muted">({{ $destination->country_code }})</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            <div class="form-text text-muted">
                Nothing to choose yet &mdash; add some from the admin menu first.
            </div>
        @endif

        <small class="form-text text-muted">Select multiple destinations this tour visits.</small>

        @error('destinations')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('destinations.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<style>
.destination-checkbox-dropdown { position: relative; }
.destination-checkbox-panel {
    display: none;
    position: absolute;
    z-index: 30;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #dce3ea;
    border-radius: 8px;
    background: #fff;
    padding: 10px 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
}
.destination-checkbox-dropdown.open .destination-checkbox-panel { display: block; }
.destination-checkbox-dropdown.open .destinations-dropdown-toggle { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
.destination-checkbox-item { margin-bottom: 4px; }
</style>
