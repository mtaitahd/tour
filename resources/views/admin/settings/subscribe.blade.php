@extends('admin.layouts.app')
@php
    use App\Models\Setting;

    $ytChannel = Setting::get('subscribe_youtube_url') ?: Setting::get('social_youtube');

    // The same default cards the home page shows before any admin edit is saved
    // — so this editor always mirrors what visitors currently see on the index.
    $defaultVideos = [
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'The Great Migration — Serengeti Up Close',
            'views'     => '12K views',
            'when'      => '2 weeks ago',
            'url'       => $ytChannel ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'Climbing Mount Kilimanjaro — Machame Route',
            'views'     => '8.4K views',
            'when'      => '1 month ago',
            'url'       => $ytChannel ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/botswana.webp'),
            'title'     => 'Ngorongoro Crater — A Day in the Caldera',
            'views'     => '6.1K views',
            'when'      => '2 months ago',
            'url'       => $ytChannel ?: '#',
        ],
    ];

    $currentVideos = Setting::get('subscribe_videos') === null
        ? $defaultVideos
        : Setting::json('subscribe_videos');
@endphp
@section('title', 'Subscribe Section')

@section('content')
  <div class="pagetitle">
    <h1>Subscribe Section</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Website Content</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">YouTube Subscribe Section</h5>
            <p class="text-muted">This section is shown on the <strong>home page only</strong>. A maximum of <strong>3</strong> video cards are displayed.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.subscribe-content') }}">
              @csrf

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Heading</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[subscribe_heading]" class="form-control" value="{{ Setting::get('subscribe_heading', 'Subscribe Afro&#8209;Vertex Tours &amp; Safaris on YouTube') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">YouTube Channel Link</label>
                <div class="col-sm-9">
                  <input type="url" name="settings[subscribe_youtube_url]" class="form-control" value="{{ Setting::get('subscribe_youtube_url') ?: Setting::get('social_youtube') }}" placeholder="https://www.youtube.com/@yourchannel">
                  <small class="text-muted d-block mt-1">Used for the video links and the "Subscribe on YouTube" button.</small>
                </div>
              </div>

              <hr class="my-4">
              <h6 class="mb-1">Video Cards</h6>
              <p class="text-muted">These are the videos currently shown on the home page. Only the <strong>first 3</strong> are displayed; add, edit or delete them freely.</p>

              <div class="row mb-2">
                <div class="col-sm-12">
                  <div id="subscribe-videos-list">
                    @if (count($currentVideos) > 0)
                      @foreach ($currentVideos as $index => $video)
                        <div class="card mb-3 shadow-sm subscribe-video-item">
                          <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted">Video #{{ $loop->iteration }}</small>
                            <button type="button" class="btn btn-sm btn-outline-danger subscribe-video-remove">
                              <i class="bi bi-trash"></i> Delete
                            </button>
                          </div>
                          <div class="card-body">
                            <div class="row g-2 mb-2">
                              <div class="col-md-8">
                                <label class="form-label small text-muted mb-1">Title</label>
                                <input type="text" name="subscribe_videos[{{ $index }}][title]" class="form-control" placeholder="e.g. The Great Migration — Serengeti Up Close" value="{{ $video['title'] ?? '' }}">
                              </div>
                              <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Thumbnail URL (16:9)</label>
                                <input type="url" name="subscribe_videos[{{ $index }}][thumbnail]" class="form-control" placeholder="https://i.ytimg.com/vi/VIDEO_ID/maxresdefault.jpg" value="{{ $video['thumbnail'] ?? '' }}">
                              </div>
                            </div>
                            <div class="row g-2">
                              <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Views text</label>
                                <input type="text" name="subscribe_videos[{{ $index }}][views]" class="form-control" placeholder="e.g. 12K views" value="{{ $video['views'] ?? '' }}">
                              </div>
                              <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Posted when</label>
                                <input type="text" name="subscribe_videos[{{ $index }}][when]" class="form-control" placeholder="e.g. 2 weeks ago" value="{{ $video['when'] ?? '' }}">
                              </div>
                              <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Video URL</label>
                                <input type="url" name="subscribe_videos[{{ $index }}][url]" class="form-control" placeholder="https://www.youtube.com/watch?v=…" value="{{ $video['url'] ?? '' }}">
                              </div>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    @endif
                  </div>
                  <button type="button" id="add-subscribe-video" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Video
                  </button>
                </div>
              </div>

              <hr class="my-4">

              <div class="row mb-3">
                <div class="col-sm-9 offset-sm-3">
                  <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="subscribe_hidden" name="settings[subscribe_hidden]" value="1" {{ Setting::get('subscribe_hidden') ? 'checked' : '' }}>
                    <label class="form-check-label" for="subscribe_hidden">Hide the subscribe section on the home page</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    (function () {
      const list = document.getElementById('subscribe-videos-list');
      const addBtn = document.getElementById('add-subscribe-video');
      if (!list || !addBtn) return;

      addBtn.addEventListener('click', function () {
        const index = list.querySelectorAll('.subscribe-video-item').length;
        const div = document.createElement('div');
        div.className = 'card mb-3 shadow-sm subscribe-video-item';
        div.innerHTML =
          '<div class="card-header py-2 d-flex justify-content-between align-items-center">' +
            '<small class="text-muted">Video #' + (index + 1) + '</small>' +
            '<button type="button" class="btn btn-sm btn-outline-danger subscribe-video-remove"><i class="bi bi-trash"></i> Delete</button>' +
          '</div>' +
          '<div class="card-body">' +
            '<div class="row g-2 mb-2">' +
              '<div class="col-md-8">' +
                '<label class="form-label small text-muted mb-1">Title</label>' +
                '<input type="text" name="subscribe_videos[' + index + '][title]" class="form-control" placeholder="e.g. The Great Migration — Serengeti Up Close">' +
              '</div>' +
              '<div class="col-md-4">' +
                '<label class="form-label small text-muted mb-1">Thumbnail URL (16:9)</label>' +
                '<input type="url" name="subscribe_videos[' + index + '][thumbnail]" class="form-control" placeholder="https://i.ytimg.com/vi/VIDEO_ID/maxresdefault.jpg">' +
              '</div>' +
            '</div>' +
            '<div class="row g-2">' +
              '<div class="col-md-4">' +
                '<label class="form-label small text-muted mb-1">Views text</label>' +
                '<input type="text" name="subscribe_videos[' + index + '][views]" class="form-control" placeholder="e.g. 12K views">' +
              '</div>' +
              '<div class="col-md-4">' +
                '<label class="form-label small text-muted mb-1">Posted when</label>' +
                '<input type="text" name="subscribe_videos[' + index + '][when]" class="form-control" placeholder="e.g. 2 weeks ago">' +
              '</div>' +
              '<div class="col-md-4">' +
                '<label class="form-label small text-muted mb-1">Video URL</label>' +
                '<input type="url" name="subscribe_videos[' + index + '][url]" class="form-control" placeholder="https://www.youtube.com/watch?v=…">' +
              '</div>' +
            '</div>' +
          '</div>';
        list.appendChild(div);
      });

      list.addEventListener('click', function (e) {
        const btn = e.target.closest('.subscribe-video-remove');
        if (!btn) return;
        if (!window.confirm('Delete this video card?')) return;
        e.target.closest('.subscribe-video-item').remove();
      });
    })();
  </script>
@endsection