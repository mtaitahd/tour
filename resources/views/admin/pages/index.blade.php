@extends('admin.layouts.app')
@section('title', 'Pages')

@section('content')
  <div class="pagetitle">
    <h1>Pages</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">CMS</li>
        <li class="breadcrumb-item active">Pages</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show my-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
              <h5 class="card-title mb-0">All Pages</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pageFormModal" onclick="openPageForm('{{ route('admin.pages.create') }}')">
                <i class="bi bi-plus-circle"></i> Add New Page
              </button>
            </div>

            <table class="table datatable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Title</th>
                  <th>Slug</th>
                  <th>Status</th>
                  <th>Order</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($pages as $page)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $page->title }}</td>
                    <td>{{ $page->slug }}</td>
                    <td>
                      @if($page->status === 'published')
                        <span class="badge bg-success">Published</span>
                      @else
                        <span class="badge bg-secondary">Draft</span>
                      @endif
                    </td>
                    <td>{{ $page->order }}</td>
                    <td>{{ $page->created_at->format('d M Y') }}</td>
                    <td>
                      <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="btn btn-info btn-sm" title="View on site">
                        <i class="bi bi-eye"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
                              data-bs-toggle="modal" data-bs-target="#pageFormModal"
                              onclick="openPageForm('{{ route('admin.pages.edit', $page->id) }}')">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('DELETE')

                          <!-- Trigger modal button -->
                          <button type="button" class="btn btn-sm btn-outline-danger" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#deleteModal{{ $page->id }}"
                                  title="Delete">
                              <i class="fas fa-trash"></i>
                          </button>

                          <!-- Modal for this specific page -->
                          <div class="modal fade" id="deleteModal{{ $page->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $page->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                  <div class="modal-content">
                                      <div class="modal-header bg-danger text-white">
                                          <h5 class="modal-title" id="deleteModalLabel{{ $page->id }}">Confirm Deletion</h5>
                                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          Are you sure you want to delete the page:  
                                          <strong>"{{ $page->title }}"</strong>?<br>
                                          <small class="text-muted">This action cannot be undone.</small>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                          <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">No pages found. Create your first one!</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Add / Edit Page Modal -->
  <div class="modal fade" id="pageFormModal" tabindex="-1" aria-labelledby="pageFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="pageFormModalLabel">Page</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="pageFormIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function openPageForm(url) {
      var frame = document.getElementById('pageFormIframe');
      document.getElementById('pageFormModalLabel').textContent = 'Add Page';
      if (typeof url === 'string' && url.indexOf('/edit') !== -1) {
        document.getElementById('pageFormModalLabel').textContent = 'Edit Page';
      }
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      frame.src = url + sep + 'modal=1';
    }

    (function () {
      var modalEl = document.getElementById('pageFormModal');
      var frame = document.getElementById('pageFormIframe');
      var wasForm = false;

      modalEl.addEventListener('hidden.bs.modal', function () {
        wasForm = false;
        frame.src = 'about:blank';
      });

      frame.addEventListener('load', function () {
        try {
          var path = frame.contentWindow.location.pathname;
          var basePath = new URL("{{ route('admin.pages.index') }}", window.location.origin).pathname;
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