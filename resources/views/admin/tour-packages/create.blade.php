@extends('admin.layouts.app')
@section('title', 'Create Tour Package')
@push('styles')
<link rel="stylesheet" href="{{ asset('asset/vendor/leaflet/leaflet.css') }}" />
<style>
/* ── Scoped protections for Leaflet so global CSS (Bootstrap img margin,
      max-width / height:auto resets, etc.) cannot break tile layout ── */
.itinerary-location-map-wrapper {
    position: relative;
    width: 100%;
    margin-top: 12px;
    border: 1px solid #dce3ea;
    border-radius: 10px;
    overflow: hidden;
    background: #eef2f5;
}

.itinerary-location-map {
    position: relative;
    display: block;
    width: 100%;
    height: 320px;
    min-height: 320px;
    overflow: hidden;
    z-index: 1;
    background: #e8eef2;
}

.itinerary-location-map .leaflet-pane,
.itinerary-location-map .leaflet-tile,
.itinerary-location-map .leaflet-marker-icon,
.itinerary-location-map .leaflet-marker-shadow,
.itinerary-location-map .leaflet-tile-container,
.itinerary-location-map .leaflet-pane > svg,
.itinerary-location-map .leaflet-pane > canvas {
    position: absolute;
}

.itinerary-location-map img.leaflet-tile,
.itinerary-location-map .leaflet-marker-icon,
.itinerary-location-map .leaflet-marker-shadow {
    max-width: none !important;
    max-height: none !important;
    width: auto;
    height: auto;
    padding: 0;
    margin: 0;
    border: 0;
    box-shadow: none;
}

.itinerary-location-map .leaflet-control-zoom {
    z-index: 800;
}

@media (max-width: 767.98px) {
    .itinerary-location-map {
        height: 260px;
        min-height: 260px;
    }
}

/* Green numbered day pin (divIcon) for the location picker marker */
.itinerary-location-map .itinerary-map-marker-wrapper {
    background: transparent;
    border: none;
}
.itinerary-map-marker {
    position: relative;
    width: 36px;
    height: 46px;
    background: #1e7e34;
    border: 2px solid #fff;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}
.itinerary-map-marker span {
    transform: rotate(45deg);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    line-height: 1;
}
</style>
@endpush
@section('content')
<div class="pagetitle">
<h1>Create Tour Package</h1>
<nav>
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.tour-packages.index') }}">Tour Packages</a></li>
<li class="breadcrumb-item active">Create</li>
</ol>
</nav>
</div>
<section class="section">
<div class="row">
<div class="col-lg-12">
<div class="card">
<div class="card-body">
<h5 class="card-title">Package Information</h5>
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<form id="tour-create-form" method="POST" action="{{ isset($draft) && $draft ? route('admin.tour-packages.update', $draft->id) : route('admin.tour-packages.store') }}" enctype="multipart/form-data">
@csrf
@if(isset($draft) && $draft) @method('PUT') @endif
<input type="hidden" id="tour-draft-id" name="draft_id" value="{{ $draft->id ?? old('draft_id') }}">
<div class="tour-wizard-header mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div><div class="small text-uppercase text-muted fw-semibold">Tour builder</div><div class="fw-semibold" id="tour-wizard-step-title">Step 1 of 4 · Package Information</div></div>
    <div id="tour-draft-save-status" class="small text-muted" role="status" aria-live="polite">Draft saves automatically</div>
  </div>
  <div class="progress mb-3" style="height:6px" aria-label="Tour form progress"><div class="progress-bar bg-success" id="tour-wizard-progress" role="progressbar" style="width:25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div></div>
  <div class="row g-2" aria-label="Tour form steps">
    <div class="col-6 col-lg-3"><button type="button" class="btn btn-sm btn-success w-100 tour-wizard-step-indicator" data-step-indicator="1" aria-current="step">1 · Package Info</button></div>
    <div class="col-6 col-lg-3"><button type="button" class="btn btn-sm btn-outline-secondary w-100 tour-wizard-step-indicator" data-step-indicator="2">2 · Pricing &amp; Setup</button></div>
    <div class="col-6 col-lg-3"><button type="button" class="btn btn-sm btn-outline-secondary w-100 tour-wizard-step-indicator" data-step-indicator="3">3 · Itinerary</button></div>
    <div class="col-6 col-lg-3"><button type="button" class="btn btn-sm btn-outline-secondary w-100 tour-wizard-step-indicator" data-step-indicator="4">4 · SEO &amp; Publish</button></div>
  </div>
</div>
<div class="tour-wizard-panel" data-wizard-step="1">
<!-- Basic Info -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
<div class="col-sm-10">
<input type="text" id="tour-title" name="title" class="form-control" value="{{ old('title') }}" >
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Slug</label>
<div class="col-sm-10">
<input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
<small>Leave empty to auto-generate from title</small>
</div>
</div>
<!-- Destinations — multi-select checkbox dropdown -->
@include('admin.tour-packages.partials.destination-picker')
@include('admin.tour-packages.partials.mountain-and-related', ['tourPackage' => null])
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Duration (Days)</label>
<div class="col-sm-10">
<input type="number" name="duration_days" class="form-control" value="{{ old('duration_days') }}">
</div>
</div>

<!-- Tour Categories -->
@include('admin.tour-packages.partials.checkbox-picker', [
    'pickerLabel'   => 'Categories',
    'fieldName'     => 'categories',
    'pickerOptions' => \App\Models\TourCategory::orderBy('order')->orderBy('name')->get()
                          ->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])
                          ->all(),
    'pickerHelp'    => 'Select every category this tour should appear under. Manage them under Tours & Packages &rarr; Categories.',
    'idPrefix'      => 'tour-create',
])

<!-- Activities -->
@include('admin.tour-packages.partials.checkbox-picker', [
    'pickerLabel'   => 'Activities',
    'fieldName'     => 'activities',
    'pickerOptions' => \App\Models\Activity::active()->orderBy('order')->orderBy('name')->get()
                          ->map(fn ($activity) => ['value' => $activity->id, 'label' => $activity->name])
                          ->all(),
    'pickerHelp'    => 'Select the activities included in this tour.',
    'idPrefix'      => 'tour-create',
])

{{-- Video URL and Embed Map are not collected here. Both columns stay on
     tour_packages and the public tour page still reads them
     (TourController::buildEmbedUrl / tours.show), but they are no longer part of
     creating a tour — a new tour starts with neither, and they are filled in
     later on the edit form, which still offers Video URL and shows Embed Map for
     tours that already have one. --}}

<div class="row mb-3">
  <label class="col-sm-2 col-form-label">Transfer Cars Images</label>
  <div class="col-sm-10">
    <x-media-picker
        name="safari_car_image_ids"
        multiple
        :selected="old('safari_car_image_ids')"
        label="Select Transfer Cars Images"
    />
    <small class="text-muted d-block mt-1">Choose one or more images from the library. Drag to reorder.</small>
  </div>
</div>




<div class="d-flex justify-content-end mt-4">
  <button type="button" class="btn btn-primary tour-wizard-next" data-next-step="2">Next: Pricing &amp; Setup <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<div class="tour-wizard-panel d-none" data-wizard-step="2">
<h5 class="card-title">Pricing &amp; Trip Setup</h5>
@include('admin.tour-packages.partials.pricing.selector', ['tourPackage' => null])

<!-- Level & Rating -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Physical Rating</label>
<div class="col-sm-10">
<select name="physical_rating" class="form-select">
<option value="relaxing">Relaxing</option>
<option value="easy">Easy</option>
<option value="moderate" selected>Moderate</option>
<option value="complex">Complex</option>
<option value="super_complex">Super Complex</option>
</select>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Tour Level</label>
<div class="col-sm-10">
<select name="tour_level" class="form-select">
<option value="budget_camping">Camping</option>
<option value="budget_lodge">Budget</option>
<option value="mid_range" selected>Mid-Range</option>
<option value="luxury">Luxury</option>
</select>
</div>
</div>
<!-- Available Months — 12 checkboxes + a Select all toggle -->
@include('admin.tour-packages.partials.available-months', ['tourPackage' => null, 'idPrefix' => 'tour-create'])
<div class="row mb-3">
  <label class="col-sm-2 col-form-label" for="tour-starting-point">Starting Point</label>
  <div class="col-sm-10"><input type="text" id="tour-starting-point" name="starting_point" class="form-control" value="{{ old('starting_point') }}" placeholder="e.g. Arusha"></div>
</div>
<div class="row mb-3">
  <label class="col-sm-2 col-form-label" for="tour-ending-point">Ending Point</label>
  <div class="col-sm-10"><input type="text" id="tour-ending-point" name="ending_point" class="form-control" value="{{ old('ending_point') }}" placeholder="e.g. Arusha, Zanzibar Airport"></div>
</div>
<div class="row mb-3">
  <label class="col-sm-2 col-form-label fw-bold">Hero Image (main cover)</label>
  <div class="col-sm-10"><x-media-picker name="hero_image_id" :selected="old('hero_image_id')" label="Select from Media Library" /><small class="text-muted d-block mt-1">Choose an existing image from the library.</small></div>
</div>
<div class="d-flex justify-content-between mt-4">
  <button type="button" class="btn btn-outline-secondary tour-wizard-back" data-previous-step="1"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="button" class="btn btn-primary tour-wizard-next" data-next-step="3">Next: Itinerary <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<div class="tour-wizard-panel d-none" data-wizard-step="3">
<h5 class="card-title">Itinerary &amp; Experience</h5>
<!-- Overview — the full notepad, same editor as the destination form's Description.
     Not a .tinymce-editor on purpose: the Overview is initialised by this form
     instead (see the script at the foot of this file) so its toolbar can carry the
     Media Library button. The textarea is a real form field, so the Overview still
     saves even if the editor fails to start. -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Overview</label>
<div class="col-sm-10">
<textarea name="overview" id="tour-overview" class="form-control" rows="8">{{ old('overview') }}</textarea>
</div>
</div>
<!-- Itinerary Repeater -->
<div class="row mb-4 mt-5">
<label class="col-sm-2 col-form-label">Detailed Itinerary</label>
<div class="col-sm-10">
<div id="itinerary-repeater">
{{--
    Fixed pre-existing bug: this previously referenced $tourPackage->itinerary, but
    $tourPackage doesn't exist on this create form at all (TourPackageController::
    create() never passes one) — a silent undefined-variable warning on every page
    load. old('itinerary_days') alone is the correct fallback here, since a create
    form has no prior saved itinerary to fall back to in the first place.
--}}
@if(old('itinerary_days'))
@foreach(old('itinerary_days') as $index => $day)
<div class="itinerary-day card mb-3 shadow-sm" data-day-index="{{ $index }}">
<div class="card-header d-flex justify-content-between align-items-center bg-success">
<h6 class="mb-0">Day {{ $loop->iteration }}</h6>
<button type="button" class="btn btn-sm btn-danger remove-day">
<i class="bi bi-trash"></i> Remove
</button>
</div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-12">
<label class="form-label">Day Title</label>
<input type="text" name="itinerary_days[{{ $index }}][title]"
class="form-control"
value="{{ old("itinerary_days.$index.title") }}">
</div>
<div class="col-md-12">
<hr class="my-2">
<strong class="text-muted small">Day Location &amp; Route Point (Optional)</strong>
<div class="row g-2 mt-1 align-items-end">
<div class="col-md-7">
<label class="form-label small">Search location</label>
<div class="input-group input-group-sm">
<input type="text" class="form-control loc-search-input" placeholder="e.g. Machame Gate, Tanzania" data-day-idx="{{ $index }}">
<button type="button" class="btn btn-outline-primary loc-search-btn" data-day-idx="{{ $index }}">Search</button>
</div>
</div>
<div class="col-md-5">
<div class="loc-results small mt-1" data-day-idx="{{ $index }}" style="max-height:140px;overflow-y:auto;"></div>
</div>
</div>
<div class="itinerary-location-map-wrapper" data-day-idx="{{ $index }}" style="display:none;">
<div class="itinerary-location-map" data-location-map data-day-idx="{{ $index }}"></div>
</div>
<div class="loc-summary small text-success mt-1 d-none" data-day-idx="{{ $index }}">
<span class="loc-summary-text"></span>
<button type="button" class="btn btn-sm btn-outline-danger ms-2 loc-clear-btn" data-day-idx="{{ $index }}">Clear Location</button>
</div>
<input type="hidden" name="itinerary_days[{{ $index }}][location_name]" class="loc-field-location_name" value="{{ old("itinerary_days.$index.location_name") }}">
<input type="hidden" name="itinerary_days[{{ $index }}][lat]" class="loc-field-lat" value="{{ old("itinerary_days.$index.lat") }}">
<input type="hidden" name="itinerary_days[{{ $index }}][lng]" class="loc-field-lng" value="{{ old("itinerary_days.$index.lng") }}">
</div>
<div class="col-md-12">
<label class="form-label">Day Images</label>
<x-media-picker
    name="itinerary_days[{{ $index }}][existing_image_ids]"
    multiple
    :selected="$dayExistingImageIds"
    label="Select Day Images"
/>
<small class="text-muted d-block mt-1">Choose one or more images from the library. Drag to reorder.</small>
</div>
<div class="col-md-12">
<label class="form-label">Description Notepad</label>
<textarea id="itinerary-description-{{ $index }}" name="itinerary_days[{{ $index }}][description]" class="form-control tinymce-editor itinerary-notepad" rows="8">{{ old("itinerary_days.$index.description") }}</textarea>
</div>
<div class="col-md-12">
<label class="form-label">Accommodation Tiers</label>
@php $accommodationOptions = \App\Models\Accommodation::published()->orderBy('name')->get(); $level = old('tour_level', 'mid_range'); $group = $level === 'luxury' ? 'luxury' : (str_contains($level, 'budget') || $level === 'budget' ? 'budget' : 'mid_range'); $tierLabels = $group === 'luxury' ? ['silver' => 'High Luxury', 'gold' => 'High Exclusive', 'platinum' => 'High Elite'] : ($group === 'budget' ? ['silver' => 'Essential', 'gold' => 'Value', 'platinum' => 'Plus'] : ['silver' => 'High Classic', 'gold' => 'Comfort', 'platinum' => 'Premium']); @endphp
@foreach($tierLabels as $tierKey => $tierLabel)
<div class="row g-2 align-items-end mb-2 accommodation-tier-row" data-tier-key="{{ $tierKey }}">
<div class="col-md-3">
<label class="form-label small mb-1">{{ $tierLabel }}</label>
@php $selectedAccommodationName = old("itinerary_days.$index.accommodation_name_$tierKey", ''); @endphp
<select name="itinerary_days[{{ $index }}][accommodation_name_{{ $tierKey }}]" class="form-select accommodation-choice" data-accommodation-choice>
<option value="">Choose {{ $tierLabel }} accommodation</option>
@if($selectedAccommodationName && !$accommodationOptions->contains('name', $selectedAccommodationName))<option value="{{ $selectedAccommodationName }}" selected>{{ $selectedAccommodationName }} (saved entry)</option>@endif
@foreach($accommodationOptions as $accommodationOption)
@php $optionTier = strtolower((string) $accommodationOption->tier); $optionGroup = str_contains($optionTier, 'luxury') || str_contains($optionTier, 'exclusive') || str_contains($optionTier, 'elite') ? 'luxury' : (str_contains($optionTier, 'classic') || str_contains($optionTier, 'comfort') || str_contains($optionTier, 'premium') ? 'mid_range' : (str_contains($optionTier, 'essential') || str_contains($optionTier, 'value') || str_contains($optionTier, 'plus') ? 'budget' : 'all')); @endphp
<option value="{{ $accommodationOption->name }}" data-level-group="{{ $optionGroup }}" @selected($selectedAccommodationName === $accommodationOption->name)>{{ $accommodationOption->name }}{{ $accommodationOption->tier ? ' · ' . $accommodationOption->tier : '' }}</option>
@endforeach
</select>
</div>
<div class="col-md-9">
<label class="form-label small mb-1">{{ $tierLabel }} Image</label>
@php $accExistingImageId = old("itinerary_days.$index.existing_accommodation_image_$tierKey"); @endphp
<x-media-picker
    name="itinerary_days[{{ $index }}][existing_accommodation_image_{{ $tierKey }}]"
    :selected="$accExistingImageId"
    label="Select {{ $tierLabel }} Image"
/>
</div>
</div>
@endforeach
<small class="text-muted">Accommodation names and images can be left blank for the final day.</small>
</div>
<div class="col-md-6">
<label class="form-label">Meals</label>
<input type="text" name="itinerary_days[{{ $index }}][meals]"
class="form-control"
value="{{ old("itinerary_days.$index.meals") }}">
</div>
</div>
</div>
</div>
@endforeach
@endif
</div>
<button type="button" id="add-itinerary-day" class="btn btn-outline-primary mt-3">
<i class="bi bi-plus-circle"></i> Add New Day
</button>

{{-- Hidden template pickers for new days added client-side — see the matching
     block and rationale in tour-packages/edit.blade.php. --}}
<div id="itinerary-day-picker-template" class="d-none">
  <x-media-picker
      name="itinerary_days[__INDEX__][existing_image_ids]"
      multiple
      :selected="[]"
      label="Select Day Images"
  />
  @foreach(['silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum / Private'] as $tierKey => $tierLabel)
    <x-media-picker
        name="itinerary_days[__INDEX__][existing_accommodation_image_{{ $tierKey }}]"
        :selected="null"
        label="Select {{ $tierLabel }} Image"
    />
  @endforeach
</div>
</div>

</div>
<!-- Inclusions & Exclusions -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Price Inclusions & Exclusions</label>
<div class="col-sm-10">
<div class="row">
<div class="col-md-6">
<label class="form-label fw-bold">What's Included</label>
<div id="inclusions-repeater">
@if(old('inclusions_items', []))
@foreach(old('inclusions_items', []) as $index => $item)
<div class="input-group mb-2 inclusion-item">
<input type="text" name="inclusions_items[]" class="form-control"
value="{{ old("inclusions_items.$index", $item) }}">
<button type="button" class="btn btn-outline-danger remove-inclusion">
<i class="bi bi-trash"></i>
</button>
</div>
@endforeach
@endif
</div>
<button type="button" id="add-inclusion" class="btn btn-outline-success btn-sm mt-2">
<i class="bi bi-plus-circle"></i> Add Inclusion
</button>
</div>
<div class="col-md-6">
<label class="form-label fw-bold">What's Excluded</label>
<div id="exclusions-repeater">
@if(old('exclusions_items', []))
@foreach(old('exclusions_items', []) as $index => $item)
<div class="input-group mb-2 exclusion-item">
<input type="text" name="exclusions_items[]" class="form-control"
value="{{ old("exclusions_items.$index", $item) }}">
<button type="button" class="btn btn-outline-danger remove-exclusion">
<i class="bi bi-trash"></i>
</button>
</div>
@endforeach
@endif
</div>
<button type="button" id="add-exclusion" class="btn btn-outline-success btn-sm mt-2">
<i class="bi bi-plus-circle"></i> Add Exclusion
</button>
</div>
</div>
</div>
</div>
<!-- Highlights -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Highlights (one per line)</label>
<div class="col-sm-10">
<textarea name="highlights_text" class="form-control" rows="5" placeholder="See the Big Five&#10;Sunrise at Ngorongoro&#10;...">{{ old('highlights_text') }}</textarea>
<small>Will be saved as JSON array</small>
</div>
</div>
@include('admin.tour-packages.partials.faq-fields')
<div class="d-flex justify-content-between mt-4">
  <button type="button" class="btn btn-outline-secondary tour-wizard-back" data-previous-step="2"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="button" class="btn btn-primary tour-wizard-next" data-next-step="4">Next: SEO &amp; Publish <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<div class="tour-wizard-panel d-none" data-wizard-step="4">
<h5 class="card-title">SEO &amp; Publish</h5>
<h6 class="text-muted mt-3 mb-3">Search engine settings</h6>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Title</label>
<div class="col-sm-10">
<input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
<small>Recommended: 50–60 characters</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Description</label>
<div class="col-sm-10">
<textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') }}</textarea>
<small>Recommended: 150–160 characters</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Keywords</label>
<div class="col-sm-10">
<input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords') }}">
<small>comma separated, 5–10 keywords</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Indexing</label>
<div class="col-sm-10">
<div class="form-check form-switch mt-2">
<input type="checkbox" class="form-check-input" id="no_robots_create" name="no_robots" value="1" {{ old('no_robots') ? 'checked' : '' }}>
<label class="form-check-label" for="no_robots_create">Exclude from Google / sitemap (noindex)</label>
</div>
<small>When checked this tour is removed from sitemap.xml and hidden from search engines.</small>
</div>
</div>
<!-- Status -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Status</label>
<div class="col-sm-5">
<select name="status" class="form-select">
<option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
<option value="published" @selected(old('status', 'draft') === 'published')>Published</option>
<option value="archived" @selected(old('status', 'draft') === 'archived')>Archived</option>
</select>
</div>
<div class="col-sm-5">
<div class="form-check mt-2">
<input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
<label class="form-check-label">Featured on homepage</label>
</div>
</div>
</div>
<div class="d-flex justify-content-between align-items-center mt-4">
  <button type="button" class="btn btn-outline-secondary tour-wizard-back" data-previous-step="3"><i class="bi bi-arrow-left"></i> Back</button>
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-success">Finish &amp; Save Tour</button>
    <a href="{{ route('admin.tour-packages.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</section>

<!--
    Removed: two legacy Dropzone instances (#hero-dropzone, #gallery-dropzone) that
    targeted elements which no longer exist in this form and posted straight to the
    store route via AJAX, plus a .remove-media handler for .existing-media markup
    that no longer exists either. All tour images now come from the Media Library
    pickers, and uploads belong to the Media Library's own upload modal.
-->

<script>
$(document).ready(function () {
let sectionIndex = $('#extra-sections-repeater .section-item').length;
$('#add-extra-section').click(function () {
sectionIndex++;
let newSectionHtml = `
<div class="section-item card mb-4 shadow-sm">
<div class="card-header d-flex justify-content-between align-items-center bg-light">
<h6 class="mb-0">Section ${sectionIndex}</h6>
<button type="button" class="btn btn-sm btn-danger remove-section">
<i class="bi bi-trash"></i> Remove
</button>
</div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-6 section-picker-slot">
<label class="form-label">Section Image</label>
</div>
<div class="col-md-6">
<label class="form-label">Section Title</label>
<input type="text" name="extra_sections[${sectionIndex}][title]" class="form-control">
</div>
<div class="col-12">
<label class="form-label">Section Content</label>
<div class="quill-editor quill-mini border rounded" style="height: 180px;"></div>
<input type="hidden" name="extra_sections[${sectionIndex}][content]" class="quill-hidden-input">
</div>
<div class="col-md-6">
<label class="form-label">Image Position</label>
<select name="extra_sections[${sectionIndex}][image_side]" class="form-select">
<option value="left">Image on Left</option>
<option value="right">Image on Right</option>
</select>
</div>
</div>
</div>
</div>`;
const $newSection = $(newSectionHtml);
$('#extra-sections-repeater').append($newSection);

// Clone the hidden template picker for this new section — see the matching
// rationale in tour-packages/edit.blade.php's itinerary day picker template.
const templateHtml = document.getElementById('extra-section-picker-template').innerHTML;
const pickerNode = $('<div>' + templateHtml.replace(/__INDEX__/g, sectionIndex) + '</div>').children();
$newSection.find('.section-picker-slot').append(pickerNode);

// Re-init TinyMCE for new textarea (kept exactly as you had)
tinymce.init({
selector: '.tinymce-editor-mini',
height: 200,
menubar: false,
plugins: 'lists link code',
toolbar: 'undo redo | bold italic | bullist numlist | link | code'
});
});
$(document).on('click', '.remove-section', function () {
$(this).closest('.section-item').remove();
});
});
</script>

<!-- QUILL EDITOR (only added - with your requested changes: H1 to H6 + popular font families) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- Font families CSS (Times New Roman + other popular website fonts) -->
<style>
.ql-font-arial { font-family: Arial, sans-serif !important; }
.ql-font-helvetica { font-family: Helvetica, Arial, sans-serif !important; }
.ql-font-times-new-roman { font-family: "Times New Roman", serif !important; }
.ql-font-georgia { font-family: Georgia, serif !important; }
.ql-font-verdana { font-family: Verdana, sans-serif !important; }
.ql-font-courier-new { font-family: "Courier New", monospace !important; }
</style>

{{-- Media Library button for the Quill editors below (itinerary days, extra
     sections) — see the partial for why it exists. --}}
@include('admin.tour-packages.partials.quill-media')

{{-- The Overview is the full notepad: the same options the admin layout gives
     every .tinymce-editor (the destination form's Description, blog bodies …), with
     the Media Library button added to the toolbar. The notepad partial must be
     included first so 'mediagallery' is a known button. --}}
@include('admin.tour-packages.partials.tinymce-media')
<script>
$(document).ready(function () {
if (!document.getElementById('tour-overview') || tinymce.get('tour-overview')) return;

const overviewConfig = Object.assign({
selector: '#tour-overview',

setup: function (editor) {
editor.on('change', function () {
editor.save();
});
}
}, @include('admin.partials.tinymce-notepad'));

overviewConfig.toolbar = overviewConfig.toolbar + ' | mediagallery';
tinymce.init(overviewConfig);
});
</script>

<script>
$(document).ready(function () {
function initQuill(editorDiv) {
if (!editorDiv.length || editorDiv.hasClass('ql-container')) return;

const quill = new Quill(editorDiv[0], {
theme: 'snow',
modules: {
toolbar: {
container: [
[{ 'font': ['arial', 'helvetica', 'times-new-roman', 'georgia', 'verdana', 'courier-new'] }],
[{ 'header': [1, 2, 3, 4, 5, 6, false] }], // all 6 heading levels
['bold', 'italic', 'underline', 'strike'],
['blockquote', 'code-block'],
[{ 'list': 'ordered'}, { 'list': 'bullet' }],
['link'],
['clean'],
QuillMedia.galleryButton()
]
}
}
});

const hiddenInput = editorDiv.siblings('.quill-hidden-input').first();

QuillMedia.attach(quill, hiddenInput);

if (hiddenInput.val()) {
// dangerouslyPasteHTML (not quill.root.innerHTML = ...) so the HTML is parsed
// into Quill's document model. Assigning innerHTML leaves the model empty, which
// means any image already in the content is dropped the first time the editor is
// changed and re-saved.
const html = hiddenInput.val();
try {
quill.clipboard.dangerouslyPasteHTML(html, 'silent');
} catch (e) { /* handled by the fallback below */ }
if (quill.getLength() < 2) {
// Stored HTML Quill has no format for (e.g. hand-written markup) would parse to
// an empty model, so the first keystroke would wipe the field. Render it as-is
// instead — an imperfect editor is better than losing someone's content.
quill.root.innerHTML = html;
}
}
quill.on('text-change', function () {
hiddenInput.val(quill.root.innerHTML);
});
}

// Init all Quill editors on load
$('.quill-editor').each(function () {
initQuill($(this));
});

// Auto-init Quill when new extra section is added
$('#add-extra-section').on('click', function () {
setTimeout(() => {
initQuill($('#extra-sections-repeater .quill-editor').last());
}, 100);
});
});
</script>
@push('scripts')
<script>
(function(){var levelSelect=document.querySelector('[name="tour_level"]');if(!levelSelect)return;function group(){var v=levelSelect.value;return v==='luxury'?'luxury':(v.indexOf('budget')===0||v==='budget'?'budget':'mid_range');}function filter(){var g=group();document.querySelectorAll('[data-accommodation-choice] option[data-level-group]').forEach(function(o){o.hidden=o.dataset.levelGroup!=='all'&&o.dataset.levelGroup!==g;});var labels={luxury:{silver:'High Luxury',gold:'High Exclusive',platinum:'High Elite'},mid_range:{silver:'High Classic',gold:'Comfort',platinum:'Premium'},budget:{silver:'Essential',gold:'Value',platinum:'Plus'}};document.querySelectorAll('.accommodation-tier-row').forEach(function(row){var key=row.dataset.tierKey,label=row.querySelector('label.form-label.small.mb-1');if(label&&labels[g][key])label.textContent=labels[g][key];});}window.filterAccommodationOptions=filter;levelSelect.addEventListener('change',filter);filter();})();
</script>
@endpush
@endsection

@push('scripts')
<script src="{{ asset('asset/vendor/leaflet/leaflet.js') }}"></script>
<script>
$(document).ready(function () {
    /* ── Itinerary day add/remove ───────────────────────────────────── */
    var dayCounter = $('#itinerary-repeater .itinerary-day').length;

    $('#add-itinerary-day').on('click', function () {
        dayCounter++;
        var n = dayCounter;
        var html = '<div class="itinerary-day card mb-3 shadow-sm" data-day-index="' + (n - 1) + '">' +
            '<div class="card-header d-flex justify-content-between align-items-center bg-success">' +
            '<h6 class="mb-0 text-white">Day ' + n + '</h6>' +
            '<button type="button" class="btn btn-sm btn-danger remove-day"><i class="bi bi-trash"></i> Remove</button>' +
            '</div>' +
            '<div class="card-body"><div class="row g-3">' +
            '<div class="col-md-12"><label class="form-label">Day Title</label>' +
            '<input type="text" name="itinerary_days[' + n + '][title]" class="form-control"></div>' +
            '<div class="col-md-12"><hr class="my-2"><strong class="text-muted small">Day Location &amp; Route Point (Optional)</strong>' +
            '<div class="row g-2 mt-1 align-items-end">' +
            '<div class="col-md-7"><label class="form-label small">Search location</label>' +
            '<div class="input-group input-group-sm">' +
            '<input type="text" class="form-control loc-search-input" placeholder="e.g. Machame Gate, Tanzania" data-day-idx="' + n + '">' +
            '<button type="button" class="btn btn-outline-primary loc-search-btn" data-day-idx="' + n + '">Search</button></div></div>' +
            '<div class="col-md-5"><div class="loc-results small mt-1" data-day-idx="' + n + '" style="max-height:140px;overflow-y:auto;"></div></div></div>' +
            '<div class="itinerary-location-map-wrapper" data-day-idx="' + n + '" style="display:none;">' +
            '<div class="itinerary-location-map" data-location-map data-day-idx="' + n + '"></div></div>' +
            '<div class="loc-summary small text-success mt-1 d-none" data-day-idx="' + n + '">' +
            '<span class="loc-summary-text"></span>' +
            '<button type="button" class="btn btn-sm btn-outline-danger ms-2 loc-clear-btn" data-day-idx="' + n + '">Clear Location</button></div>' +
            '<input type="hidden" name="itinerary_days[' + n + '][location_name]" class="loc-field-location_name">' +
            '<input type="hidden" name="itinerary_days[' + n + '][lat]" class="loc-field-lat">' +
            '<input type="hidden" name="itinerary_days[' + n + '][lng]" class="loc-field-lng">' +
            '</div>' +
            '<div class="col-md-12 day-images-picker-slot"><label class="form-label">Day Images</label></div>' +
            '<div class="col-md-12"><label class="form-label">Description Notepad</label>' +
            '<textarea id="itinerary-description-' + n + '" name="itinerary_days[' + n + '][description]" class="form-control tinymce-editor itinerary-notepad" rows="8"></textarea></div>' +
            '<div class="col-md-12"><label class="form-label">Accommodation Tiers</label>' +
            ['silver', 'gold', 'platinum'].map(function (tier) {
                var label = tier === 'platinum' ? 'Platinum / Private' : tier.charAt(0).toUpperCase() + tier.slice(1);
                return '<div class="row g-2 align-items-end mb-2 accommodation-tier-row" data-tier-key="' + tier + '"><div class="col-md-3"><label class="form-label small mb-1">' + label + '</label>' +
                    '<input type="text" name="itinerary_days[' + n + '][accommodation_name_' + tier + ']" class="form-control" placeholder="' + label + ' accommodation"></div>' +
                    '<div class="col-md-9 acc-tier-picker-slot" data-tier="' + tier + '"><label class="form-label small mb-1">' + label + ' Image</label></div></div>';
            }).join('') +
            '<small class="text-muted">Accommodation names and images can be left blank for the final day.</small></div>' +
            '<div class="col-md-6"><label class="form-label">Meals</label>' +
            '<input type="text" name="itinerary_days[' + n + '][meals]" class="form-control"></div>' +
            '</div></div></div>';

        var $newDay = $(html);
        $('#itinerary-repeater').append($newDay);
        if (window.tinymce) tinymce.init({ selector: '#itinerary-description-' + n });
        $newDay.find('input[name*="accommodation_name_"]').each(function () {
          var input=this, select=document.createElement('select');
          select.name=input.name; select.className='form-select accommodation-choice'; select.setAttribute('data-accommodation-choice','');
          select.add(new Option('Choose accommodation',''));
          var catalog=@json(\App\Models\Accommodation::published()->orderBy('name')->get(['name','tier'])->values());
          catalog.forEach(function(item){
            var tier=String(item.tier||'').toLowerCase();
            var group=(tier.indexOf('luxury')>=0||tier.indexOf('exclusive')>=0||tier.indexOf('elite')>=0)?'luxury':((tier.indexOf('classic')>=0||tier.indexOf('comfort')>=0||tier.indexOf('premium')>=0)?'mid_range':((tier.indexOf('essential')>=0||tier.indexOf('value')>=0||tier.indexOf('plus')>=0)?'budget':'all'));
            var option=new Option(item.name+(item.tier?' · '+item.tier:''),item.name); option.dataset.levelGroup=group; select.add(option);
          });
          input.replaceWith(select);
        });
        if(window.filterAccommodationOptions)window.filterAccommodationOptions();

        var templateHtml = document.getElementById('itinerary-day-picker-template').innerHTML;
        var pickerNodes = $('<div>' + templateHtml.replace(/__INDEX__/g, n) + '</div>').children();
        $newDay.find('.day-images-picker-slot').append(pickerNodes.eq(0));
        ['silver', 'gold', 'platinum'].forEach(function (tier, i) {
            $newDay.find('.acc-tier-picker-slot[data-tier="' + tier + '"]').append(pickerNodes.eq(i + 1));
        });
        pickerNodes.find('.media-picker-sortable').each(function () {
            if (typeof Sortable !== 'undefined') new Sortable(this, { animation: 150, ghostClass: 'bg-light' });
        });
        setTimeout(function () { initQuill($('#itinerary-repeater .itinerary-day').last().find('.quill-editor')); }, 100);
    });

    $(document).on('click', '.remove-day', function () {
        var $day = $(this).closest('.itinerary-day');
        var idx = $day.find('.itinerary-location-map').attr('data-day-idx');
        if (idx !== undefined && window._locMaps && window._locMaps[idx]) {
            window._locMaps[idx].remove();
            delete window._locMaps[idx];
            delete window._locMarkers[idx];
        }
        $day.remove();
        reindexDays();
    });
});

/* ── Location picker logic ────────────────────────────────────────── */
window._locMaps = {};
window._locMarkers = {};

/* Marker pin is a CSS divIcon (no PNG dependency), labeled with the day
   number so fragile image asset paths can't break it under /tour base path. */
function createItineraryMarkerIcon(dayNumber) {
    return L.divIcon({
        className: 'itinerary-map-marker-wrapper',
        html: '<div class="itinerary-map-marker"><span>' + dayNumber + '</span></div>',
        iconSize: [36, 46],
        iconAnchor: [18, 46],
        popupAnchor: [0, -44]
    });
}

/* Visible day number rule: internal itinerary index is 0-based, the visible
   day number is index + 1. Read the stable 0-based data-day-index from the
   day card and convert ONCE (never from data-day-idx, which may be off). */
function getVisibleDayNumber(dayElement) {
    var index = parseInt(dayElement.getAttribute('data-day-index'), 10);
    if (!Number.isInteger(index) || index < 0) return 1;
    return index + 1;
}

/* Show + size a location-map wrapper (creates the rectangular box) and then
   invalidate every Leaflet instance inside it once it has a real width. */
function _showLocationMap(wrapper) {
    if (!wrapper) return;
    wrapper.style.display = 'block';
    var idx = wrapper.getAttribute('data-day-idx');
    var map = window._locMaps[idx];
    if (map) {
        requestAnimationFrame(function () {
            map.invalidateSize(true);
        });
    }
}

function _initLocMap(container) {
    var idx = container.getAttribute('data-day-idx');
    if (!idx || window._locMaps[idx]) return;
    var lat = -6.3690, lng = 34.8888;
    var map = L.map(container, { scrollWheelZoom: false, zoomControl: true }).setView([lat, lng], 6);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    window._locMaps[idx] = map;

    /* The container is now shown with a real size; recompute the layout. */
    requestAnimationFrame(function () { map.invalidateSize(true); });

    map.on('click', function (e) {
        _setMapPoint(idx, e.latlng.lat, e.latlng.lng, 'Selected map location');
    });
}

function _setMapPoint(idx, lat, lng, name) {
    var map = window._locMaps[idx];
    if (!map) return;

    if (window._locMarkers[idx]) {
        map.removeLayer(window._locMarkers[idx]);
    }
    var dayContainer = document.querySelector('.itinerary-location-map[data-day-idx="' + idx + '"]');
    var dayCard = dayContainer ? dayContainer.closest('.itinerary-day') : null;
    var dayNumber = dayCard ? getVisibleDayNumber(dayCard) : (parseInt(idx, 10) || 0) + 1;
    var marker = L.marker([lat, lng], { draggable: true, icon: createItineraryMarkerIcon(dayNumber) }).addTo(map);
    window._locMarkers[idx] = marker;

    marker.bindPopup(name || 'Selected map location', { className: 'loc-popup' }).openPopup();
    map.setView([lat, lng], Math.max(map.getZoom(), 11));
    requestAnimationFrame(function () { map.invalidateSize(true); });

    var $card = document.querySelector('.itinerary-location-map[data-day-idx="' + idx + '"]');
    if ($card) {
        var $cardRoot = $card.closest('.itinerary-day');
        $cardRoot.querySelector('.loc-field-lat').value = lat.toFixed(7);
        $cardRoot.querySelector('.loc-field-lng').value = lng.toFixed(7);
        $cardRoot.querySelector('.loc-field-location_name').value = name || 'Selected map location';
        var $summary = $cardRoot.querySelector('.loc-summary');
        $summary.classList.remove('d-none');
        $summary.querySelector('.loc-summary-text').textContent = 'Selected: ' + (name || 'Selected map location');
    }

    marker.on('dragend', function () {
        var pos = marker.getLatLng();
        _setMapPoint(idx, pos.lat, pos.lng, name || 'Selected map location');
    });
}

/* ── Search ────────────────────────────────────────────────────────── */
$(document).on('click', '.loc-search-btn', function () {
    var dayIdx = this.getAttribute('data-day-idx');
    var $input = document.querySelector('.loc-search-input[data-day-idx="' + dayIdx + '"]');
    var query = ($input ? $input.value : '').trim();
    if (query.length < 3) { return; }
    var $results = document.querySelector('.loc-results[data-day-idx="' + dayIdx + '"]');
    if ($results) $results.innerHTML = '<span class="text-muted">Searching…</span>';

    fetch("{{ route('admin.location-search') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ q: query })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!$results) return;
        if (!data.results || data.results.length === 0) {
            $results.innerHTML = '<span class="text-muted">' + (data.message || 'No matching locations found') + '</span>';
            return;
        }
        $results.innerHTML = data.results.map(function (r, i) {
            return '<div class="loc-result-item p-1 px-2 rounded mb-1 cursor-pointer" style="cursor:pointer;background:#f0f4f8;" ' +
                'data-day-idx="' + dayIdx + '" data-lat="' + r.lat + '" data-lng="' + r.lng + '" data-name="' + r.display_name.replace(/"/g, '&quot;') + '">' +
                r.display_name + '</div>';
        }).join('');
    })
    .catch(function () {
        if ($results) $results.innerHTML = '<span class="text-muted">Unable to search locations right now</span>';
    });
});

$(document).on('keypress', '.loc-search-input', function (e) {
    if (e.which === 13) {
        e.preventDefault();
        $(this).closest('.input-group').find('.loc-search-btn').trigger('click');
    }
});

$(document).on('click', '.loc-result-item', function () {
    var lat = parseFloat(this.getAttribute('data-lat'));
    var lng = parseFloat(this.getAttribute('data-lng'));
    var name = this.getAttribute('data-name');
    var dayIdx = this.getAttribute('data-day-idx');
    var wrapper = document.querySelector('.itinerary-location-map-wrapper[data-day-idx="' + dayIdx + '"]');
    var container = document.querySelector('.itinerary-location-map[data-day-idx="' + dayIdx + '"]');

    /* Reveal + lay out the container, then (re)create the map and place the
       marker. invalidateSize ensures tiles cover the full rectangle. */
    if (wrapper) _showLocationMap(wrapper);
    if (container) {
        if (!window._locMaps[dayIdx]) {
            _initLocMap(container);
        }
        setTimeout(function () {
            _setMapPoint(dayIdx, lat, lng, name);
        }, 60);
    }

    var $results = document.querySelector('.loc-results[data-day-idx="' + dayIdx + '"]');
    if ($results) $results.innerHTML = '';
});

/* ── Clear ─────────────────────────────────────────────────────────── */
$(document).on('click', '.loc-clear-btn', function () {
    var dayIdx = this.getAttribute('data-day-idx');
    var $card = this.closest('.itinerary-day');
    $card.querySelector('.loc-field-lat').value = '';
    $card.querySelector('.loc-field-lng').value = '';
    $card.querySelector('.loc-field-location_name').value = '';
    $card.querySelector('.loc-summary').classList.add('d-none');
    $card.querySelector('.loc-summary-text').textContent = '';
    if (window._locMarkers[dayIdx] && window._locMaps[dayIdx]) {
        window._locMaps[dayIdx].removeLayer(window._locMarkers[dayIdx]);
        delete window._locMarkers[dayIdx];
    }
    if (window._locMaps[dayIdx]) {
        window._locMaps[dayIdx].setView([-6.3690, 34.8888], 6);
        requestAnimationFrame(function () { window._locMaps[dayIdx].invalidateSize(true); });
    }
});

/* ── Reindex after add/remove ──────────────────────────────────────── */
function reindexDays() {
    var $days = $('#itinerary-repeater .itinerary-day');
    $days.each(function (idx) {
        var $day = $(this);
        $day.find('h6.mb-0, h6.mb-0.text-white').text('Day ' + (idx + 1));
        var newIdx = idx;
        $day.attr('data-day-index', newIdx);
        $day.find('[name]').each(function () {
            var name = this.getAttribute('name');
            if (!name) return;
            var newName = name.replace(/itinerary_days\[\d+\]/, 'itinerary_days[' + newIdx + ']');
            if (newName !== name) this.setAttribute('name', newName);
        });
        var $mapContainer = $day.find('.itinerary-location-map');
        if ($mapContainer.length) {
            var oldIdx = $mapContainer.attr('data-day-idx');
            if (String(oldIdx) !== String(newIdx)) {
                if (window._locMaps && window._locMaps[oldIdx]) {
                    window._locMaps[newIdx] = window._locMaps[oldIdx];
                    delete window._locMaps[oldIdx];
                    if (window._locMarkers && window._locMarkers[oldIdx]) {
                        window._locMarkers[newIdx] = window._locMarkers[oldIdx];
                        delete window._locMarkers[oldIdx];
                    }
                    var hasMarker = window._locMarkers[newIdx] != null;
                    var latVal = $day.find('.loc-field-lat').val();
                    var lngVal = $day.find('.loc-field-lng').val();
                    var nameVal = $day.find('.loc-field-location_name').val();
                    if (!hasMarker && latVal && lngVal) {
                        setTimeout(function () { _setMapPoint(newIdx, parseFloat(latVal), parseFloat(lngVal), nameVal); }, 100);
                    }
                }
                $mapContainer.attr('data-day-idx', newIdx);
                $day.find('.itinerary-location-map-wrapper').attr('data-day-idx', newIdx);
                $day.find('.loc-search-input').attr('data-day-idx', newIdx);
                $day.find('.loc-search-btn').attr('data-day-idx', newIdx);
                $day.find('.loc-results').attr('data-day-idx', newIdx);
                $day.find('.loc-summary').attr('data-day-idx', newIdx);
                $day.find('.loc-clear-btn').attr('data-day-idx', newIdx);
            }
        }
    });
}

/* ── Init existing maps on load ─────────────────────────────────── */
setTimeout(function () {
    document.querySelectorAll('.itinerary-location-map-wrapper').forEach(function (wrapper) {
        var idx = wrapper.getAttribute('data-day-idx');
        var container = wrapper.querySelector('.itinerary-location-map');
        var $card = $(wrapper).closest('.itinerary-day');
        var latVal = $card.find('.loc-field-lat').val();
        var lngVal = $card.find('.loc-field-lng').val();
        var nameVal = $card.find('.loc-field-location_name').val();
        if (latVal && lngVal && container) {
            _showLocationMap(wrapper);
            if (!window._locMaps[idx]) {
                _initLocMap(container);
            }
            _setMapPoint(idx, parseFloat(latVal), parseFloat(lngVal), nameVal);
        }
    });
}, 500);

/* Keep the maps correctly sized after the containing modal/iframe finishes
   laying out and whenever the viewport resizes. */
$(window).on('resize', function () {
    Object.keys(window._locMaps).forEach(function (idx) {
        var map = window._locMaps[idx];
        if (map) {
            requestAnimationFrame(function () { map.invalidateSize(true); });
        }
    });
});
</script>

{{-- Save button spinner --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('tour-create-form');
    if (!form) return;
    form.addEventListener('submit', function (event) {
      if (event.defaultPrevented) return;
      const btn = form.querySelector('button[type="submit"]');
      if (!btn || btn.dataset.saving === '1') return;
      btn.dataset.saving = '1';
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
    });
  });
</script>
<script>
(function(){
  var form=document.getElementById('tour-create-form');
  if(!form)return;
  var panels=Array.from(form.querySelectorAll('[data-wizard-step]'));
  var indicators=Array.from(form.querySelectorAll('[data-step-indicator]'));
  var stepTitle=document.getElementById('tour-wizard-step-title');
  var progress=document.getElementById('tour-wizard-progress');
  var saveStatus=document.getElementById('tour-draft-save-status');
  var draftInput=document.getElementById('tour-draft-id');
  var titleInput=document.getElementById('tour-title');
  var slugInput=form.querySelector('[name="slug"]');
  var autosaveUrl=@json(route('admin.tour-packages.autosave'));
  var updateUrlTemplate=@json(route('admin.tour-packages.update', ['tour_package' => '__DRAFT_ID__']));
  var stepNames={1:'Package Information',2:'Pricing & Setup',3:'Itinerary & Experience',4:'SEO & Publish'};
  var currentStep=1;
  var draftId=draftInput&&draftInput.value?String(draftInput.value):'';
  var slugManuallyEdited=!!(slugInput&&slugInput.value.trim());
  var dirty=false;
  var saving=false;
  var saveQueued=false;
  var activeSave=null;
  var revision=0;
  var timer=null;
  var finalizing=false;

  function updateSaveStatus(text,kind){
    if(!saveStatus)return;
    saveStatus.textContent=text;
    saveStatus.className='small '+(kind==='success'?'text-success':kind==='error'?'text-danger':'text-muted');
  }
  function setStep(step,scroll){
    step=Math.max(1,Math.min(4,Number(step)||1));
    currentStep=step;
    panels.forEach(function(panel){panel.classList.toggle('d-none',Number(panel.dataset.wizardStep)!==step);});
    indicators.forEach(function(button){
      var selected=Number(button.dataset.stepIndicator)===step;
      button.classList.toggle('btn-success',selected);
      button.classList.toggle('btn-outline-secondary',!selected);
      if(selected)button.setAttribute('aria-current','step');else button.removeAttribute('aria-current');
      button.disabled=Number(button.dataset.stepIndicator)>step;
    });
    if(stepTitle)stepTitle.textContent='Step '+step+' of 4 · '+stepNames[step];
    if(progress){var percent=step*25;progress.style.width=percent+'%';progress.setAttribute('aria-valuenow',String(percent));}
    if(scroll){var top=form.getBoundingClientRect().top+window.pageYOffset-90;window.scrollTo({top:Math.max(0,top),behavior:'smooth'});}
    setTimeout(function(){window.dispatchEvent(new Event('resize'));},150);
  }
  function slugify(value){return String(value||'').toLowerCase().trim().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');}
  function markChanged(){
    dirty=true;
    revision++;
    updateSaveStatus('Unsaved changes…');
    if(saving)saveQueued=true;
    clearTimeout(timer);
    timer=setTimeout(function(){saveDraft(false);},900);
  }
  function applyDraftResponse(data,revisionAtSave){
    if(!draftId){
      draftId=String(data.draft_id);
      draftInput.value=draftId;
      var method=form.querySelector('input[name="_method"]');
      if(!method){method=document.createElement('input');method.type='hidden';method.name='_method';method.value='PUT';form.appendChild(method);}
      form.action=updateUrlTemplate.replace('__DRAFT_ID__',encodeURIComponent(draftId));
    }
    if(slugInput&&revision===revisionAtSave&&data.slug){slugInput.value=data.slug;}
  }
  async function saveDraft(force){
    if(saving){saveQueued=true;return activeSave;}
    if(!dirty&&!force)return true;
    saving=true;
    activeSave=(async function(){
      var successful=true;
      do{
        saveQueued=false;
        if(!dirty&&!force&&draftId)break;
        if(window.tinymce&&typeof window.tinymce.triggerSave==='function')window.tinymce.triggerSave();
        var revisionAtSave=revision;
        var data=new FormData(form);
        data.set('draft_id',draftId||'');
        data.delete('_method');
        dirty=false;
        updateSaveStatus('Saving draft…');
        try{
          var response=await fetch(autosaveUrl,{method:'POST',body:data,credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
          if(!response.ok)throw new Error('Draft save returned '+response.status);
          var result=await response.json();
          applyDraftResponse(result,revisionAtSave);
          if(revision!==revisionAtSave){dirty=true;saveQueued=true;}
          else{
            var savedTime=result.saved_at?new Date(result.saved_at):new Date();
            updateSaveStatus('Draft saved at '+savedTime.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}),'success');
          }
        }catch(error){
          dirty=true;
          saveQueued=false;
          successful=false;
          updateSaveStatus('Could not save draft. Check your connection and try again.','error');
        }
        force=false;
      }while(saveQueued&&successful);
      saving=false;
      activeSave=null;
      return successful&&!dirty;
    })();
    return activeSave;
  }

  form.addEventListener('input',markChanged);
  form.addEventListener('change',markChanged);
  if(slugInput)slugInput.addEventListener('input',function(){slugManuallyEdited=true;});
  if(titleInput)titleInput.addEventListener('input',function(){if(!slugManuallyEdited&&slugInput)slugInput.value=slugify(titleInput.value);});
  if(window.tinymce){
    var bindEditor=function(editor){editor.on('input change undo redo',markChanged);};
    window.tinymce.on('AddEditor',function(event){bindEditor(event.editor);});
    window.tinymce.editors.forEach(bindEditor);
  }
  form.querySelectorAll('.tour-wizard-next').forEach(function(button){button.addEventListener('click',async function(){
    var next=Number(button.dataset.nextStep);
    if(currentStep===1&&(!titleInput||!titleInput.value.trim())){
      setStep(1,true);
      updateSaveStatus('Add a tour title before continuing.','error');
      if(titleInput)titleInput.focus();
      return;
    }
    button.disabled=true;
    var saved=await saveDraft(true);
    button.disabled=false;
    if(saved)setStep(next,true);
  });});
  form.querySelectorAll('.tour-wizard-back').forEach(function(button){button.addEventListener('click',function(){setStep(Number(button.dataset.previousStep),true);});});
  indicators.forEach(function(button){button.addEventListener('click',function(){var target=Number(button.dataset.stepIndicator);if(target<currentStep)setStep(target,true);});});
  form.addEventListener('submit',async function(event){
    if(finalizing)return;
    event.preventDefault();
    if(window.tinymce&&typeof window.tinymce.triggerSave==='function')window.tinymce.triggerSave();
    if(!titleInput||!titleInput.value.trim()){
      setStep(1,true);
      updateSaveStatus('Add a tour title before saving.','error');
      if(titleInput)titleInput.focus();
      return;
    }
    var submitButton=form.querySelector('button[type="submit"]');
    if(submitButton){submitButton.disabled=true;submitButton.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Saving…';}
    var saved=await saveDraft(true);
    if(!saved){if(submitButton){submitButton.disabled=false;submitButton.textContent='Finish & Save Tour';}return;}
    finalizing=true;
    form.submit();
  });
  window.addEventListener('beforeunload',function(event){if(dirty||saving){event.preventDefault();event.returnValue='';}});
  setStep(1,false);
})();
</script>
@endpush
