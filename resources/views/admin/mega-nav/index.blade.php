@extends('admin.layouts.app')
@section('title', 'Mega Nav')

@section('content')
  <div class="pagetitle">
    <h1>Mega Nav</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Mega Nav</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
              <h5 class="card-title">All Mega Nav Items</h5>
              <a href="{{ route('admin.mega-nav.create') }}" class="btn btn-primary btn-sm my-3">
                <i class="bi bi-plus-circle"></i> Add New Item
              </a>
            </div>

            <div class="alert alert-light border small">
              <i class="bi bi-info-circle me-1"></i>
              Each item fills one row of the navigation's three-column mega menu:
              the <strong>title</strong> in the left panel, the <strong>heading</strong>
              + <strong>short description</strong> in the middle column, and the
              <strong>image</strong> in the right column. The item appears under the
              selected parent menu (Kilimanjaro / Safari / Day Trips) exactly as it
              renders on the homepage.
            </div>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Menu</th>
                  <th>Left Panel Title</th>
                  <th>Middle Heading</th>
                  <th>Image</th>
                  <th>Order</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($items as $item)
                  <tr>
                    <td>
                      <span class="badge bg-secondary">{{ $parents[$item->parent_menu_key]['label'] ?? $item->parent_menu_key }}</span>
                    </td>
                    <td>{{ $item->menu_label }}</td>
                    <td>{{ $item->heading ?: $item->menu_label }}</td>
                    <td>
                      @if ($item->image)
                        <img src="{{ $item->image->getUrl('thumb-webp') ?: $item->image->getUrl() }}"
                             alt="" class="img-thumbnail" style="width: 70px; height: 50px; object-fit: cover;">
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>{{ $item->display_order }}</td>
                    <td>
                      @if ($item->is_active)
                        <span class="badge bg-success">Active</span>
                      @else
                        <span class="badge bg-secondary">Hidden</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('admin.mega-nav.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i>
                      </a>
                      <form action="{{ route('admin.mega-nav.destroy', $item) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Remove this Mega Nav item ({{ addslashes($item->menu_label) }})? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-center py-4">No Mega Nav items yet. Add your first one to populate the navigation menu.</td></tr>
                @endforelse
              </tbody>
            </table>

            {{ $items->links() }}
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection