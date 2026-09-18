@extends('admin.layouts.app')
@php
    use App\Models\Setting;
@endphp
@section('title', 'Homepage Buttons & Captions')

@section('content')
  <div class="pagetitle">
    <h1>Homepage Buttons & Captions</h1>
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
            <h5 class="card-title">Section Buttons</h5>
            <p class="text-muted">Edit the text shown on every homepage section button. Use <code>{count}</code> on the tours button to keep the live safari count.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.homepage-content') }}">
              @csrf

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Destinations Button</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_destinations]" class="form-control" value="{{ Setting::get('home_btn_destinations', 'View All Destinations') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Tours Button</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_tours]" class="form-control" value="{{ Setting::get('home_btn_tours', 'Explore All {count} Safari Tours') }}">
                  <small class="text-muted"><code>{count}</code> is replaced with the real tour count (e.g. "Explore All 5 Safari Tours").</small>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Countries Button (open)</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_countries_more]" class="form-control" value="{{ Setting::get('home_btn_countries_more', 'View All Countries') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Countries Button (collapse)</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_countries_less]" class="form-control" value="{{ Setting::get('home_btn_countries_less', 'Show Fewer Countries') }}">
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Accommodations Button</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_accommodations]" class="form-control" value="{{ Setting::get('home_btn_accommodations', 'View All Accommodations') }}">
                </div>
              </div>

              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Blog Button</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_btn_blog]" class="form-control" value="{{ Setting::get('home_btn_blog', 'All Blog Posts') }}">
                </div>
              </div>

              <hr>

              <h5 class="card-title mt-4">Section Captions</h5>
              <p class="text-muted">Edit the titles and captions shown above each homepage section.</p>

              <h6 class="mt-4 mb-3 text-muted">Destinations Section</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_destinations_title]" class="form-control" value="{{ Setting::get('home_sec_destinations_title', 'Explore Top Destinations.') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_destinations_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_destinations_subtitle', 'Diverse landscapes, iconic wildlife, and cultures that stay with you long after you return home.') }}</textarea>
                </div>
              </div>

              <h6 class="mt-4 mb-3 text-muted">Tours Section</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_tours_title]" class="form-control" value="{{ Setting::get('home_sec_tours_title', 'Featured Tours Around East Africa.') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_tours_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_tours_subtitle', 'Handpicked itineraries across Tanzania, Kenya, Uganda, and Rwanda — designed by locals who know every trail and watering hole.') }}</textarea>
                </div>
              </div>

              <h6 class="mt-4 mb-3 text-muted">Countries Section (Where We Go)</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Eyebrow</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_countries_eyebrow]" class="form-control" value="{{ Setting::get('home_sec_countries_eyebrow', 'Worldwide Group Travellers') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_countries_title]" class="form-control" value="{{ Setting::get('home_sec_countries_title', 'Kilimanjaro Group Tours for Adventurers from Every Country') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_countries_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_countries_subtitle', 'Our group tours bring together climbers and travellers from around the world. Choose your country to explore relevant travel information and start planning your Kilimanjaro adventure.') }}</textarea>
                </div>
              </div>

              <h6 class="mt-4 mb-3 text-muted">Accommodations Section</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Eyebrow</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_accommodations_eyebrow]" class="form-control" value="{{ Setting::get('home_sec_accommodations_eyebrow', "Where You'll Stay") }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_accommodations_title]" class="form-control" value="{{ Setting::get('home_sec_accommodations_title', 'Relaxing Accommodations.') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_accommodations_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_accommodations_subtitle', 'Boutique lodges, tented camps, and beach resorts — each stay handpicked for comfort, character, and a front-row seat to the wild.') }}</textarea>
                </div>
              </div>

              <h6 class="mt-4 mb-3 text-muted">Latest Blog Section</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_blog_title]" class="form-control" value="{{ Setting::get('home_sec_blog_title', 'Latest Blog Posts.') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_blog_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_blog_subtitle', 'Stories, tips, and field notes from the road — everything you need before your next African adventure.') }}</textarea>
                </div>
              </div>

              <h6 class="mt-4 mb-3 text-muted">Safari Cinema Wall (The Afro Gallery)</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Eyebrow</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_gallery_eyebrow]" class="form-control" value="{{ Setting::get('home_sec_gallery_eyebrow', 'The Untamed Archive') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[home_sec_gallery_title]" class="form-control" value="{{ Setting::get('home_sec_gallery_title', 'The Afro Gallery') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Caption</label>
                <div class="col-sm-9">
                  <textarea name="settings[home_sec_gallery_subtitle]" class="form-control" rows="2">{{ Setting::get('home_sec_gallery_subtitle', 'Raw beauty, untold stories, and landscapes that demand to be explored — curated from our travels across the continent.') }}</textarea>
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