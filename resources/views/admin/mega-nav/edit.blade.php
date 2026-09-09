@extends('admin.layouts.app')
@section('title', 'Edit Mega Nav Item')

@section('content')
  <div class="pagetitle">
    <h1>Edit Mega Nav Item</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.mega-nav.index') }}">Mega Nav</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit Mega Nav Item</h5>

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

            <form method="POST" action="{{ route('admin.mega-nav.update', $megaNav) }}">
              @csrf
              @method('PUT')

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Parent Menu <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <select name="parent_menu_key" class="form-select" required>
                    <option value="">— Select parent menu —</option>
                    @foreach ($parents as $key => $def)
                      <option value="{{ $key }}" {{ old('parent_menu_key', $megaNav->parent_menu_key) === $key ? 'selected' : '' }}>
                        {{ $def['label'] }}
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted">The top-level menu this item appears under (Kilimanjaro / Safari / Day Trips).</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Left Panel Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="menu_label" class="form-control" maxlength="120" required
                         placeholder="e.g. 4-Day Tanzania Safari"
                         value="{{ old('menu_label', $megaNav->menu_label) }}">
                  <small class="text-muted">Shown in the left column of the mega menu.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Middle Heading <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="heading" class="form-control" maxlength="255" required
                         placeholder="e.g. 4-Day Tanzania Safari"
                         value="{{ old('heading', $megaNav->heading) }}">
                  <small class="text-muted">The heading shown at the top of the middle column.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Short Description <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <textarea name="short_description" class="form-control" rows="3" maxlength="500" required
                            placeholder="A short description shown in the middle column.">{{ old('short_description', $megaNav->short_description) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Link URL <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="url" name="button_url_override" class="form-control" maxlength="2048" required
                         placeholder="https://www.afrovertex.com/tours/..."
                         value="{{ old('button_url_override', $megaNav->button_url_override) }}">
                  <small class="text-muted">Where this item links to, e.g. a tour page.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">CTA Button Label</label>
                <div class="col-sm-10">
                  <input type="text" name="button_label" class="form-control" maxlength="120"
                         placeholder="e.g. View This Tour"
                         value="{{ old('button_label', $megaNav->button_label) }}">
                  <small class="text-muted">Defaults to "Read More" if left blank.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Image <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <x-media-picker
                      name="image_id"
                      :selected="old('image_id', $megaNav->image_id ?: null)"
                      label="Select Mega Nav Image"
                  />
                  <small class="text-muted d-block mt-1">Large image shown in the right column of the mega menu.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Badge</label>
                <div class="col-sm-10">
                  <input type="text" name="badge_text" class="form-control" maxlength="40"
                         placeholder="e.g. Popular, New, Free PDF"
                         value="{{ old('badge_text', $megaNav->badge_text) }}">
                  <small class="text-muted">Optional small pill shown next to the title.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Display Order</label>
                <div class="col-sm-10">
                  <input type="number" name="display_order" class="form-control w-25" min="0" max="9999"
                         value="{{ old('display_order', $megaNav->display_order) }}">
                  <small>Lower number = appears higher in the menu.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           id="is_active" {{ old('is_active', $megaNav->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Show in Mega Menu</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Mega Nav Item</button>
                  <a href="{{ route('admin.mega-nav.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection