@extends('admin.layouts.app')
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

        {{ $accommodations->links() }}
      </div>
    </div>
  </section>
@endsection
