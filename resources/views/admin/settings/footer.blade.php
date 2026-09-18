@extends('admin.layouts.app')
@php
    use App\Models\Setting;

    $groups = [
        'park'    => ['label' => 'Safaris by Park',    'headingKey' => 'footer_heading_parks'],
        'country' => ['label' => 'Safaris by Country', 'headingKey' => 'footer_heading_countries'],
        'type'    => ['label' => 'Safaris by Type',    'headingKey' => 'footer_heading_types'],
        'general' => ['label' => 'General',            'headingKey' => 'footer_heading_general'],
    ];
@endphp
@section('title', 'Footer Settings')

@section('content')
  <div class="pagetitle">
    <h1>Footer Settings</h1>
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
            <h5 class="card-title">Footer Link Groups</h5>
            <p class="text-muted">Manage each footer column independently. The statistics column has been removed.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.footer-settings') }}">
              @csrf

              <h6 class="mt-3 mb-3 text-muted">About Column</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">About Heading</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[footer_about_heading]" class="form-control" value="{{ Setting::get('footer_about_heading') }}">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">About Text</label>
                <div class="col-sm-9">
                  <textarea name="settings[footer_about_text]" class="form-control" rows="3">{{ Setting::get('footer_about_text') }}</textarea>
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">About Link Text</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[footer_about_link_text]" class="form-control" value="{{ Setting::get('footer_about_link_text', 'More About Us') }}">
                </div>
              </div>

              @foreach ($groups as $key => $meta)
                @php
                    $links = Setting::json('footer_links_' . $key, []);
                @endphp
                <hr>
                <h6 class="mt-4 mb-3 text-muted">{{ $meta['label'] }}</h6>
                <div class="row mb-3">
                  <label class="col-sm-3 col-form-label">Column Heading</label>
                  <div class="col-sm-9">
                    <input type="text" name="settings[{{ $meta['headingKey'] }}]" class="form-control" value="{{ Setting::get($meta['headingKey'], $meta['label']) }}">
                  </div>
                </div>

                <div class="footer-links-repeater" data-group="{{ $key }}" data-next-index="{{ count($links) }}">
                  @foreach ($links as $i => $link)
                    <div class="row g-2 mb-2 footer-link-row">
                      <div class="col-md-5">
                        <input type="text" name="footer_groups[{{ $key }}][{{ $i }}][label]" class="form-control" placeholder="Label" value="{{ $link['label'] ?? '' }}">
                      </div>
                      <div class="col-md-6">
                        <input type="text" name="footer_groups[{{ $key }}][{{ $i }}][url]" class="form-control" placeholder="https://… or /path" value="{{ $link['url'] ?? '' }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-footer-link" title="Remove"><i class="bi bi-trash"></i></button>
                      </div>
                    </div>
                  @endforeach
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm add-footer-link" data-group="{{ $key }}">
                  <i class="bi bi-plus-circle"></i> Add Link
                </button>
              @endforeach

              <hr>
              <h6 class="mt-4 mb-3 text-muted">Copyright Bar</h6>
              <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Privacy Link Text</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[footer_copy_privacy]" class="form-control" value="{{ Setting::get('footer_copy_privacy', 'Privacy Policy') }}">
                </div>
              </div>
              <div class="row mb-4">
                <label class="col-sm-3 col-form-label">Terms Link Text</label>
                <div class="col-sm-9">
                  <input type="text" name="settings[footer_copy_terms]" class="form-control" value="{{ Setting::get('footer_copy_terms', 'Terms & Conditions') }}">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">Save Footer</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.querySelectorAll('.add-footer-link').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var group = btn.dataset.group;
        var repeater = document.querySelector('.footer-links-repeater[data-group="' + group + '"]');
        var index = parseInt(repeater.dataset.nextIndex, 10);
        repeater.dataset.nextIndex = index + 1;

        var row = document.createElement('div');
        row.className = 'row g-2 mb-2 footer-link-row';
        row.innerHTML =
          '<div class="col-md-5"><input type="text" name="footer_groups[' + group + '][' + index + '][label]" class="form-control" placeholder="Label"></div>' +
          '<div class="col-md-6"><input type="text" name="footer_groups[' + group + '][' + index + '][url]" class="form-control" placeholder="https://… or /path"></div>' +
          '<div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-footer-link" title="Remove"><i class="bi bi-trash"></i></button></div>';
        repeater.appendChild(row);
      });
    });

    document.addEventListener('click', function (e) {
      if (e.target.closest('.remove-footer-link')) {
        e.target.closest('.footer-link-row').remove();
      }
    });
  </script>
@endsection