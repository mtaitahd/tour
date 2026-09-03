@extends('admin.layouts.app')
@section('title', 'Edit Activity')

@section('content')
  <div class="pagetitle">
    <h1>Edit Activity</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.activities.index') }}">Activities</a></li>
        <li class="breadcrumb-item active">Edit: {{ Str::limit($activity->name, 30) }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: {{ $activity->name }}</h5>
            <p class="text-muted">Linked to {{ $activity->tour_count }} published tour(s). Manage which tours include this activity from each tour's edit page.</p>

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

            <form method="POST" action="{{ route('admin.activities.update', $activity) }}">
              @csrf
              @method('PUT')

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="name" class="form-control" value="{{ old('name', $activity->name) }}" required>
                  <small class="text-muted">Current slug: {{ $activity->slug }} — regenerated automatically if you change the name.</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control" rows="4">{{ old('description', $activity->description) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Icon</label>
                <div class="col-sm-10">
                  <input type="text" name="icon" class="form-control" value="{{ old('icon', $activity->icon) }}" placeholder="e.g. isax isax-airplane">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Image</label>
                <div class="col-sm-10">
                  <x-media-picker
                      name="hero_image_id"
                      :selected="old('hero_image_id', $activity->hero_image_id)"
                      label="Select from Media Library"
                  />
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Active</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $activity->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Visible on the site</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured', $activity->is_featured) ? 'checked' : '' }}>
                    <label class="form-check-label">Show in homepage's "What We Offer" section</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Order</label>
                <div class="col-sm-10">
                  <input type="number" name="order" class="form-control w-25" value="{{ old('order', $activity->order) }}">
                  <small>Lower number = appears higher in lists</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Activity</button>
                  <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
