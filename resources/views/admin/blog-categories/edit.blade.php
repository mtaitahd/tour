@extends('admin.layouts.app')
@section('title', 'Create Blog Category')

@section('content')
  <div class="pagetitle">
    <h1>Edit Category: {{ $category->name }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li><a href="{{ route('admin.blog-categories.index') }}">Blog Categories</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ route('admin.blog-categories.update', $category) }}">
              @method('PUT')
              @csrf

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Category Name *</label>
                <div class="col-sm-9">
                  <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Slug</label>
                <div class="col-sm-9">
                  <input type="text" name="slug" class="form-control" value="{{ old('name', $category->slug) }}">
                  <small class="text-muted">Auto-generated from name if left empty</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                  <textarea name="description" class="form-control" rows="5">{{ old('description' , $category->description) }}</textarea>
                  <small>Optional short description (shown in category pages if you create them later)</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label"></label>
                <div class="col-sm-9">
                  <button type="submit" class="btn btn-primary">Update Category</button>
                  <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection