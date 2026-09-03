@extends('admin.layouts.app')
@section('title', 'Edit Testimonial')

@section('content')
  <div class="pagetitle">
    <h1>Edit Testimonial</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>
        <li class="breadcrumb-item active">Edit: {{ Str::limit($testimonial->name, 30) }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: {{ $testimonial->name }}</h5>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

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

            <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}">
              @csrf
              @method('PUT')

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="name" class="form-control" value="{{ old('name', $testimonial->name) }}" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Location</label>
                <div class="col-sm-10">
                  <input type="text" name="location" class="form-control" value="{{ old('location', $testimonial->location) }}" placeholder="e.g. United Kingdom">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Rating <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <select name="rating" class="form-select w-auto" required>
                    @for ($i = 5; $i >= 1; $i--)
                      <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Testimonial <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control" rows="5" required>{{ old('content', $testimonial->content) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Client Photo</label>
                <div class="col-sm-10">
                  <x-media-picker
                      name="avatar_image_id"
                      :selected="old('avatar_image_id', $testimonial->avatar_image_id)"
                      label="Select from Media Library"
                  />
                </div>
              </div>

              <h5 class="card-title mt-5">Where should this appear?</h5>
              <p class="text-muted">Leave both blank to show only in the homepage's general Testimonials section.</p>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Specific Tour</label>
                <div class="col-sm-10">
                  <select name="tour_package_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach($tourPackages as $tour)
                      <option value="{{ $tour->id }}" {{ old('tour_package_id', $testimonial->tour_package_id) == $tour->id ? 'selected' : '' }}>
                        {{ $tour->title }}
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted">Also shows this testimonial on that tour's own page.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Specific Destination</label>
                <div class="col-sm-10">
                  <select name="destination_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach($destinations as $destination)
                      <option value="{{ $destination->id }}" {{ old('destination_id', $testimonial->destination_id) == $destination->id ? 'selected' : '' }}>
                        {{ $destination->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row mb-3 mt-5">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="status" class="form-select" required>
                    <option value="draft" {{ old('status', $testimonial->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $testimonial->status) == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ old('status', $testimonial->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}>
                    <label class="form-check-label">Show in homepage's Testimonials & Reviews section</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control w-25" value="{{ old('order', $testimonial->order) }}">
                  <small>Lower number = appears higher in lists</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Testimonial</button>
                  <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
