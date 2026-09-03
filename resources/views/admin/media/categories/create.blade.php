@extends('admin.layouts.app')
@section('title', 'Add Category')

@section('content')
  <div class="pagetitle">
    <h1>Add Category</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.categories.index') }}">Categories</a></li>
        <li class="breadcrumb-item active">Add</li>
      </ol>
    </nav>
  </div>

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

  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <form action="{{ route('admin.media.categories.store') }}" method="POST">
              @csrf

              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
              </div>

              <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                  <option value="">None (top-level category)</option>
                  @include('admin.media.categories.partials.parent-options', ['categories' => $categories, 'depth' => 0, 'selectedId' => old('parent_id')])
                </select>
                <small class="text-muted">Leave as "None" for a top-level category like "Destinations" or "Wildlife".</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Order</label>
                <input type="number" name="order" class="form-control w-25" value="{{ old('order', 999) }}" min="0">
                <small class="text-muted">Lower numbers appear first.</small>
              </div>

              <button type="submit" class="btn btn-primary">Create Category</button>
              <a href="{{ route('admin.media.categories.index') }}" class="btn btn-secondary ms-2">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
