@extends('frontend.layouts.app')

@section('title', 'Group Departures & Fixed-Date Tours | Afro-Vertex')

@section('page-content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <h1 class="breadcrumb-title mb-2">Group Departures & Fixed-Date Tours 2026–2027</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                    <li class="breadcrumb-item active">Group Departures</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <!-- Filters -->
            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <form method="GET" action="{{ route('group-departures.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Month / Year</label>
                            <input type="month" name="month" class="form-control" 
                                   value="{{ request('month') ?? now()->format('Y-m') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tour</label>
                            <select name="tour" class="form-select">
                                <option value="">All Tours</option>
                                @foreach($tours as $tourItem)
                                    <option value="{{ $tourItem->id }}" {{ request('tour') == $tourItem->id ? 'selected' : '' }}>
                                        {{ $tourItem->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 mt-4">
                                Filter Departures
                            </button>
                        </div>

                        <div class="col-md-2 text-end mt-4">
                            <a href="{{ route('group-departures.index') }}" class="btn btn-outline-secondary w-100">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grid View -->
            @if($departures->count() > 0)
                <div class="row g-4">
                    @foreach($departures as $dep)
                        <div class="col-lg-4 col-md-6">
                            <div class="card shadow-sm h-100 border-0 departure-card hover-lift">
                                <div class="card-body d-flex flex-column p-4">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="mb-0 fw-bold fs-20">
                                            {{ $dep->tour?->title ?? 'Tour not found' }}
                                        </h5>
                                        @if($dep->is_featured)
                                            <span class="badge bg-success">Featured</span>
                                        @endif
                                    </div>

                                    <!-- Dates -->
                                    <p class="mb-2 text-primary fw-medium">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $dep->departure_date->format('d M Y') }}
                                        @if($dep->return_date)
                                            – {{ $dep->return_date->format('d M Y') }}
                                        @endif
                                    </p>

                                    <!-- Spots -->
                                    <p class="mb-2">
                                        <strong>Spots:</strong> 
                                        @if($dep->isSoldOut())
                                            <span class="badge bg-danger">Sold Out</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $dep->spotsLeft() }} / {{ $dep->total_spots }}</span>
                                        @endif
                                    </p>

                                    <!-- Price -->
                                    @if($dep->group_price)
                                        <p class="mb-3 fs-18 fw-bold text-primary">
                                            From ${{ number_format($dep->group_price, 0) }} {{ $dep->currency }}
                                        </p>
                                    @else
                                        <p class="mb-3 text-muted">Price as per tour package</p>
                                    @endif

                                    <!-- Status Badges -->
                                    @if($dep->status == 'guaranteed')
                                        <span class="badge bg-success mb-3">Guaranteed Departure</span>
                                    @elseif($dep->status == 'limited')
                                        <span class="badge bg-secondary mb-3">Limited Seats</span>
                                    @endif

                                    <!-- Action Button -->
                                    <div class="mt-auto d-grid">
                                        <a href="{{ route('tour.show', $dep->tour->slug) }}" 
                                           class="btn btn-outline-primary">
                                            View Tour Details →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-5">
                    {{ $departures->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <h4 class="mb-3">No upcoming group departures</h4>
                    <p class="text-muted mb-4">
                        Check back soon or browse our regular tours below.
                    </p>
                    <a href="{{ route('tours.index') }}" class="btn btn-primary">
                        View All Tours
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('extra-styles')
    <style>
        .departure-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .departure-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem !important;
        }
    </style>
@endsection