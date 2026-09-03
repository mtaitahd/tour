@extends('frontend.layouts.app')

@section('title', $tour->meta_title ?? $tour->title . ' | Afro-Vertex Tours & Safaris')
@section('extra-head')
    <meta name="description" content="{{ $tour->meta_description ?? Str::limit(strip_tags($tour->overview), 160) }}">
    <meta name="keywords" content="{{ $tour->meta_keywords ?? 'safari, kilimanjaro, serengeti, tanzania tour' }}">
    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="{{ $tour->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($tour->overview), 200) }}">
    @if($tour->hero_image)
        <meta property="og:image" content="{{ asset($tour->hero_image) }}">
    @endif
    <meta property="og:url" content="{{ request()->url() }}">
@endsection

@section('page-content')
    <!-- Hero / Banner -->
    @if($tour->hero_image)
        <div class="tour-hero mb-5">
            <img src="{{ asset($tour->hero_image) }}" alt="{{ $tour->title }}" class="img-fluid w-100 rounded" style="height: 500px; object-fit: cover;">
            <div class="hero-overlay text-white text-center">
                <h1 class="display-4 fw-bold mb-3">{{ $tour->title }}</h1>
                <p class="lead">
                    <i class="isax isax-calendar-1 me-2"></i> {{ $tour->duration_days }} Days / {{ $tour->duration_nights ?? $tour->duration_days - 1 }} Nights
                    <span class="mx-3">|</span>
                    <i class="isax isax-dollar-square me-2"></i> From ${{ number_format($tour->base_price, 0) }} {{ $tour->currency }}
                </p>
            </div>
        </div>
    @endif

    <div class="row g-5">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Overview -->
            @if($tour->overview)
                <section class="mb-5">
                    <h2 class="mb-4">Overview</h2>
                    <div class="prose max-w-none">
                        {!! $tour->overview !!}
                    </div>
                </section>
            @endif

            <!-- Highlights -->
            @if($tour->highlights && count($tour->highlights) > 0)
                <section class="mb-5">
                    <h2 class="mb-4">Highlights</h2>
                    <ul class="list-unstyled highlight-list">
                        @foreach($tour->highlights as $highlight)
                            <li class="d-flex align-items-start mb-3">
                                <i class="isax isax-tick-circle5 text-primary fs-24 me-3 mt-1"></i>
                                <span>{{ $highlight }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <!-- Itinerary (Accordion style) -->
            @if($tour->itinerary && count($tour->itinerary) > 0)
                <section class="mb-5">
                    <h2 class="mb-4">Detailed Itinerary</h2>
                    <div class="accordion accordion-flush" id="itineraryAccordion">
                        @foreach($tour->itinerary as $day => $item)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $day }}">
                                    <button class="accordion-button {{ $day == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $day }}" aria-expanded="{{ $day == 0 ? 'true' : 'false' }}">
                                        Day {{ $day + 1 }}: {{ $item['title'] ?? 'Day ' . ($day + 1) }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $day }}" class="accordion-collapse collapse {{ $day == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $day }}">
                                    <div class="accordion-body">
                                        {!! $item['description'] ?? '' !!}
                                        @if(!empty($item['accommodation']))
                                            <p class="mt-3 mb-1"><strong>Accommodation:</strong> {{ $item['accommodation'] }}</p>
                                        @endif
                                        @if(!empty($item['meals']))
                                            <p class="mb-1"><strong>Meals:</strong> {{ $item['meals'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Inclusions & Exclusions -->
            <div class="row g-4">
                @if($tour->inclusions && count($tour->inclusions) > 0)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="card-title mb-4">What's Included</h3>
                                <ul class="list-unstyled">
                                    @foreach($tour->inclusions as $inc)
                                        <li class="mb-2"><i class="isax isax-tick-circle5 text-success me-2"></i> {{ $inc }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if($tour->exclusions && count($tour->exclusions) > 0)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="card-title mb-4">What's Excluded</h3>
                                <ul class="list-unstyled">
                                    @foreach($tour->exclusions as $exc)
                                        <li class="mb-2"><i class="isax isax-close-circle5 text-danger me-2"></i> {{ $exc }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Map (if exists) -->
            @if($tour->map_data)
                <section class="mt-5">
                    <h2 class="mb-4">Tour Route Map</h2>
                    <div id="tour-map" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
                </section>
            @endif
        </div>

        <!-- Sidebar / CTA -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-4">
                        <h3 class="mb-3">Book This Tour</h3>
                        <p class="fs-4 fw-bold text-primary mb-1">From ${{ number_format($tour->base_price, 0) }} {{ $tour->currency }}</p>
                        <p class="text-muted mb-4">per person</p>

                        <a href="{{ route('booking.create', $tour->slug) }}" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="isax isax-reserve-ticket me-2"></i> Book Now
                        </a>

                        <a href="https://wa.me/255YOURNUMBER?text=I'm%20interested%20in%20{{ urlencode($tour->title) }}" target="_blank" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fab fa-whatsapp me-2"></i> WhatsApp Inquiry
                        </a>

                        <a href="{{ route('page.show', 'contact') }}" class="btn btn-outline-primary w-100">
                            Send Inquiry via Form
                        </a>

                        <!-- Quick info -->
                        <ul class="list-group list-group-flush mt-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Duration</span>
                                <strong>{{ $tour->duration_days }} Days</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Difficulty</span>
                                <strong>{{ ucfirst($tour->physical_rating) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Style</span>
                                <strong>{{ ucfirst(str_replace('_', ' ', $tour->tour_level)) }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-scripts')
    @if($tour->map_data)
        <!-- Google Maps or Leaflet script -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var mapData = @json($tour->map_data);
                if (mapData && mapData.center && mapData.zoom) {
                    var map = L.map('tour-map').setView([mapData.center.lat, mapData.center.lng], mapData.zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    // Add markers/polyline if present
                    if (mapData.markers) {
                        mapData.markers.forEach(marker => {
                            L.marker([marker.lat, marker.lng]).addTo(map)
                                .bindPopup(marker.popup || 'Destination');
                        });
                    }
                }
            });
        </script>
    @endif
@endsection