@extends('admin.layouts.app')
@section('title', 'Edit Blog Post')

@section('content')
  <div class="pagetitle">
    <h1>Edit Blog Post</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.blog-posts.index') }}">Blog Posts</a></li>
        <li class="breadcrumb-item active">Edit: {{ Str::limit($blogPost->title, 40) }}</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit: {{ $blogPost->title }}</h5>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

            <form method="POST" action="{{ route('admin.blog-posts.update', $blogPost) }}" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              <!-- Title & Slug -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" required 
                         value="{{ old('title', $blogPost->title) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slug <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <input type="text" name="slug" class="form-control" required 
                         value="{{ old('slug', $blogPost->slug) }}">
                  <small class="text-muted">Used in public URL (changing it will break old links)</small>
                </div>
              </div>

              <!-- Category -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Category</label>
                <div class="col-sm-10">
                  <select name="category_id" class="form-select">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                      <option value="{{ $category->id }}" 
                              {{ old('category_id', $blogPost->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- Featured Image -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured Image</label>
                <div class="col-sm-10">
                  <div class="mb-3">
                    <x-media-picker
                        name="featured_image_id"
                        :selected="old('featured_image_id', $blogPost->featured_image_id)"
                        label="Select from Media Library"
                    />
                    <small class="text-muted d-block mt-1">Choose an existing image from the library, or upload a new one below.</small>
                  </div>

                  <!-- Current image preview -->
                  @if($blogPost->getFirstMedia('featured_image'))
                    <div class="mb-3">
                      <img src="{{ $blogPost->getFirstMediaUrl('featured_image', 'thumb') }}" 
                           alt="Current Featured Image" 
                           class="img-thumbnail" 
                           style="max-height: 180px;">
                      <small class="d-block text-muted mt-1">Current image</small>
                    </div>
                  @endif

                  <!-- Upload replacement -->
                  <input type="file" name="featured_image" accept="image/*" class="form-control">
                  <small class="text-muted">Or upload a new image directly. Leave empty to keep current image. Recommended: 1200&times;800 px</small>
                </div>
              </div>

              <!-- Content (TinyMCE) -->
              <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Content <span class="text-danger">*</span></label>
                <div class="col-sm-10">
                  <textarea name="content" class="form-control tinymce-editor" rows="15" required>
                    {{ old('content', $blogPost->content) }}
                  </textarea>
                </div>
              </div>

              <!-- Status & Publish Date -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-5">
                  <select name="status" class="form-select">
                    <option value="draft" {{ old('status', $blogPost->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $blogPost->status) == 'published' ? 'selected' : '' }}>Published</option>
                  </select>
                </div>

                <div class="col-sm-5">
                  <label class="form-label">Publish Date (optional)</label>
                  <input type="date" name="published_at" class="form-control" 
                         value="{{ old('published_at', $blogPost->published_at ? $blogPost->published_at->format('Y-m-d') : '') }}">
                </div>
              </div>

              <!-- SEO Fields -->
              <h5 class="mt-5 mb-3">SEO Settings</h5>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_title" class="form-control" 
                         value="{{ old('meta_title', $blogPost->meta_title) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                  <textarea name="meta_description" class="form-control" rows="3">
                    {{ old('meta_description', $blogPost->meta_description) }}
                  </textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Meta Keywords</label>
                <div class="col-sm-10">
                  <input type="text" name="meta_keywords" class="form-control" 
                         value="{{ old('meta_keywords', $blogPost->meta_keywords) }}">
                  <small class="text-muted">comma separated</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Indexing</label>
                <div class="col-sm-10">
                  <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="no_robots_edit" name="no_robots" value="1" 
                           {{ old('no_robots', $blogPost->no_robots) ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_robots_edit">Exclude from Google / sitemap (noindex)</label>
                  </div>
                  <small class="text-muted">When checked this post is removed from sitemap.xml and hidden from search engines.</small>
                </div>
              </div>

              <!-- Featured Post -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Featured</label>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" 
                           {{ old('is_featured', $blogPost->is_featured) ? 'checked' : '' }}>
                    <label class="form-check-label">Show as featured post</label>
                  </div>
                </div>
              </div>

              <!-- Submit -->
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Update Blog Post</button>
                  <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection