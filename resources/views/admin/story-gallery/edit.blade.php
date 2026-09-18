@extends('admin.layouts.app')
@section('title', 'Our Story Images & Captions')

@section('content')
  <div class="pagetitle">
    <h1>Our Story Images & Captions</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
        <li class="breadcrumb-item active">Our Story Images &amp; Captions</li>
      </ol>
    </nav>
  </div>

@php
    use App\Models\Setting;

    // Homepage polaroid photo stack. Mirrors the public home page fallbacks so
    // the editor always shows exactly what visitors currently see.
    $defaultPolaroids = [
        ['src' => asset('public/111/serengeti-great-migration.webp'), 'caption' => Setting::get('home_about_polaroid_1', 'Wild Encounters')],
        ['src' => asset('public/114/serengeti-elephants.webp'), 'caption' => Setting::get('home_about_polaroid_2', 'Memories Forever')],
        ['src' => asset('public/112/serengeti-lions-1.webp'), 'caption' => Setting::get('home_about_polaroid_3', 'Breathtaking Views')],
    ];

    $oldPolaroids = old('homepage_about_polaroids');
    if (is_array($oldPolaroids) && count($oldPolaroids) > 0) {
        $homePolaroids = $oldPolaroids;
    } else {
        $homePolaroids = Setting::get('home_about_polaroids') === null
            ? $defaultPolaroids
            : Setting::json('home_about_polaroids');
    }

    $homePolaroids = array_slice($homePolaroids, 0, 3);
    while (count($homePolaroids) < 3) {
        $homePolaroids[] = ['src' => '', 'caption' => ''];
    }
@endphp

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Our Story Images &amp; Captions</h5>

            <p class="text-muted">
              Manage the <strong>About page</strong> story images/captions and the
              <strong>home page</strong> "About Us / Our Story" section below. Anything
              you change or delete here updates the site immediately — no separate
              settings page needed.
            </p>

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

              <h6 class="text-muted mb-1 border-bottom pb-1">Home Page &mdash; About Us / Our Story Section</h6>
              <p class="text-muted">Edit the text and delete the images here; the home page updates immediately.</p>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Eyebrow Text</label>
                <div class="col-sm-9">
                  <input type="text" name="homepage_about[eyebrow]" class="form-control"
                         placeholder="Our Story"
                         value="{{ old('homepage_about.eyebrow', Setting::get('home_about_eyebrow', '')) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Section Title</label>
                <div class="col-sm-9">
                  <input type="text" name="homepage_about[title]" class="form-control"
                         placeholder="About Us – Afro-Vertex Tours &amp; Safaris"
                         value="{{ old('homepage_about.title', Setting::get('home_about_title', '')) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                  <textarea name="homepage_about[text]" class="form-control" rows="5"
                            placeholder="Welcome to Afro-Vertex Africa Tanzania Safari LTD…">{{ old('homepage_about.text', Setting::get('home_about_text', '')) }}</textarea>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Checklist Items</label>
                <div class="col-sm-9">
                  <textarea name="homepage_about[checklist]" class="form-control" rows="3"
                            placeholder="Custom Safari Itineraries{{ "\n" }}24/7 Customer Support{{ "\n" }}Licensed &amp; Insured">{{ old('homepage_about.checklist', Setting::get('home_about_checklist', '')) }}</textarea>
                  <small class="text-muted">One item per line, e.g. Custom Safari Itineraries, 24/7 Customer Support, Licensed &amp; Insured&hellip;</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Button Text</label>
                <div class="col-sm-9">
                  <input type="text" name="homepage_about[btn_text]" class="form-control"
                         placeholder="Discover More"
                         value="{{ old('homepage_about.btn_text', Setting::get('home_about_btn_text', '')) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Button Link</label>
                <div class="col-sm-9">
                  <input type="text" name="homepage_about[btn_link]" class="form-control"
                         placeholder="/pages/about-us"
                         value="{{ old('homepage_about.btn_link', Setting::get('home_about_btn_link', '')) }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Polaroid Images</label>
                <div class="col-sm-9">
                  <p class="text-muted mb-2">The three images in the homepage photo stack. Delete an image to remove it from the page.</p>
                  <div id="homepage-polaroid-repeater">
                    @foreach ($homePolaroids as $i => $polaroid)
                      @php $polaroidIsEmpty = empty($polaroid['src']) && trim($polaroid['caption'] ?? '') === ''; @endphp
                      <div class="polaroid-slot card mb-3 shadow-sm {{ $polaroidIsEmpty ? 'polaroid-slot-empty d-none' : '' }}">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                          <small class="text-muted">Polaroid Image #{{ $loop->iteration }}</small>
                          <button type="button" class="btn btn-sm btn-outline-danger polaroid-slot-remove">
                            <i class="bi bi-trash"></i> Delete
                          </button>
                        </div>
                        <div class="card-body">
                          <div class="row g-3 align-items-start">
                            <div class="col-md-3">
                              <img src="{{ $polaroid['src'] }}" alt="" class="img-fluid rounded mb-1 polaroid-slot-thumb" style="display:{{ empty($polaroid['src']) ? 'none' : 'block' }}">
                              <label class="form-label small">Image URL (16:9)</label>
                              <input type="url" name="homepage_about_polaroids[{{ $i }}][src]" class="form-control form-control-sm polaroid-slot-src"
                                     placeholder="https://i.ytimg.com/vi/VIDEO_ID/maxresdefault.jpg"
                                     value="{{ $polaroid['src'] }}">
                            </div>
                            <div class="col-md-9">
                              <label class="form-label small">Caption</label>
                              <input type="text" name="homepage_about_polaroids[{{ $i }}][caption]"
                                     class="form-control polaroid-slot-caption"
                                     placeholder="e.g. Wild Encounters"
                                     value="{{ $polaroid['caption'] }}">
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                  <button type="button" id="add-polaroid-slot" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Image
                  </button>
                </div>
              </div>

              <div class="d-flex gap-2">
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
<script>
(function () {
    const repeater = document.getElementById('homepage-polaroid-repeater');
    const addBtn = document.getElementById('add-polaroid-slot');
    if (!repeater || !addBtn) return;

    function refreshAddButton() {
        const hidden = repeater.querySelector('.polaroid-slot.d-none');
        addBtn.disabled = !hidden;
    }

    addBtn.addEventListener('click', function () {
        const slot = repeater.querySelector('.polaroid-slot.d-none');
        if (!slot) return;
        slot.classList.remove('d-none');
        refreshAddButton();
    });

    repeater.addEventListener('click', function (e) {
        const btn = e.target.closest('.polaroid-slot-remove');
        if (!btn) return;

        const slot = btn.closest('.polaroid-slot');
        if (!slot) return;

        if (!window.confirm('Delete this polaroid image from the home page?')) return;

        // Empty the fields so the slot is skipped on save, then hide it.
        slot.querySelectorAll('input').forEach(function (input) { input.value = ''; });
        const thumb = slot.querySelector('.polaroid-slot-thumb');
        if (thumb) thumb.style.display = 'none';
        slot.classList.add('d-none');
        refreshAddButton();
    });

    refreshAddButton();
})();
</script>
@endpush