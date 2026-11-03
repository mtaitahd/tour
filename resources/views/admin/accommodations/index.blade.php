@extends('admin.layouts.app')
@section('title', 'Accommodations')
@section('content')
  <div class="pagetitle">
    <h1>Accommodations</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/</a></li>
        <li class="breadcrumb-item active">Accommodations</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Accommodations</h5>
          <a href="{{ route('admin.accommodations.create') }}" class="btn btn-primary btn-sm my-3">
            <i class="bi bi-plus-circle"></i> Add New Accommodation
          </a>
        </div>

        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <form method="GET" action="{{ route('admin.accommodations.index') }}" id="accommodation-search" class="mb-3">
          <label for="accommodation-search-name" class="form-label">Search accommodations</label>
          <div class="input-group">
            <input type="search" class="form-control" id="accommodation-search-name" name="search" value="{{ $search }}" placeholder="Search by name">
            <button type="submit" class="btn btn-outline-primary">Search</button>
          </div>
        </form>
        <div id="accommodation-results">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Tier</th>
              <th>Destination</th>
              <th>Status</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($accommodations as $accommodation)
              <tr>
                <td>{{ $accommodation->name }}</td>
                <td>{{ $accommodation->tier ?? '-' }}</td>
                <td>{{ $accommodation->destination?->name ?? $accommodation->location ?? '-' }}</td>
                <td>
                  @if($accommodation->status === 'published')
                    <span class="badge bg-success">Published</span>
                  @elseif($accommodation->status === 'archived')
                    <span class="badge bg-secondary">Archived</span>
                  @else
                    <span class="badge bg-warning text-dark">Draft</span>
                  @endif
                </td>
                <td>{{ $accommodation->is_featured ? 'Yes' : 'No' }}</td>
                <td>{{ $accommodation->order }}</td>
                <td>
                  <a href="{{ route('admin.accommodations.edit', $accommodation) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('admin.accommodations.destroy', $accommodation) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete {{ addslashes($accommodation->name) }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-4">No accommodations yet.</td></tr>
            @endforelse
          </tbody>
        </table>

        {{ $accommodations->links('pagination::bootstrap-5') }}
        </div>
        <script>
          (() => {
            const form = document.getElementById('accommodation-search');
            const field = document.getElementById('accommodation-search-name');
            const results = document.getElementById('accommodation-results');
            let timer;
            let request;

            field.addEventListener('input', () => {
              clearTimeout(timer);
              request?.abort();
              timer = setTimeout(async () => {
                request = new AbortController();
                const { signal } = request;
                const url = new URL(form.action);
                if (field.value.trim()) url.searchParams.set('search', field.value.trim());

                try {
                  const response = await fetch(url, { signal });
                  if (!response.ok) return;
                  const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                  const filtered = page.getElementById(results.id);
                  if (!filtered || signal.aborted) return;
                  results.replaceChildren(...filtered.childNodes);
                  history.replaceState(history.state, '', url);
                } catch (error) {
                  if (!signal.aborted) console.error(error);
                }
              }, 250);
            });
          })();
        </script>
      </div>
    </div>
  </section>
@endsection
