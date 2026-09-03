
<?php $__env->startSection('title', 'Create Tour Package'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/vendor/leaflet/leaflet.css')); ?>" />
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
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="pagetitle">
<h1>Create Tour Package</h1>
<nav>
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
<li class="breadcrumb-item"><a href="<?php echo e(route('admin.tour-packages.index')); ?>">Tour Packages</a></li>
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
<?php if($errors->any()): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<ul class="mb-0">
<?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><?php echo e($error); ?></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<form id="tour-create-form" method="POST" action="<?php echo e(route('admin.tour-packages.store')); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<!-- Basic Info -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
<div class="col-sm-10">
<input type="text" name="title" class="form-control" value="<?php echo e(old('title')); ?>" >
</div>
</div>

<div class="row mb-3">
<label class="col-sm-2 col-form-label">Slug</label>
<div class="col-sm-10">
<input type="text" name="slug" class="form-control" value="<?php echo e(old('slug')); ?>">
<small>Leave empty to auto-generate from title</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Duration (Days)</label>
<div class="col-sm-10">
<input type="number" name="duration_days" class="form-control" value="<?php echo e(old('duration_days')); ?>">
</div>
</div>

<!-- Video URL -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Video URL <span class="text-danger">*</span></label>
    <div class="col-sm-10">
        <input type="text" name="video_url" class="form-control" value="<?php echo e(old('video_url')); ?>" >
    </div>
</div>

<!-- Embed Map -->
<div class="row mb-3">
    <label class="col-sm-2 col-form-label">Embed Map <span class="text-danger">*</span></label>
    <div class="col-sm-10">
        <textarea name="embed_map" class="form-control" rows="3" ><?php echo e(old('embed_map')); ?></textarea>
        <small class="form-text text-muted">
            Paste your embed code (e.g., Google Maps iframe).
        </small>
    </div>
</div>

<div class="row mb-3">
  <label class="col-sm-2 col-form-label">Safari Car Images</label>
  <div class="col-sm-10">
    <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'safari_car_image_ids','multiple' => true,'selected' => old('safari_car_image_ids'),'label' => 'Select Safari Car Images'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
    <small class="text-muted d-block mt-1">Choose one or more images from the library. Drag to reorder.</small>
  </div>
</div>




<?php echo $__env->make('admin.tour-packages.partials.pricing.selector', ['tourPackage' => null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
<option value="budget_camping">Budget Camping</option>
<option value="budget_lodge">Budget Lodge</option>
<option value="mid_range" selected>Mid-Range</option>
<option value="luxury">Luxury</option>
</select>
</div>
</div>
<!-- Overview - Quill -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Overview</label>
<div class="col-sm-10">
<div class="quill-editor border rounded" style="height: 220px;"></div>
<input type="hidden" name="overview" class="quill-hidden-input" value="<?php echo e(old('overview')); ?>">
</div>
</div>
<!-- Itinerary Repeater -->
<div class="row mb-4 mt-5">
<label class="col-sm-2 col-form-label">Detailed Itinerary</label>
<div class="col-sm-10">
<div id="itinerary-repeater">

<?php if(old('itinerary_days')): ?>
<?php $__currentLoopData = old('itinerary_days'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="itinerary-day card mb-3 shadow-sm" data-day-index="<?php echo e($index); ?>">
<div class="card-header d-flex justify-content-between align-items-center bg-success">
<h6 class="mb-0">Day <?php echo e($loop->iteration); ?></h6>
<button type="button" class="btn btn-sm btn-danger remove-day">
<i class="bi bi-trash"></i> Remove
</button>
</div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-12">
<label class="form-label">Day Title</label>
<input type="text" name="itinerary_days[<?php echo e($index); ?>][title]"
class="form-control"
value="<?php echo e(old("itinerary_days.$index.title")); ?>">
</div>
<div class="col-md-12">
<hr class="my-2">
<strong class="text-muted small">Day Location &amp; Route Point (Optional)</strong>
<div class="row g-2 mt-1 align-items-end">
<div class="col-md-7">
<label class="form-label small">Search location</label>
<div class="input-group input-group-sm">
<input type="text" class="form-control loc-search-input" placeholder="e.g. Machame Gate, Tanzania" data-day-idx="<?php echo e($index); ?>">
<button type="button" class="btn btn-outline-primary loc-search-btn" data-day-idx="<?php echo e($index); ?>">Search</button>
</div>
</div>
<div class="col-md-5">
<div class="loc-results small mt-1" data-day-idx="<?php echo e($index); ?>" style="max-height:140px;overflow-y:auto;"></div>
</div>
</div>
<div class="itinerary-location-map-wrapper" data-day-idx="<?php echo e($index); ?>" style="display:none;">
<div class="itinerary-location-map" data-location-map data-day-idx="<?php echo e($index); ?>"></div>
</div>
<div class="loc-summary small text-success mt-1 d-none" data-day-idx="<?php echo e($index); ?>">
<span class="loc-summary-text"></span>
<button type="button" class="btn btn-sm btn-outline-danger ms-2 loc-clear-btn" data-day-idx="<?php echo e($index); ?>">Clear Location</button>
</div>
<input type="hidden" name="itinerary_days[<?php echo e($index); ?>][location_name]" class="loc-field-location_name" value="<?php echo e(old("itinerary_days.$index.location_name")); ?>">
<input type="hidden" name="itinerary_days[<?php echo e($index); ?>][lat]" class="loc-field-lat" value="<?php echo e(old("itinerary_days.$index.lat")); ?>">
<input type="hidden" name="itinerary_days[<?php echo e($index); ?>][lng]" class="loc-field-lng" value="<?php echo e(old("itinerary_days.$index.lng")); ?>">
</div>
<div class="col-md-12">
<label class="form-label">Day Images</label>
<?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'itinerary_days['.e($index).'][existing_image_ids]','multiple' => true,'selected' => $dayExistingImageIds,'label' => 'Select Day Images'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<small class="text-muted d-block mt-1">Choose one or more images from the library. Drag to reorder.</small>
</div>
<div class="col-md-12">
<label class="form-label">Description</label>
<div class="quill-editor border rounded" style="height: 220px;"></div>
<input type="hidden" name="itinerary_days[<?php echo e($index); ?>][description]" class="quill-hidden-input" value="<?php echo e(old("itinerary_days.$index.description")); ?>">
</div>
<div class="col-md-12">
<label class="form-label">Accommodation Tiers</label>
<?php $__currentLoopData = ['silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum / Private']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tierKey => $tierLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="row g-2 align-items-end mb-2">
<div class="col-md-3">
<label class="form-label small mb-1"><?php echo e($tierLabel); ?></label>
<input type="text"
name="itinerary_days[<?php echo e($index); ?>][accommodation_name_<?php echo e($tierKey); ?>]"
class="form-control"
placeholder="<?php echo e($tierLabel); ?> accommodation"
value="<?php echo e(old("itinerary_days.$index.accommodation_name_$tierKey")); ?>">
</div>
<div class="col-md-9">
<label class="form-label small mb-1"><?php echo e($tierLabel); ?> Image</label>
<?php $accExistingImageId = old("itinerary_days.$index.existing_accommodation_image_$tierKey"); ?>
<?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'itinerary_days['.e($index).'][existing_accommodation_image_'.e($tierKey).']','selected' => $accExistingImageId,'label' => 'Select '.e($tierLabel).' Image'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<small class="text-muted">Accommodation names and images can be left blank for the final day.</small>
</div>
<div class="col-md-6">
<label class="form-label">Meals</label>
<input type="text" name="itinerary_days[<?php echo e($index); ?>][meals]"
class="form-control"
value="<?php echo e(old("itinerary_days.$index.meals")); ?>">
</div>
</div>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
</div>
<button type="button" id="add-itinerary-day" class="btn btn-outline-primary mt-3">
<i class="bi bi-plus-circle"></i> Add New Day
</button>


<div id="itinerary-day-picker-template" class="d-none">
  <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'itinerary_days[__INDEX__][existing_image_ids]','multiple' => true,'selected' => [],'label' => 'Select Day Images'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
  <?php $__currentLoopData = ['silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum / Private']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tierKey => $tierLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'itinerary_days[__INDEX__][existing_accommodation_image_'.e($tierKey).']','selected' => null,'label' => 'Select '.e($tierLabel).' Image'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php if(old('inclusions_items', [])): ?>
<?php $__currentLoopData = old('inclusions_items', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="input-group mb-2 inclusion-item">
<input type="text" name="inclusions_items[]" class="form-control"
value="<?php echo e(old("inclusions_items.$index", $item)); ?>">
<button type="button" class="btn btn-outline-danger remove-inclusion">
<i class="bi bi-trash"></i>
</button>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
</div>
<button type="button" id="add-inclusion" class="btn btn-outline-success btn-sm mt-2">
<i class="bi bi-plus-circle"></i> Add Inclusion
</button>
</div>
<div class="col-md-6">
<label class="form-label fw-bold">What's Excluded</label>
<div id="exclusions-repeater">
<?php if(old('exclusions_items', [])): ?>
<?php $__currentLoopData = old('exclusions_items', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="input-group mb-2 exclusion-item">
<input type="text" name="exclusions_items[]" class="form-control"
value="<?php echo e(old("exclusions_items.$index", $item)); ?>">
<button type="button" class="btn btn-outline-danger remove-exclusion">
<i class="bi bi-trash"></i>
</button>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
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
<textarea name="highlights_text" class="form-control" rows="5" placeholder="See the Big Five&#10;Sunrise at Ngorongoro&#10;..."><?php echo e(old('highlights_text')); ?></textarea>
<small>Will be saved as JSON array</small>
</div>
</div>
<!-- Hero & Gallery -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label fw-bold">Hero Image (main cover)</label>
<div class="col-sm-10">
<div class="mb-3">
  <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'hero_image_id','selected' => old('hero_image_id'),'label' => 'Select from Media Library'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
  <small class="text-muted d-block mt-1">Choose an existing image from the library, or upload a new one below.</small>
</div>
<input type="file" name="hero_image" accept="image/*" class="form-control">
<small>Or upload a new image directly. Recommended: 1200&times;800 px</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label fw-bold">Gallery Images (multiple)</label>
<div class="col-sm-10">
<input type="file" name="gallery_images[]" multiple accept="image/*" class="form-control">
<small>Upload multiple images. Reorder after save if needed.</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Starting Point</label>
<div class="col-sm-10">
<input type="text" name="starting_point" class="form-control"
value="<?php echo e(old('starting_point')); ?>" placeholder="e.g. Nairobi, Kilimanjaro Airport">
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Ending Point</label>
<div class="col-sm-10">
<input type="text" name="ending_point" class="form-control"
value="<?php echo e(old('ending_point')); ?>" placeholder="e.g. Arusha, Zanzibar Airport">
</div>
</div>
<!-- Extra Sections -->
<div class="row mt-5">
<label class="col-sm-2 col-form-label fw-bold">Extra Sections (below main content)</label>
<div class="col-sm-10">
<div id="extra-sections-repeater">

<?php if(old('extra_sections')): ?>
<?php $__currentLoopData = old('extra_sections'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="section-item card mb-4 shadow-sm">
<div class="card-header d-flex justify-content-between align-items-center bg-light">
<h6 class="mb-0">Section <?php echo e($loop->iteration); ?></h6>
<button type="button" class="btn btn-sm btn-danger remove-section">
<i class="bi bi-trash"></i> Remove
</button>
</div>
<div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Section Image</label>

<?php $sectionExistingImageId = old("extra_sections.$index.existing_image_id", $section['image_id'] ?? null); ?>
<?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'extra_sections['.e($index).'][existing_image_id]','selected' => $sectionExistingImageId,'label' => 'Select Section Image'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
</div>
<div class="col-md-6">
<label class="form-label">Section Title</label>
<input type="text" name="extra_sections[<?php echo e($index); ?>][title]" class="form-control"
value="<?php echo e(old("extra_sections.$index.title", $section['title'] ?? '')); ?>">
</div>
<div class="col-12">
<label class="form-label">Section Content</label>
<div class="quill-editor quill-mini border rounded" style="height: 180px;"></div>
<input type="hidden" name="extra_sections[<?php echo e($index); ?>][content]" 
class="quill-hidden-input" 
value="<?php echo e(old("extra_sections.$index.content", $section['content'] ?? '')); ?>">
</div>
<div class="col-md-6">
<label class="form-label">Image Position</label>
<select name="extra_sections[<?php echo e($index); ?>][image_side]" class="form-select">
<option value="left" <?php echo e(($section['image_side'] ?? 'left') == 'left' ? 'selected' : ''); ?>>Image on Left</option>
<option value="right" <?php echo e(($section['image_side'] ?? 'left') == 'right' ? 'selected' : ''); ?>>Image on Right</option>
</select>
</div>
</div>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
</div>
<button type="button" id="add-extra-section" class="btn btn-outline-primary mt-3">
<i class="bi bi-plus-circle"></i> Add New Section
</button>


<div id="extra-section-picker-template" class="d-none">
  <?php if (isset($component)) { $__componentOriginala26ee479c72899be3529000ab55d5999 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala26ee479c72899be3529000ab55d5999 = $attributes; } ?>
<?php $component = App\View\Components\MediaPicker::resolve(['name' => 'extra_sections[__INDEX__][existing_image_id]','selected' => null,'label' => 'Select Section Image'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('media-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\MediaPicker::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $attributes = $__attributesOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__attributesOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala26ee479c72899be3529000ab55d5999)): ?>
<?php $component = $__componentOriginala26ee479c72899be3529000ab55d5999; ?>
<?php unset($__componentOriginala26ee479c72899be3529000ab55d5999); ?>
<?php endif; ?>
</div>
</div>
</div>
<!-- SEO -->
<h5 class="card-title mt-5">SEO Settings</h5>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Title</label>
<div class="col-sm-10">
<input type="text" name="meta_title" class="form-control" value="<?php echo e(old('meta_title')); ?>">
<small>Recommended: 50–60 characters</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Description</label>
<div class="col-sm-10">
<textarea name="meta_description" class="form-control" rows="3"><?php echo e(old('meta_description')); ?></textarea>
<small>Recommended: 150–160 characters</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Meta Keywords</label>
<div class="col-sm-10">
<input type="text" name="meta_keywords" class="form-control" value="<?php echo e(old('meta_keywords')); ?>">
<small>comma separated, 5–10 keywords</small>
</div>
</div>
<div class="row mb-3">
<label class="col-sm-2 col-form-label">Indexing</label>
<div class="col-sm-10">
<div class="form-check form-switch mt-2">
<input type="checkbox" class="form-check-input" id="no_robots_create" name="no_robots" value="1" <?php echo e(old('no_robots') ? 'checked' : ''); ?>>
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
<option value="draft">Draft</option>
<option value="published" selected>Published</option>
<option value="archived">Archived</option>
</select>
</div>
<div class="col-sm-5">
<div class="form-check mt-2">
<input class="form-check-input" type="checkbox" name="is_featured" value="1" <?php echo e(old('is_featured') ? 'checked' : ''); ?>>
<label class="form-check-label">Featured on homepage</label>
</div>
</div>
</div>
<div class="row mb-5 mt-5"> <!-- mt-5 adds high space on top -->
  <label class="col-sm-2 col-form-label"><strong>Trip Details</strong></label>
  <div class="col-sm-10">
    <div class="detail-item row mb-3 align-items-start">
      <div class="col-md-5">
        <input type="text" name="detail[title]" class="form-control" 
               placeholder="Make Your Dream Trip Come True With Afro-vertex Tours and Safaris">
      </div>
      <div class="col-md-7">
        <textarea name="detail[description]" class="form-control" rows="3" 
                  placeholder="Detail Description (e.g. Adjust sample itineraries to your preferences)">
        </textarea>
      </div>
    </div>
  </div>
</div>


<!-- FAQ Section -->
<div class="row mb-5 mt-5"> <!-- mt-5 adds high space on top -->
  <label class="col-sm-2 col-form-label"><strong>FAQs</strong></label>
  <div class="col-sm-10">
    <div id="faqs-wrapper">
      <!-- First FAQ item -->
      <div class="faq-item row mb-3 align-items-start">
        <div class="col-md-5">
          <input type="text" name="faqs[0][question]" class="form-control" placeholder="Question" >
        </div>
        <div class="col-md-5">
          <textarea name="faqs[0][answer]" class="form-control" rows="3" placeholder="Answer" ></textarea>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
        </div>
      </div>
    </div>
    <button type="button" id="add-faq" class="btn btn-sm btn-primary mt-2">
      Add FAQ
    </button>
  </div>
</div>

<script>
  $(document).ready(function () {
    let faqIndex = $('#faqs-wrapper .faq-item').length;

    // Add FAQ
    $('#add-faq').on('click', function () {
      faqIndex++;

      const newFaq = `
        <div class="faq-item row mb-3 align-items-start">
          <div class="col-md-5">
            <input type="text" name="faqs[${faqIndex}][question]" class="form-control" placeholder="Question" >
          </div>
          <div class="col-md-5">
            <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3" placeholder="Answer" ></textarea>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-danger remove-faq">Remove</button>
          </div>
        </div>
      `;

      $('#faqs-wrapper').append(newFaq);
    });

    // Remove FAQ
    $(document).on('click', '.remove-faq', function () {
      $(this).closest('.faq-item').remove();
    });
  });
</script>

<?php echo $__env->make('admin.partials.mega-menu-section', [
    'source' => null,
    'sourceType' => 'tour',
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Submit -->
<div class="row mb-3">
<label class="col-sm-2 col-form-label"></label>
<div class="col-sm-10">
<button type="submit" class="btn btn-primary">Create Tour Package</button>
<a href="<?php echo e(route('admin.tour-packages.index')); ?>" class="btn btn-secondary">Cancel</a>
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</section>

<!-- YOUR ORIGINAL SCRIPTS (100% UNCHANGED - Dropzone + TinyMCE re-init kept exactly as you had) -->
<script>
Dropzone.autoDiscover = false;
$(document).ready(function () {
// Hero Dropzone
new Dropzone("#hero-dropzone", {
url: "<?php echo e(route('admin.tour-packages.store')); ?>",
paramName: "hero_image",
maxFiles: 1,
acceptedFiles: "image/*",
addRemoveLinks: true,
dictDefaultMessage: "Drag & drop hero image here or click to upload",
init: function() {
this.on("success", function(file, response) {
});
}
});
// Gallery Dropzone
new Dropzone("#gallery-dropzone", {
url: "<?php echo e(route('admin.tour-packages.store')); ?>",
paramName: "gallery_images[]",
acceptedFiles: "image/*",
addRemoveLinks: true,
dictDefaultMessage: "Drag & drop gallery images here or click to upload",
});
$('.remove-media').click(function(e) {
e.preventDefault();
let mediaId = $(this).data('id');
if (confirm('Remove this image?')) {
$.ajax({
url: '/admin/media/' + mediaId,
type: 'DELETE',
data: { _token: '<?php echo e(csrf_token()); ?>' },
success: function() {
$(this).closest('.existing-media').remove();
}
});
}
});
});
</script>
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

<script>
$(document).ready(function () {
function initQuill(editorDiv) {
if (!editorDiv.length || editorDiv.hasClass('ql-container')) return;

const quill = new Quill(editorDiv[0], {
theme: 'snow',
modules: {
toolbar: [
[{ 'font': ['arial', 'helvetica', 'times-new-roman', 'georgia', 'verdana', 'courier-new'] }],
[{ 'header': [1, 2, 3, 4, 5, 6, false] }], // all 6 heading levels
['bold', 'italic', 'underline', 'strike'],
['blockquote', 'code-block'],
[{ 'list': 'ordered'}, { 'list': 'bullet' }],
['link'],
['clean']
]
}
});

const hiddenInput = editorDiv.siblings('.quill-hidden-input').first();
if (hiddenInput.val()) {
quill.root.innerHTML = hiddenInput.val();
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/vendor/leaflet/leaflet.js')); ?>"></script>
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
            '<div class="col-md-12"><label class="form-label">Description</label>' +
            '<div class="quill-editor border rounded" style="height: 220px;"></div>' +
            '<input type="hidden" name="itinerary_days[' + n + '][description]" class="quill-hidden-input"></div>' +
            '<div class="col-md-12"><label class="form-label">Accommodation Tiers</label>' +
            ['silver', 'gold', 'platinum'].map(function (tier) {
                var label = tier === 'platinum' ? 'Platinum / Private' : tier.charAt(0).toUpperCase() + tier.slice(1);
                return '<div class="row g-2 align-items-end mb-2"><div class="col-md-3"><label class="form-label small mb-1">' + label + '</label>' +
                    '<input type="text" name="itinerary_days[' + n + '][accommodation_name_' + tier + ']" class="form-control" placeholder="' + label + ' accommodation"></div>' +
                    '<div class="col-md-9 acc-tier-picker-slot" data-tier="' + tier + '"><label class="form-label small mb-1">' + label + ' Image</label></div></div>';
            }).join('') +
            '<small class="text-muted">Accommodation names and images can be left blank for the final day.</small></div>' +
            '<div class="col-md-6"><label class="form-label">Meals</label>' +
            '<input type="text" name="itinerary_days[' + n + '][meals]" class="form-control"></div>' +
            '</div></div></div>';

        var $newDay = $(html);
        $('#itinerary-repeater').append($newDay);

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

    fetch("<?php echo e(route('admin.location-search')); ?>", {
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


<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('tour-create-form');
    if (!form) return;
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (!btn || btn.dataset.saving === '1') return;
      btn.dataset.saving = '1';
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
    });
  });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\tour-packages\create.blade.php ENDPATH**/ ?>