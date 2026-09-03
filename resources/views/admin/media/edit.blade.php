@extends('admin.layouts.app')
@section('title', 'Edit Image')

@section('content')
  <div class="pagetitle">
    <h1>Edit Image</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.show', $image) }}">{{ $image->display_title }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
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
      <div class="col-lg-3 text-center">
        <img src="{{ $image->getUrl('thumb-webp') ?: $image->getUrl() }}"
             alt="{{ $image->meta?->alt_text ?: $image->name }}"
             class="img-fluid rounded shadow-sm mb-3">
        <p class="text-muted small">{{ $image->file_name }}</p>
      </div>

      <div class="col-lg-9">
        <div class="card">
          <div class="card-body">
            <form action="{{ route('admin.media.update', $image) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title</label>
                <div class="col-sm-10">
                  <input type="text" name="title" class="form-control" value="{{ old('title', $image->meta?->title) }}"
                         placeholder="{{ $image->name }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Alt Text</label>
                <div class="col-sm-10">
                  <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text', $image->meta?->alt_text) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Caption</label>
                <div class="col-sm-10">
                  <input type="text" name="caption" class="form-control" value="{{ old('caption', $image->meta?->caption) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control" rows="3">{{ old('description', $image->meta?->description) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Categories</label>
                <div class="col-sm-10">
                  <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                    @include('admin.media.partials.category-checkboxes', [
                        'categories' => $categories,
                        'selectedIds' => old('category_ids', $image->categories->pluck('id')->all()),
                    ])
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Tags</label>
                <div class="col-sm-10">
                  <div class="d-flex flex-wrap gap-2 border rounded p-3">
                    @forelse ($tags as $tag)
                      @php $checked = in_array($tag->id, old('tag_ids', $image->tags->pluck('id')->all())); @endphp
                      <label class="badge {{ $checked ? 'bg-primary' : 'bg-light text-dark' }} text-decoration-none" style="cursor: pointer;">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="d-none tag-toggle" {{ $checked ? 'checked' : '' }}>
                        {{ $tag->name }}
                      </label>
                    @empty
                      <span class="text-muted small">No tags yet.</span>
                    @endforelse
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                  <a href="{{ route('admin.media.show', $image) }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tag-toggle').forEach(function (input) {
        input.addEventListener('change', function () {
            const badge = this.closest('label');
            badge.classList.toggle('bg-primary', this.checked);
            badge.classList.toggle('text-dark', !this.checked);
            badge.classList.toggle('bg-light', !this.checked);
        });
    });
});
</script>
@endpush
