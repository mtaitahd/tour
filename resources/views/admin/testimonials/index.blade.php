@extends('admin.layouts.app')
@section('content')
  <div class="pagetitle">
    <h1>Testimonials</h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/</a></li>
        <li class="breadcrumb-item active">Testimonials</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-4">
          <h5 class="card-title">All Testimonials</h5>
          <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm my-3">
            <i class="bi bi-plus-circle"></i> Add New Testimonial
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
              <th>Rating</th>
              <th>Context</th>
              <th>Status</th>
              <th>Featured</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($testimonials as $testimonial)
              <tr>
                <td>{{ $testimonial->name }}</td>
                <td>{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</td>
                <td>
                  @if($testimonial->tourPackage)
                    Tour: {{ Str::limit($testimonial->tourPackage->title, 30) }}
                  @elseif($testimonial->destination)
                    Destination: {{ $testimonial->destination->name }}
                  @else
                    <span class="text-muted">General (homepage)</span>
                  @endif
                </td>
                <td>
                  @if($testimonial->status === 'published')
                    <span class="badge bg-success">Published</span>
                  @elseif($testimonial->status === 'archived')
                    <span class="badge bg-secondary">Archived</span>
                  @else
                    <span class="badge bg-warning text-dark">Draft</span>
                  @endif
                </td>
                <td>{{ $testimonial->is_featured ? 'Yes' : 'No' }}</td>
                <td>{{ $testimonial->order }}</td>
                <td>
                  <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this testimonial from {{ addslashes($testimonial->name) }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center py-4">No testimonials yet.</td></tr>
            @endforelse
          </tbody>
        </table>

        {{ $testimonials->links() }}
      </div>
    </div>
  </section>
@endsection
