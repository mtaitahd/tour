@extends('admin.layouts.app')
@section('title', 'Inquiries')

@section('content')
  <div class="pagetitle">
    <h1>Inquiries & Booking Requests</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Inquiries</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Inquiries ({{ $inquiries->total() }})</h5>
              <div>
                <!-- Status filters -->
                <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-secondary btn-sm {{ !request('status') ? 'active' : '' }}">All</a>
                <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm {{ request('status') == 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('admin.inquiries.index', ['status' => 'contacted']) }}" class="btn btn-info btn-sm {{ request('status') == 'contacted' ? 'active' : '' }}">Contacted</a>
                <a href="{{ route('admin.inquiries.index', ['status' => 'confirmed']) }}" class="btn btn-success btn-sm {{ request('status') == 'confirmed' ? 'active' : '' }}">Confirmed</a>
                <a href="{{ route('admin.inquiries.index', ['status' => 'cancelled']) }}" class="btn btn-danger btn-sm {{ request('status') == 'cancelled' ? 'active' : '' }}">Cancelled</a>
              </div>
            </div>

            <!-- Type filters -->
            <div class="mb-3">
                <a href="{{ route('admin.inquiries.index', request()->only('status')) }}" class="btn btn-outline-dark btn-sm {{ !request('type') ? 'active' : '' }}">All Types</a>
                <a href="{{ route('admin.inquiries.index', array_merge(request()->only('status'), ['type' => 'tour_booking'])) }}" class="btn btn-outline-dark btn-sm {{ request('type') == 'tour_booking' ? 'active' : '' }}">Tour Bookings</a>
                <a href="{{ route('admin.inquiries.index', array_merge(request()->only('status'), ['type' => 'contact'])) }}" class="btn btn-outline-dark btn-sm {{ request('type') == 'contact' ? 'active' : '' }}">General Contact</a>
            </div>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <table class="table table-striped datatable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Tour</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($inquiries as $inquiry)
                  <tr>
                    <td>{{ $inquiry->id }}</td>
                    <td>
                      @if($inquiry->isTourBooking())
                        <span class="badge bg-primary">Tour Booking</span>
                      @else
                        <span class="badge bg-secondary">Contact</span>
                      @endif
                    </td>
                    <td>{{ $inquiry->name }}</td>
                    <td>{{ $inquiry->email }}</td>
                    <td>{{ $inquiry->phone }}</td>
                    <td>
                      @if($inquiry->tour)
                        <a href="{{ route('tour.show', $inquiry->tour->slug) }}" target="_blank">
                          {{ Str::limit($inquiry->tour->title, 40) }}
                        </a>
                      @else
                        <span class="text-muted">General Inquiry</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-{{ 
                        $inquiry->status == 'pending' ? 'warning' : 
                        ($inquiry->status == 'contacted' ? 'info' : 
                        ($inquiry->status == 'confirmed' ? 'success' : 'danger'))
                      }}">
                        {{ ucfirst($inquiry->status) }}
                      </span>
                    </td>
                    <td>{{ $inquiry->created_at->format('d M Y H:i') }}</td>
                    <td>
                      <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> View
                      </a>
                      <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Delete this inquiry from {{ addslashes($inquiry->name) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center py-5">No inquiries yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>

            {{ $inquiries->links() }}
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection 