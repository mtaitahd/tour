@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Activities</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/</a></li>
        <li class="breadcrumb-item active">Activities</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Activities</h5>
          <a href="{{ route('admin.activities.create') }}" class="btn btn-primary btn-sm my-3">
            <i class="bi bi-plus-circle"></i> Add New Activity
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
              <th>Tours</th>
              <th>Active</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($activities as $activity)
              <tr>
                <td>{{ $activity->name }}</td>
                <td>{{ $activity->tour_count }}</td>
                <td>{{ $activity->is_active ? 'Yes' : 'No' }}</td>
                <td>{{ $activity->is_featured ? 'Yes' : 'No' }}</td>
                <td>{{ $activity->order }}</td>
                <td>
                  <a href="{{ route('admin.activities.edit', $activity) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete {{ addslashes($activity->name) }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4">No activities yet.</td></tr>
            @endforelse
          </tbody>
        </table>

        {{ $activities->links() }}
      </div>
    </div>
  </section>
@endsection
