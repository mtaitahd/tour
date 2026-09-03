@extends('admin.layouts.app')
@section('title', 'Inquiry Details')

@section('content')
  <div class="pagetitle">
    <h1>
      Inquiry from {{ $inquiry->name }}
      @if($inquiry->isTourBooking())
        <span class="badge bg-primary fs-14 align-middle">Tour Booking</span>
      @else
        <span class="badge bg-secondary fs-14 align-middle">Contact</span>
      @endif
    </h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}">Home/</a></li>
        <li><a href="{{ route('admin.inquiries.index') }}">Inquiries/</a></li>
        <li class="breadcrumb-item active">Details</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Inquiry Information</h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Name</div>
              <div class="col-sm-8">{{ $inquiry->name }}</div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Email</div>
              <div class="col-sm-8"><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Phone / WhatsApp</div>
              <div class="col-sm-8">{{ $inquiry->phone }}</div>
            </div>

            @if($inquiry->country)
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Country</div>
                <div class="col-sm-8">{{ $inquiry->country }}</div>
              </div>
            @endif

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Tour</div>
              <div class="col-sm-8">
                @if($inquiry->tour)
                  <a href="{{ route('tour.show', $inquiry->tour->slug) }}" target="_blank">
                    {{ $inquiry->tour->title }}
                  </a>
                @else
                  General Inquiry
                @endif
              </div>
            </div>

            @if($inquiry->isTourBooking())
              <hr>
              <h6 class="text-muted mb-3">Trip Preferences</h6>

              @if($inquiry->companions)
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Travelling As</div>
                  <div class="col-sm-8">{{ $inquiry->companions }}</div>
                </div>
              @endif

              @if($inquiry->accommodation)
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Accommodation Preference</div>
                  <div class="col-sm-8">{{ $inquiry->accommodation }}</div>
                </div>
              @endif

              @if($inquiry->room_type || $inquiry->bed_type)
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Room / Bed Type</div>
                  <div class="col-sm-8">{{ $inquiry->room_type ?? '-' }} / {{ $inquiry->bed_type ?? '-' }}</div>
                </div>
              @endif

              @if($inquiry->budget_min || $inquiry->budget_max)
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Budget Range (per person)</div>
                  <div class="col-sm-8">
                    {{ $inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-' }}
                    to
                    {{ $inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-' }}
                  </div>
                </div>
              @endif

              @if($inquiry->adult_age_range || $inquiry->children_age_range)
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Age Ranges</div>
                  <div class="col-sm-8">
                    Adults: {{ $inquiry->adult_age_range ?? '-' }} | Children: {{ $inquiry->children_age_range ?? 'None' }}
                  </div>
                </div>
              @endif
              <hr>
            @endif

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Preferred Dates</div>
              <div class="col-sm-8">
                {{ $inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : '-' }}
                to
                {{ $inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : '-' }}
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Group Size</div>
              <div class="col-sm-8">{{ $inquiry->adults }} Adults, {{ $inquiry->children }} Children</div>
            </div>

            @if($inquiry->total_amount)
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Total Amount</div>
                <div class="col-sm-8">${{ number_format($inquiry->total_amount, 2) }}</div>
              </div>
            @endif

            @if($inquiry->quote_snapshot)
              @php $snap = $inquiry->quote_snapshot; @endphp
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Price Quote</div>
                <div class="col-sm-8">
                  @if(($snap['request_type'] ?? '') === 'automatic')
                    ${{ number_format((float) $snap['price_pp'], 2) }} per person
                    ({{ $snap['level_name'] ?? ucfirst(strtolower($snap['season'] ?? '')) }},
                    {{ ucfirst(strtolower($snap['season_label'] ?? $snap['season'] ?? '')) }})
                    &middot; group total: ${{ number_format((float) $snap['group_total'], 2) }}
                  @elseif(($snap['request_type'] ?? '') === 'custom')
                    {{ $snap['note'] ?? 'Custom price requested.' }}
                  @else
                    Package price requested — no configured price for this tour.
                  @endif
                  <div class="small text-muted mt-1">
                    Server-generated quote for {{ $snap['group_size'] ?? '-' }} traveler(s)
                    ({{ $snap['season_label'] ?? 'High Season' }}). Currency: {{ $snap['currency'] ?? 'USD' }}.
                  </div>
                </div>
              </div>
            @endif

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Message</div>
              <div class="col-sm-8">
                <div class="p-3 bg-light rounded">
                  {!! nl2br(e($inquiry->message)) !!}
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Submitted</div>
              <div class="col-sm-8">{{ $inquiry->created_at->diffForHumans() }} ({{ $inquiry->created_at->format('d M Y H:i') }})</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column - Status & Notes -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Status</h5>

            <form action="{{ route('admin.inquiries.update', $inquiry) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="pending" {{ $inquiry->status == 'pending' ? 'selected' : '' }}>Pending</option>
                  <option value="contacted" {{ $inquiry->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                  <option value="confirmed" {{ $inquiry->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                  <option value="cancelled" {{ $inquiry->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Admin Notes</label>
                <textarea name="admin_notes" class="form-control" rows="5">{{ old('admin_notes', $inquiry->admin_notes) }}</textarea>
              </div>

              <button type="submit" class="btn btn-primary">Update</button>
            </form>

            <hr>

            <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="POST"
                  onsubmit="return confirm('Delete this inquiry? This cannot be undone.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger w-100">
                <i class="bi bi-trash"></i> Delete Inquiry
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection