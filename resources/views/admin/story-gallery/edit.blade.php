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

    $checklistRaw = Setting::get('home_about_checklist');
    if ($checklistRaw) {
        $checklistItems = array_filter(array_map('trim', explode("\n", $checklistRaw)));
    } else {
        $checklistItems = ['Custom Safari Itineraries', '24/7 Customer Support', 'Licensed & Insured', 'Professional Local Guides', 'Best Price Guarantee', 'Sustainable Tourism'];
    }
    $homePolaroids = [
        ['src' => asset('public/111/serengeti-great-migration.webp'), 'caption' => Setting::get('home_about_polaroid_1', 'Wild Encounters')],
        ['src' => asset('public/114/serengeti-elephants.webp'), 'caption' => Setting::get('home_about_polaroid_2', 'Memories Forever')],
        ['src' => asset('public/112/serengeti-lions-1.webp'), 'caption' => Setting::get('home_about_polaroid_3', 'Breathtaking Views')],
    ];
  @endphp

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card mb-4">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Current Homepage &mdash; About Us / Our Story Section</h5>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil-square"></i> Edit in Settings
            </a>
          </div>
          <div class="card-body">
            <p class="text-muted">This is exactly what the homepage "Our Story" section shows right now &mdash; text and images together.</p>
            <div class="row g-4">
              <div class="col-lg-7">
                <span class="badge bg-light text-dark mb-2">{{ Setting::get('home_about_eyebrow', 'Our Story') }}</span>
                <h4 class="mb-2">{!! Setting::get('home_about_title') ?: 'About Us &ndash; Afro-Vertex Tours &amp; Safaris' !!}</h4>
                <div class="mb-3">{!! Setting::get('home_about_text') ?: 'Welcome to Afro-Vertex Africa Tanzania Safari LTD, where unforgettable African adventures meet the untamed beauty of nature. Based in Tanzania, we are a trusted safari operator and destination management company dedicated to creating immersive wildlife experiences, tailor-made journeys, mountain adventures, and relaxing beach escapes across East Africa.' !!}</div>
                <ul class="mb-3" style="columns:2; column-gap:1.5rem;">
                  @foreach ($checklistItems as $item)
                    <li class="mb-1">{{ $item }}</li>
                  @endforeach
                </ul>
                <a href="{{ Setting::get('home_about_btn_link') ?: route('page.show', 'about-us') }}" class="btn btn-primary btn-sm">
                  {{ Setting::get('home_about_btn_text', 'Discover More') }}
                </a>
              </div>
              <div class="col-lg-5">
                <div class="row g-3">
                  @foreach ($homePolaroids as $polaroid)
                    <div class="col-4">
                      <img src="{{ $polaroid['src'] }}" alt="{{ $polaroid['caption'] }}" class="img-fluid rounded shadow-sm mb-1" loading="lazy">
                      <small class="d-block text-muted text-center">{{ $polaroid['caption'] }}</small>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Our Story Images &amp; Captions</h5>

            <p class="text-muted">
              These images and captions are shown in the <strong>Our Story</strong> section of the
              public About page. Add new images, change their captions, or remove images entirely.
              Blank image slots are never shown on the page.
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
@endpush