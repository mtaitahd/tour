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
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div><h5 class="card-title mb-1">Page Builder</h5><div class="text-muted small">Create and save your page in two clear steps.</div></div>
              <div class="small text-success" id="page-draft-status" aria-live="polite">Draft not saved yet</div>
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

            <form id="page-builder-form" method="POST" action="{{ route('admin.pages.store') }}" data-update-url="{{ route('admin.pages.update', ['page' => '__PAGE_ID__']) }}" enctype="multipart/form-data" novalidate>
              @csrf
              <input type="hidden" name="draft_id" id="page-draft-id" value="{{ old('draft_id', $draft->id ?? '') }}">
              <input type="hidden" name="_method" id="page-method" value="">

              <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-success" id="page-wizard-progress" style="width:50%" role="progressbar"></div></div>
              <div class="row g-2 mb-4">
                <div class="col-6"><button type="button" class="btn btn-success btn-sm w-100 page-step-indicator" data-page-step-indicator="1">1 · Page Information</button></div>
                <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 page-step-indicator" data-page-step-indicator="2">2 · SEO Settings</button></div>
              </div>

              <div class="page-step-panel" data-page-step="1">
              <h5 class="card-title">Page Information</h5>

              <!-- Title & Slug -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" required value="{{ old('title', request('title')) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug</label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" value="{{ old('slug', request('slug')) }}">
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

              <!-- Hero Image (Media Library picker only) -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Hero Image</label>
                <div class="col-sm-10">
                  {{--
                      Picker-only: images come from the Media Library, never from a
                      device upload. Admin\PageController::store() reads hero_image_id
                      and records the usage against the page. The controller has no
                      hasFile()/addMediaFromRequest() branch for 'hero_image' any
                      more, so a direct file POST is ignored rather than accepted.
                  --}}
                  <x-media-picker
                      name="hero_image_id"
                      :selected="old('hero_image_id')"
                      label="Select from Media Library"
                  />
                  <small class="text-muted d-block mt-1">Choose an existing image from the library. Used as cover in page header and social sharing.</small>
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

              <div class="d-flex justify-content-end mt-4"><button type="button" class="btn btn-primary page-step-next">Next: SEO Settings <i class="bi bi-arrow-right"></i></button></div>
              </div>

              <div class="page-step-panel d-none" data-page-step="2">
              <h5 class="card-title mb-3">SEO Settings</h5>

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

              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary page-step-back"><i class="bi bi-arrow-left"></i> Back</button>
              <!-- Submit -->
              <div class="d-flex gap-2 mb-3">
                  <button type="submit" class="btn btn-primary">Create Page</button>
                  <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary ms-2">Cancel</a>
              </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>

  @include('admin.pages.partials.wizard-script', ['autosaveEnabled' => true])
@endsection
