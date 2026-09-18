@extends('admin.layouts.app')
@php
    use App\Models\Setting;
@endphp
@section('title', 'Traveller Stories Section')

@section('content')
  <div class="pagetitle">
    <h1>Traveller Stories Section</h1>
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
            <h5 class="card-title">Traveller Stories — "Our Latest Customer Reviews"</h5>
            <p class="text-muted">The section is split into two equal halves: an embedded map on the left and a Tripadvisor block (with logo) on the right.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.traveller-stories') }}">
              @csrf

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Section Badge</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_badge]" class="form-control" value="{{ Setting::get('traveller_badge', 'Traveller Stories') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Section Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_title]" class="form-control" value="{{ Setting::get('traveller_title', 'Our Latest Customer Reviews') }}">
                </div>
              </div>

              <hr>
              <h6 class="mt-4 mb-3 text-muted">Left Half — Embedded Map</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Map Heading</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_map_heading]" class="form-control" value="{{ Setting::get('traveller_map_heading', 'Where We Are') }}">
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Map Embed Code</label>
                <div class="col-sm-9">
                  <textarea name="settings[traveller_map_embed]" class="form-control" rows="5" placeholder="Paste your Google Maps embed code (iframe) here…">{{ Setting::get('traveller_map_embed') }}</textarea>
                  <small class="text-muted d-block mt-1">Paste the full embed code or just the iframe <code>src</code> URL. Only HTTPS URLs are rendered.</small>
                </div>
              </div>

              <hr>
              <h6 class="mt-4 mb-3 text-muted">Right Half — Tripadvisor</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tripadvisor Logo</label>
                <div class="col-sm-9">
                  <x-media-picker
                      name="settings[traveller_tripadvisor_logo_id]"
                      :selected="Setting::get('traveller_tripadvisor_logo_id')"
                      label="Select Tripadvisor logo from Media Library"
                  />
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Heading</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_tripadvisor_heading]" class="form-control" value="{{ Setting::get('traveller_tripadvisor_heading', 'Loved by Travellers on Tripadvisor') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Description</label>
                <div class="col-sm-9">
                  <textarea name="settings[traveller_tripadvisor_text]" class="form-control" rows="3" placeholder="Short blurb about your Tripadvisor rating and reviews…">{{ Setting::get('traveller_tripadvisor_text') }}</textarea>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tripadvisor Page Link</label>
                <div class="col-sm-9">
                  <input type="url" name="settings[traveller_tripadvisor_link]" class="form-control" value="{{ Setting::get('traveller_tripadvisor_link') }}" placeholder="https://www.tripadvisor.com/...">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tripadvisor Button Text</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_tripadvisor_button_text]" class="form-control" value="{{ Setting::get('traveller_tripadvisor_button_text', 'Read Tripadvisor Reviews') }}">
                </div>
              </div>

              <hr>
              <h6 class="mt-4 mb-3 text-muted">"Leave a Review" Button</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Review Link</label>
                <div class="col-sm-9">
                  <input type="url" name="settings[traveller_review_link]" class="form-control" value="{{ Setting::get('traveller_review_link', 'https://g.page/r/CR7qe8CBnNH7EBM/review') }}">
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Button Text</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[traveller_review_link_text]" class="form-control" value="{{ Setting::get('traveller_review_link_text', 'Leave a Review') }}">
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