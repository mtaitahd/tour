@extends('frontend.layouts.app')

@section('title', 'Destinations in East Africa | Afro-Vertex Tours & Safaris')
@section('extra-head')
    <meta name="description" content="Explore the most stunning destinations in Tanzania, Kenya, Uganda, and Rwanda. From Serengeti safaris and Kilimanjaro climbs to Zanzibar beaches and gorilla trekking in Bwindi — discover your next adventure.">
    <meta name="keywords" content="east africa destinations, serengeti, ngorongoro, zanzibar, kilimanjaro, bwindi, uganda gorillas, kenya safaris, rwanda tourism">
@endsection

@section('page-content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">Explore Destinations</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active">Destinations</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Grid -->
    <div class="content">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-xl-3 col-lg-4 theiaStickySidebar">
                    <div class="sticky-top" style="top: 100px;">
                        <div class="card shadow-none mb-4">
                            <div class="card-body">
                                <h5 class="fs-18 mb-4">Filter Destinations</h5>

                                <form method="GET" action="{{ route('destinations.index') }}">
                                    <!-- Country -->
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Country</label>
                                        <select name="country" class="form-select">
                                            <option value="">All Countries</option>
                                            <option value="TZ" {{ request('country') == 'TZ' ? 'selected' : '' }}>Tanzania</option>
                                            <option value="KE" {{ request('country') == 'KE' ? 'selected' : '' }}>Kenya</option>
                                            <option value="UG" {{ request('country') == 'UG' ? 'selected' : '' }}>Uganda</option>
                                            <option value="RW" {{ request('country') == 'RW' ? 'selected' : '' }}>Rwanda</option>
                                        </select>
                                    </div>

                                    <!-- Type -->
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Type</label>
                                        <select name="type" class="form-select">
                                            <option value="">All Types</option>
                                            <option value="national_park" {{ request('type') == 'national_park' ? 'selected' : '' }}>National Park</option>
                                            <option value="mountain" {{ request('type') == 'mountain' ? 'selected' : '' }}>Mountain</option>
                                            <option value="beach" {{ request('type') == 'beach' ? 'selected' : '' }}>Beach</option>
                                            <option value="lake" {{ request('type') == 'lake' ? 'selected' : '' }}>Lake</option>
                                            <option value="city" {{ request('type') == 'city' ? 'selected' : '' }}>City</option>
                                            <option value="village" {{ request('type') == 'village' ? 'selected' : '' }}>Village</option>
                                            <!-- Add more types as needed -->
                                        </select>
                                    </div>

                                    <!-- Featured Only -->
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" {{ request('featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="featured">Featured Destinations Only</label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        Apply Filters
                                    </button>

                                    <a href="{{ route('destinations.index') }}" class="btn btn-outline-secondary w-100">
                                        Reset Filters
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Destinations Grid -->
                <div class="col-xl-9 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <h2 class="fs-24 fw-bold mb-0">Our Destinations</h2>
                        <span class="text-muted">Showing {{ $destinations->firstItem() }}-{{ $destinations->lastItem() }} of {{ $destinations->total() }}</span>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary active grid-view"><i class="bi bi-grid-3x3-gap"></i></button>
                            <button class="btn btn-outline-secondary list-view"><i class="bi bi-list-ul"></i></button>
                        </div>
                    </div>

                    <div class="row g-4 destination-grid">
                        @forelse ($destinations as $SingleDestination)
                            <div class="col-lg-4 col-md-6 dest-item">
                                <div class="destination-card shadow-sm rounded overflow-hidden h-100 bg-white">
                                    @if($SingleDestination->hasHeroImage())
                                        <div class="position-relative destination-img">
                                            <img src="{{ $SingleDestination->heroUrl('medium') }}" 
                                                 alt="{{ $SingleDestination->name }}" 
                                                 class="img-fluid w-100" 
                                                 style="height: 240px; object-fit: cover;">
                                            @if($SingleDestination->is_featured)
                                                <span class="badge bg-success position-absolute top-0 start-0 m-3">Featured</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="p-4 destination-content">
                                        <h5 class="fs-20 fw-bold mb-2">
                                            <a href="{{ route('destination.show', $SingleDestination->slug) }}" class="text-dark text-decoration-none">
                                                {{ $SingleDestination->name }}
                                            </a>
                                        </h5>

                                        <div class="d-flex align-items-center mb-3">
                                            <span class="badge bg-light text-dark me-2">
                                                {{ strtoupper($SingleDestination->country_code) }}
                                            </span>
                                            @if($SingleDestination->type)
                                                <span class="badge bg-light text-dark">
                                                    {{ ucfirst(str_replace('_', ' ', $SingleDestination->type)) }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($SingleDestination->description)
                                            <p class="text-muted mb-3">
                                                {{ Str::limit(strip_tags($SingleDestination->description), 120) }}
                                            </p>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('destination.show', $SingleDestination->slug) }}" class="btn btn-outline-primary btn-sm">
                                                Explore
                                            </a>
                                            <span class="text-primary">
                                                {{ $SingleDestination->tours()->where('status', 'published')->count() }} Tours
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <h4>No destinations found matching your filters.</h4>
                                <a href="{{ route('destinations.index') }}" class="btn btn-primary mt-3">Clear Filters</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-5">
                        {{ $destinations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@section('extra-scripts'):
    <script>
    $(document).ready(function() {
        $('.grid-view').click(function() {
            $('.destination-grid').removeClass('list-view-active').addClass('grid-view-active');
            $('.grid-view').addClass('active');
            $('.list-view').removeClass('active');
        });

        $('.list-view').click(function() {
            $('.destination-grid').removeClass('grid-view-active').addClass('list-view-active');
            $('.list-view').addClass('active');
            $('.grid-view').removeClass('active');
        });
    });
</script>

<style>
    .destination-grid.list-view-active .dest-item {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .destination-grid.list-view-active .destination-card {
        display: flex;
        flex-direction: row;
    }
    .destination-grid.list-view-active .destination-img {
        width: 35%;
        flex-shrink: 0;
    }
    .destination-grid.list-view-active .destination-content {
        width: 65%;
    }
</style>
@endsection
@endsection