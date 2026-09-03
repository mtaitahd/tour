@extends('admin.layouts.app')
@section('title', 'Image Details')

@section('content')
  <div class="pagetitle">
    <h1>Image Details</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item active">{{ $image->display_title }}</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="row">
      {{-- Preview --}}
      <div class="col-lg-5">
        <div class="card">
          <div class="card-body text-center">
            <img src="{{ $image->getUrl('medium-webp') ?: $image->getUrl() }}"
                 alt="{{ $image->meta?->alt_text ?: $image->name }}"
                 class="img-fluid rounded">
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small mb-3">File Information</h6>
            <table class="table table-sm mb-0">
              <tbody>
                <tr><th class="text-muted" style="width: 40%;">File name</th><td>{{ $image->file_name }}</td></tr>
                <tr><th class="text-muted">File size</th><td>{{ $image->human_readable_size }}</td></tr>
                <tr><th class="text-muted">Dimensions</th><td>{{ $image->width && $image->height ? "{$image->width} &times; {$image->height} px" : 'Unknown' }}</td></tr>
                <tr><th class="text-muted">MIME type</th><td>{{ $image->mime_type }}</td></tr>
                <tr><th class="text-muted">Uploaded by</th><td><i class="bi bi-person-up me-1 text-muted"></i>{{ $image->uploaded_by_name }}</td></tr>
                <tr><th class="text-muted">Uploaded</th><td>{{ $image->created_at->format('M j, Y \a\t g:ia') }}</td></tr>
                <tr>
                  <th class="text-muted">Used in</th>
                  <td>
                    @if ($image->usage_count > 0)
                      <span class="badge bg-primary">{{ $image->usage_count }} location{{ $image->usage_count === 1 ? '' : 's' }}</span>
                    @else
                      <span class="badge bg-light text-muted">Not used anywhere</span>
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        @if (!empty($usageLocations))
          <div class="card">
            <div class="card-body">
              <h6 class="card-title text-uppercase text-muted small mb-3">Used In</h6>
              <ul class="list-unstyled mb-0 small">
                @foreach ($usageLocations as $usage)
                  <li class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ $usage['label'] }}</span>
                    <form action="{{ route('admin.media.detach', $image) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Unlink this image from here? The image stays in the library.');">
                      @csrf
                      <input type="hidden" name="model_type" value="{{ \App\Http\Controllers\Admin\MediaLibraryController::aliasForModel($usage['model_type']) }}">
                      <input type="hidden" name="model_id" value="{{ $usage['model_id'] }}">
                      <input type="hidden" name="context" value="{{ $usage['context'] }}">
                      <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Unlink">
                        <i class="bi bi-x"></i>
                      </button>
                    </form>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif
      </div>

      {{-- Editable metadata --}}
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">Details</h5>
              <div>
                @if ($image->usage_count === 0)
                  <form action="{{ route('admin.media.destroy', $image) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this image permanently? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                @else
                  <button type="button" class="btn btn-outline-danger btn-sm" disabled
                          title="Used in {{ $image->usage_count }} location(s) — unlink it from those first">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                @endif
              </div>
            </div>

            <form action="{{ route('admin.media.update', $image) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $image->meta?->title) }}"
                       placeholder="{{ $image->name }}">
                <small class="text-muted">Shown throughout the admin area if set; falls back to the file name.</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Alt Text</label>
                <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text', $image->meta?->alt_text) }}"
                       placeholder="Describe the image for accessibility and SEO">
              </div>

              <div class="mb-3">
                <label class="form-label">Caption</label>
                <input type="text" name="caption" class="form-control" value="{{ old('caption', $image->meta?->caption) }}">
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $image->meta?->description) }}</textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                  @include('admin.media.partials.category-checkboxes', [
                      'categories' => $categories,
                      'selectedIds' => old('category_ids', $image->categories->pluck('id')->all()),
                  ])
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label">Tags</label>
                <div class="d-flex flex-wrap gap-2 border rounded p-3">
                  @forelse ($tags as $tag)
                    @php $checked = in_array($tag->id, old('tag_ids', $image->tags->pluck('id')->all())); @endphp
                    <label class="badge {{ $checked ? 'bg-primary' : 'bg-light text-dark' }} text-decoration-none" style="cursor: pointer;">
                      <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="d-none tag-toggle" {{ $checked ? 'checked' : '' }}>
                      {{ $tag->name }}
                    </label>
                  @empty
                    <span class="text-muted small">No tags yet — <a href="{{ route('admin.media.tags.index') }}">create one</a>.</span>
                  @endforelse
                </div>
              </div>

              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="{{ route('admin.media.index') }}" class="btn btn-secondary ms-2">Back to Library</a>
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
