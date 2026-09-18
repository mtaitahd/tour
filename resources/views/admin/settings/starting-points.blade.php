@extends('admin.layouts.app')
@php
    use App\Models\Setting;
    $points = Setting::json('starting_points', []);
@endphp
@section('title', 'Tour Starting Points')

@section('content')
  <div class="pagetitle">
    <h1>Tour Starting Points</h1>
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
            <h5 class="card-title">Starting Points</h5>
            <p class="text-muted">These starting points populate the "Starting Point" filter on the public tour listing.</p>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.starting-points') }}">
              @csrf

              <div class="starting-points-repeater" data-next-index="{{ count($points) }}">
                @foreach ($points as $i => $point)
                  <div class="input-group mb-2 starting-point-row">
                    <input type="text" name="starting_points[{{ $i }}]" class="form-control" value="{{ $point }}">
                    <button type="button" class="btn btn-outline-danger remove-starting-point"><i class="bi bi-trash"></i></button>
                  </div>
                @endforeach
              </div>

              <button type="button" class="btn btn-outline-primary btn-sm add-starting-point">
                <i class="bi bi-plus-circle"></i> Add Starting Point
              </button>

              <div class="row mt-4">
                <div class="col-sm-9">
                  <button type="submit" class="btn btn-primary">Save Starting Points</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.querySelector('.add-starting-point').addEventListener('click', function () {
      var repeater = document.querySelector('.starting-points-repeater');
      var index = parseInt(repeater.dataset.nextIndex, 10);
      repeater.dataset.nextIndex = index + 1;

      var row = document.createElement('div');
      row.className = 'input-group mb-2 starting-point-row';
      row.innerHTML =
        '<input type="text" name="starting_points[' + index + ']" class="form-control" placeholder="e.g. Nairobi, Kilimanjaro Airport">' +
        '<button type="button" class="btn btn-outline-danger remove-starting-point"><i class="bi bi-trash"></i></button>';
      repeater.appendChild(row);
    });

    document.addEventListener('click', function (e) {
      if (e.target.closest('.remove-starting-point')) {
        e.target.closest('.starting-point-row').remove();
      }
    });
  </script>
@endsection