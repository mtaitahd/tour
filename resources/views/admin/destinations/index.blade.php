@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Destinations</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/</a></li>
        <li class="breadcrumb-item active">Destinations</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Destinations</h5>
          <button type="button" class="btn btn-primary btn-sm my-3" data-bs-toggle="modal" data-bs-target="#destinationFormModal" onclick="openDestinationForm('{{ route('admin.destinations.create') }}')">
            <i class="bi bi-plus-circle"></i> Add New Destination
          </button>
        </div>

        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Country</th>
              <th>Type</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($destinations as $destination)
              <tr>
                <td>{{ $destination->name }}</td>
                <td>{{ $destination->country_code }}</td>
                <td>{{ ucfirst($destination->type ?? '-') }}</td>
                <td>{{ $destination->is_featured ? 'Yes' : 'No' }}</td>
                <td>{{ $destination->order }}</td>
                <td>
                  <a href="{{ route('destination.show', $destination->slug) }}" class="btn btn btn-sm btn-outline-success" target="_blank"><i class="fas fa-eye"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#destinationFormModal" onclick="openDestinationForm('{{ route('admin.destinations.edit', $destination) }}')"><i class="fas fa-edit"></i></button>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4">No destinations yet.</td></tr>
            @endforelse
          </tbody>
        </table>

        {{ $destinations->links() }}
      </div>
    </div>
  </section>

  <!-- Add / Edit Destination Modal -->
  <div class="modal fade" id="destinationFormModal" tabindex="-1" aria-labelledby="destinationFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="destinationFormModalLabel">Destination</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="destinationFormIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function openDestinationForm(url) {
      var frame = document.getElementById('destinationFormIframe');
      document.getElementById('destinationFormModalLabel').textContent = 'Add Destination';
      if (typeof url === 'string' && url.indexOf('/edit') !== -1) {
        document.getElementById('destinationFormModalLabel').textContent = 'Edit Destination';
      }
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      frame.src = url + sep + 'modal=1';
    }

    (function () {
      var modalEl = document.getElementById('destinationFormModal');
      var frame = document.getElementById('destinationFormIframe');
      var wasForm = false;

      modalEl.addEventListener('hidden.bs.modal', function () {
        wasForm = false;
        frame.src = 'about:blank';
      });

      frame.addEventListener('load', function () {
        try {
          var path = frame.contentWindow.location.pathname;
          var basePath = new URL("{{ route('admin.destinations.index') }}", window.location.origin).pathname;
          if (path === basePath && wasForm) {
            wasForm = false;
            bootstrap.Modal.getInstance(modalEl).hide();
            window.location.reload();
            return;
          }
          if (path !== basePath && path.indexOf(basePath + '/') === 0) {
            wasForm = true;
          } else {
            wasForm = false;
          }
        } catch (e) {
          wasForm = false;
        }
      });
    })();
  </script>
@endpush