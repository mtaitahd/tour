@extends('admin.layouts.app')
@php
    // Grouped by screen so the two /pages slots sit together and the two /tours
    // slots sit together, instead of four unrelated cards in catalogue order.
    $byScreen = collect($rows)->groupBy('screen');

    $previewRoute = fn (string $key) => str_starts_with($key, 'pages_') ? 'pages.index' : 'tours.index';
@endphp
@section('title', 'Listing Titles')

@section('content')
  <div class="pagetitle">
    <h1>Listing Titles</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">Website Content</li>
        <li class="breadcrumb-item active">Listing Titles</li>
      </ol>
    </nav>
  </div>

  @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $flashKey => $alertClass)
    @if (session($flashKey))
      <div class="alert alert-{{ $alertClass }} alert-dismissible fade show" role="alert">
        {{ session($flashKey) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  @if ($errors->any())
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Listing Titles</h5>
      <p class="text-muted mb-0">
        These are the headings and intro paragraphs on the two public listing pages.
        Whatever is marked <strong>Default</strong> below is the text shipped with the
        site; saving a value switches that field to <strong>Custom</strong> and the
        public page starts using yours. Resetting a field throws your text away and
        puts the shipped default back.
      </p>
    </div>
  </div>

  @foreach ($byScreen as $screen => $screenRows)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ $screen }}</h5>
      </div>
      <div class="card-body">
        @foreach ($screenRows as $row)
          @php $field = 'value_' . $row['key']; @endphp

          <div class="border rounded p-3 mb-3 {{ $loop->last ? 'mb-0' : '' }}">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1">{{ $row['label'] }}</h6>
                <p class="text-muted small mb-0">{{ $row['description'] }}</p>
              </div>
              <span class="badge bg-{{ $row['is_override'] ? 'primary' : 'secondary' }}">
                {{ $row['is_override'] ? 'Custom' : 'Default' }}
              </span>
            </div>

            <div class="bg-light border rounded p-2 mb-3">
              <div class="small text-uppercase text-muted fw-bold mb-1">Currently showing</div>
              @if ($row['value'] === '')
                <div class="fst-italic">Nothing yet — the page uses its own built-in copy.</div>
              @else
                <div class="text-break">{{ $row['value'] }}</div>
              @endif
            </div>

            {{-- Three sibling elements, never nested: a <form> inside a <form>
                 is not valid HTML and browsers silently drop the inner one, so
                 the reset button has to sit outside the update form. The row is
                 a flex container to keep them looking like one control group. --}}
            <div class="d-flex flex-wrap align-items-start gap-2">
              <form method="POST" action="{{ route('admin.listing-titles.update') }}"
                    class="d-flex flex-wrap align-items-start gap-2 flex-grow-1">
                @csrf
                <input type="hidden" name="key" value="{{ $row['key'] }}">

                @if ($row['multiline'])
                  <textarea name="{{ $field }}" class="form-control flex-grow-1" rows="3"
                            maxlength="2000">{{ old($field, $row['value']) }}</textarea>
                @else
                  <input type="text" name="{{ $field }}" class="form-control flex-grow-1"
                         maxlength="160" value="{{ old($field, $row['value']) }}">
                @endif

                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-lg"></i>
                  {{ $row['is_override'] ? 'Update' : 'Save' }}
                </button>
              </form>

              @if ($row['is_override'])
                {{-- Reset to default. The confirmation quotes the value being
                     discarded, because for the two intro fields the custom copy
                     is the only copy there is. --}}
                <form method="POST" action="{{ route('admin.listing-titles.destroy', $row['key']) }}"
                      class="listing-titles-reset" data-current="{{ $row['value'] }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                  </button>
                </form>
              @endif

              <a href="{{ route($previewRoute($row['key'])) }}" target="_blank" rel="noopener"
                 class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-up-right"></i>
                View page
              </a>
            </div>

            <div class="form-text mt-2">
              @if ($row['multiline'])
                Up to 2000 characters. HTML is allowed but tags are stripped when the paragraph is displayed.
              @else
                Up to 160 characters. Also used as the page&rsquo;s browser tab title and breadcrumb.
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('.listing-titles-reset').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        // dataset.current holds admin-entered copy, so it is untrusted. The
        // Blade attribute escaped it once on the way into the attribute, but
        // that only protects the attribute — it is raw text once read back
        // out, and SweetAlert's `html` option parses it as markup. Escape it
        // again for an HTML context, or copy such as "a <b>b</b>" (or
        // "a <img src=x onerror=...>") would be injected into the dialog.
        var raw = form.dataset.current || '';
        var escaped = document.createElement('div');
        escaped.textContent = raw;
        var current = escaped.innerHTML;

        Swal.fire({
          icon: 'warning',
          title: 'Reset to the default?',
          html: 'The public page will go back to the shipped copy and this text will be deleted:<br><br><strong>' + current + '</strong>',
          showCancelButton: true,
          confirmButtonText: 'Reset it',
          cancelButtonText: 'Keep my text',
          confirmButtonColor: '#e3342f',
          reverseButtons: true,
          focusCancel: true
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  </script>
@endpush
