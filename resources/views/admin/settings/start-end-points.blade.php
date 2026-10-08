@extends('admin.layouts.app')
@php
    use App\Models\Setting;
    $startPoints = Setting::json('starting_points', []);
    $endPoints   = Setting::json('ending_points', []);
@endphp
@section('title', 'Start & End Points')

@section('content')
  <div class="pagetitle">
    <h1>Start &amp; End Points</h1>
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
        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.start-end-points') }}">
          @csrf

          <div class="row">
            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="card-title">Starting Points</h5>
                  <p class="text-muted">These starting points populate the "Starting Point" filter on the public tour listing.</p>

                  <div class="points-repeater" data-field="starting_points" data-next-index="{{ count($startPoints) }}">
                    @foreach ($startPoints as $i => $point)
                      <div class="input-group mb-2 point-row">
                        <input type="text" name="starting_points[{{ $i }}]" class="form-control" value="{{ $point }}" placeholder="e.g. Nairobi, Kilimanjaro Airport">
                        <button type="button" class="btn btn-outline-danger remove-point"><i class="bi bi-trash"></i></button>
                      </div>
                    @endforeach
                  </div>

                  <button type="button" class="btn btn-outline-primary btn-sm add-point" data-field="starting_points">
                    <i class="bi bi-plus-circle"></i> Add Starting Point
                  </button>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="card-title">Ending Points</h5>
                  <p class="text-muted">These ending points are shown as the trip end location on tour pages.</p>

                  <div class="points-repeater" data-field="ending_points" data-next-index="{{ count($endPoints) }}">
                    @foreach ($endPoints as $i => $point)
                      <div class="input-group mb-2 point-row">
                        <input type="text" name="ending_points[{{ $i }}]" class="form-control" value="{{ $point }}" placeholder="e.g. Arusha, Tanzania">
                        <button type="button" class="btn btn-outline-danger remove-point"><i class="bi bi-trash"></i></button>
                      </div>
                    @endforeach
                  </div>

                  <button type="button" class="btn btn-outline-primary btn-sm add-point" data-field="ending_points">
                    <i class="bi bi-plus-circle"></i> Add Ending Point
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-sm-9">
              <button type="submit" class="btn btn-primary">Save Start &amp; End Points</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script>
    document.querySelectorAll('.add-point').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var field = btn.dataset.field;
        var repeater = document.querySelector('.points-repeater[data-field="' + field + '"]');
        var index = parseInt(repeater.dataset.nextIndex, 10);
        repeater.dataset.nextIndex = index + 1;

        var row = document.createElement('div');
        row.className = 'input-group mb-2 point-row';
        row.innerHTML =
          '<input type="text" name="' + field + '[' + index + ']" class="form-control" placeholder="' +
          (field === 'ending_points' ? 'e.g. Arusha, Tanzania' : 'e.g. Nairobi, Kilimanjaro Airport') + '">' +
          '<button type="button" class="btn btn-outline-danger remove-point"><i class="bi bi-trash"></i></button>';
        repeater.appendChild(row);
      });
    });

    document.addEventListener('click', function (e) {
      if (e.target.closest('.remove-point')) {
        e.target.closest('.point-row').remove();
      }
    });
  </script>
@endsection
