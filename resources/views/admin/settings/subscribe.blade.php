@extends('admin.layouts.app')
@php
    use App\Models\Setting;
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
@endsection