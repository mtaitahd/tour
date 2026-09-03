@extends('admin.layouts.app')
@section('title', 'Create Page')

@section('content')
  <div class="pagetitle">
    <h1>Create New Page</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Page Information</h5>

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

            <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
              @csrf

              <!-- Title & Slug -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug</label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                  <small class="text-muted">Leave empty to auto-generate from title</small>
                </div>
              </div>

              <!-- Content (TinyMCE) -->
              <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Content</label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control tinymce-editor" rows="12">{{ old('content') }}</textarea>
                </div>
              </div>

              <!-- Hero Image Upload -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Hero Image</label>
                <div class="col-sm-10">
                  <input type="file" name="hero_image" accept="image/*" class="form-control">
                  <small class="text-muted">Recommended: 1200×800 px, max 5MB. Used as cover in page header and social sharing.</small>
                </div>
              </div>

              <!-- Status & Order -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-5">
                  <select name="status" class="form-select">
                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                  </select>
                </div>

                <div class="col-sm-5">
                  <label class="form-label">Order (position in menu)</label>
                  <input type="number" name="order" class="form-control" value="{{ old('order', 999) }}">
                  <small>Lower number = appears higher</small>
                </div>
              </div>

              <!-- SEO -->
              <h5 class="mt-5 mb-3">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords') }}">
                  <small class="text-muted">comma separated</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_create" name="no_robots" value="1" {{ old('no_robots') ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_robots_create">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this page is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>

              @include('admin.partials.mega-menu-section', [
                  'source' => null,
                  'sourceType' => 'page',
              ])

              <!-- Submit -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Create Page</button>
                  <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection