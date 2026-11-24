@extends('admin.layouts.app')
@section('title', 'About Page Story')

@section('content')
  <div class="pagetitle">
    <h1>About Page Story</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
        <li class="breadcrumb-item active">About Page Story</li>
      </ol>
    </nav>
  </div>

<section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage About Page Story</h5>

            <p class="text-muted">Edit the About page story images and captions.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the errors below.</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <form action="{{ route('admin.our-story.update') }}" method="POST">
              @csrf
              @method('PUT')

              <h6 class="text-muted mb-3 border-bottom pb-1">About Page &mdash; Our Story Section</h6>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label fw-bold" for="story_title">Our Story Heading</label>
                <div class="col-sm-9">
                  <input type="text" id="story_title" name="story_title" class="form-control"
                         placeholder="e.g. Our Story & Passion"
                         value="{{ old('story_title', $page->story_title ?? '') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Story Images &amp; Captions</label>
                <div class="col-sm-9">
                  <div id="story-gallery-repeater">
                    @php
                      $gallery = old('story_gallery', $page->story_gallery ?? []);
                      $slotCount = max(min(count($gallery) + 6, 30), 9);
                    @endphp

                    @for ($i = 0; $i < $slotCount; $i++)
                      @php
                        $storyImage = $gallery[$i] ?? [];
                        $isEmpty = empty($storyImage['image_id']) && trim($storyImage['caption'] ?? '') === '';
                      @endphp
                      <div class="story-slot card mb-3 shadow-sm {{ $isEmpty ? 'story-slot-empty d-none' : '' }}">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                          <small class="text-muted">Story Image #{{ $i + 1 }}</small>
                          <button type="button" class="btn btn-sm btn-outline-danger story-slot-remove">
                            <i class="bi bi-trash"></i> Remove
                          </button>
                        </div>
                        <div class="card-body">
                          <div class="row g-3 align-items-start">
                            <div class="col-md-3">
                              <label class="form-label small">Image</label>
                              <x-media-picker
                                  name="story_gallery[{{ $i }}][image_id]"
                                  :selected="$storyImage['image_id'] ?? null"
                                  label="Select Image"
                              />
                            </div>
                            <div class="col-md-9">
                              <label class="form-label small">Caption</label>
                              <input type="text" name="story_gallery[{{ $i }}][caption]"
                                     class="form-control story-slot-caption"
                                     placeholder="e.g. Our team at Kilimanjaro base camp"
                                     value="{{ $storyImage['caption'] ?? '' }}">
                            </div>
                          </div>
                        </div>
                      </div>
                    @endfor
                  </div>

                  <button type="button" id="add-story-slot" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Image
                  </button>
                </div>
              </div>

              <hr class="my-4">

              <a href="{{ route('admin.landing.edit', 'about') }}" class="btn btn-outline-primary mb-3">Edit Homepage Our Story in Landing Page</a>
              <div class="text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-lg"></i> Save Changes
                </button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Back to Pages</a>
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
(function () {
    const repeater = document.getElementById('story-gallery-repeater');
    const addBtn = document.getElementById('add-story-slot');
    if (!repeater || !addBtn) return;

    function refreshAddButton() {
        const hidden = repeater.querySelector('.story-slot.d-none');
        addBtn.disabled = !hidden;
        addBtn.querySelector('span')?.remove();
    }

    addBtn.addEventListener('click', function () {
        const slot = repeater.querySelector('.story-slot.d-none');
        if (!slot) return;
        slot.classList.remove('d-none');
        const caption = slot.querySelector('.story-slot-caption');
        if (caption) caption.focus();
        refreshAddButton();
    });

    repeater.addEventListener('click', function (e) {
        const btn = e.target.closest('.story-slot-remove');
        if (!btn) return;

        const slot = btn.closest('.story-slot');
        if (!slot) return;

        if (!window.confirm('Remove this story image?')) return;

        // Clear the media picker selection (same as its own remove button) and the caption.
        const picker = slot.querySelector('.media-picker');
        if (picker) {
            picker.querySelectorAll('.media-picker-thumb').forEach(function (thumb) {
                thumb.remove();
            });
            const input = picker.querySelector('.media-picker-input');
            if (input) input.value = '';
        }
        const caption = slot.querySelector('.story-slot-caption');
        if (caption) caption.value = '';

        slot.classList.add('d-none');
        refreshAddButton();
    });

    refreshAddButton();
})();
</script>

@endpush