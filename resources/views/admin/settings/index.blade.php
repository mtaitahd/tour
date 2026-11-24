@extends('admin.layouts.app')
@php
    use App\Models\Setting;
@endphp
@section('title', 'Site Settings')

@section('content')
  <div class="pagetitle">
    <h1>Site Settings</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Global Settings</h5>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
              @csrf

              <!-- Tabs for better organization -->
              <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Header & Footer</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">Social Links</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">SEO & Advanced</button>
                  </li>
              </ul>

              <div class="tab-content" id="settingsTabContent">
                  <!-- General -->
                  <div class="tab-pane fade show active" id="general" role="tabpanel">
                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Site Name</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[site_name]" class="form-control" value="{{ Setting::get('site_name') }}">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Site Logo</label>
                          <div class="col-sm-9">
                              <x-media-picker
                                  name="settings[logo_image_id]"
                                  :selected="Setting::get('logo_image_id')"
                                  label="Select from Media Library"
                              />
                              @if(!Setting::get('logo_image_id') && Setting::hasLogo())
                                  {{-- Legacy direct-upload logo, shown until a new one is picked --}}
                                  <div class="mb-3">
                                      <img src="{{ Setting::logoUrl() }}" alt="Current Logo"
                                           style="max-height: 100px; max-width: 300px; object-fit: contain;">
                                      <small class="d-block text-muted mt-1">Uploaded the old way — pick a new one from the Media Library to switch over.</small>
                                  </div>
                              @endif
                              <small class="text-muted d-block mt-1">Recommended: transparent PNG or SVG</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Default Currency</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[default_currency]" class="form-control" value="{{ Setting::get('default_currency', 'USD') }}">
                              <small>e.g. USD, EUR, TZS</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Timezone</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[timezone]" class="form-control" value="{{ Setting::get('timezone', 'Africa/Dar_es_Salaam') }}">
                              <small>e.g. Africa/Dar_es_Salaam, UTC, Europe/London</small>
                          </div>
                      </div>
                  </div>

                  <!-- Contact & Footer -->
                  <div class="tab-pane fade" id="contact" role="tabpanel">
                      <h5 class="mb-3">Contact Details</h5>
                      <p class="text-muted">Used across the site header, footer, and Contact page.</p>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Phone Number</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[footer_phone]" class="form-control" value="{{ Setting::get('footer_phone') }}"
                                     placeholder="+255 760 096 715">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">WhatsApp Number</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[whatsapp_number]" class="form-control" value="{{ Setting::get('whatsapp_number') }}"
                                     placeholder="255760096715">
                              <small class="text-muted">Digits only, with country code, no "+" or spaces — used to build the wa.me chat link.</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Email Address</label>
                          <div class="col-sm-9">
                              <input type="email" name="settings[site_email]" class="form-control" value="{{ Setting::get('site_email') }}">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">WeChat ID (optional)</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[wechat_id]" class="form-control" value="{{ Setting::get('wechat_id') }}">
                              <small class="text-muted">Leave blank to hide from the header entirely.</small>
                          </div>
                      </div>

                      <div class="row mb-4">
                          <label class="col-sm-3 col-form-label">LINE ID (optional)</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[line_id]" class="form-control" value="{{ Setting::get('line_id') }}">
                              <small class="text-muted">Leave blank to hide from the header entirely.</small>
                          </div>
                      </div>

                      <hr class="my-4">
                      <a href="{{ route('admin.landing.edit', 'footer') }}" class="btn btn-outline-primary">Edit Footer in Landing Page</a>
                  </div>

                  <div class="tab-pane fade" id="social" role="tabpanel">
                    <a href="{{ route('admin.landing.edit', 'footer') }}" class="btn btn-outline-primary">Edit Social Links in Landing Page</a>
                  </div>

                  <!-- SEO & Advanced -->
                  <div class="tab-pane fade" id="seo" role="tabpanel">
                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Google Analytics ID</label>
                          <div class="col-sm-9">
                              <input type="text" name="settings[ga_id]" class="form-control" value="{{ Setting::get('ga_id') }}">
                              <small>e.g. G-XXXXXXXXXX or UA-XXXXXXXXX-X</small>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">robots.txt</label>
                          <div class="col-sm-9">
                              <div class="alert alert-info mb-0 py-2">
                                  <i class="bi bi-info-circle me-1"></i>
                                  <strong>Auto-generated</strong> — robots.txt is served automatically by <code>robots.php</code> with the correct Disallow rules and Sitemap directive. No manual editing needed.
                              </div>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label class="col-sm-3 col-form-label">Enable Sitemap.xml</label>
                          <div class="col-sm-9">
                              <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="settings[enable_sitemap]" value="1" 
                                         {{ Setting::get('enable_sitemap') ? 'checked' : '' }}>
                                  <label class="form-check-label">Generate basic sitemap.xml</label>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Submit -->
              <div class="row mt-5">
                  <div class="col-12 text-end">
                      <button type="submit" class="btn btn-primary btn-lg px-5">
                          <i class="bi bi-save me-2"></i> Save All Settings
                      </button>
                  </div>
              </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection