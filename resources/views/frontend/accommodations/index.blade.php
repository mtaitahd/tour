@extends('frontend.layouts.app')

@section('title', 'Accommodations | Afro-Vertex Tours & Safaris')
@section('extra-head')
    <meta name="description" content="Browse every boutique lodge, tented camp, and beach resort handpicked by Afro-Vertex Tours & Safaris. Comfort, character, and a front-row seat to the wild.">
    <meta name="keywords" content="east africa accommodation, safari lodges, tented camps, beach resorts, tanzania hotels, kenya safari lodges, zanzibar resorts">
@endsection

@section('page-content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">All Accommodations</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active">Accommodations</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Accommodations Grid -->
    <div class="content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="fs-24 fw-bold mb-0">Where You'll Stay</h2>
                <span class="text-muted">Showing {{ $accommodations->firstItem() }}-{{ $accommodations->lastItem() }} of {{ $accommodations->total() }}</span>
            </div>

            <div class="featured-tours-grid">
                @forelse ($accommodations as $stay)
                    <article class="afro-tour-card afro-tour-card--refined wow fadeInUp" role="button" tabindex="0"
                             data-bs-toggle="modal" data-bs-target="#accommodationModal{{ $stay->id }}" style="cursor: pointer;">
                        <div class="afro-tour-card__img-wrap">
                            <img src="{{ $stay->cardImageUrl('medium') }}" alt="{{ $stay->name }}" class="afro-tour-card__img" width="800" height="600" loading="lazy"
                                 onerror="this.onerror=null;this.src='{{ asset('public/assets/images/safari-hero.jpg') }}';">
                            <div class="afro-tour-card__overlay" aria-hidden="true"></div>
                            @if($stay->tier)
                                <span class="afro-tour-card__badge">{{ $stay->tier }}</span>
                            @endif
                            <h3 class="afro-tour-card__title">{{ $stay->name }}</h3>
                        </div>

                        <div class="afro-tour-card__body">
                            <div class="afro-tour-card__meta">
                                <span class="afro-tour-card__meta-item">
                                    <i class="isax isax-location5" aria-hidden="true"></i>
                                    {{ $stay->destination->name ?? $stay->location ?? 'East Africa' }}
                                </span>
                            </div>

                            @if($stay->price_from)
                                <div class="afro-tour-card__price-row">
                                    <div class="afro-tour-card__price">
                                        <span class="afro-tour-card__price-label">From</span>
                                        <span class="afro-tour-card__price-amount afro-tour-card__price-amount--quote">{{ $stay->currency }} {{ number_format($stay->price_from, 0) }}</span>
                                        <span class="afro-tour-card__price-unit">/ night</span>
                                    </div>
                                </div>
                            @endif

                            <span class="afro-tour-card__more">
                                View Details
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.16663 10H15.8333M15.8333 10L10.4166 4.58334M15.8333 10L10.4166 15.4167"/></svg>
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="col-12 text-center py-5">
                        <h4>No accommodations available yet.</h4>
                        <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-5">
                {{ $accommodations->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Accommodation Detail Modals -->
    @foreach ($accommodations as $stay)
        <div class="modal fade" id="accommodationModal{{ $stay->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $stay->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php $stayGallery = $stay->galleryImages(); @endphp

                        @if($stay->hasHeroImage() || $stayGallery->isNotEmpty())
                            <div class="row g-2 mb-3">
                                @if($stay->hasHeroImage())
                                    <div class="col-6">
                                        <img src="{{ $stay->heroUrl('medium') }}" class="img-fluid rounded" style="width:100%; height:160px; object-fit:cover;" alt="{{ $stay->name }}">
                                    </div>
                                @endif
                                @foreach($stayGallery->take(3) as $img)
                                    <div class="col-6">
                                        <img src="{{ $img->getUrl('medium') }}" class="img-fluid rounded" style="width:100%; height:160px; object-fit:cover;" alt="{{ $stay->name }}">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            @if($stay->tier)
                                <span class="badge bg-teal">{{ $stay->tier }}</span>
                            @endif
                            <span class="text-muted">
                                <i class="isax isax-location5 me-1"></i>{{ $stay->destination->name ?? $stay->location ?? 'East Africa' }}
                            </span>
                            @if($stay->price_from)
                                <span class="fw-semibold text-primary ms-auto">
                                    From {{ $stay->currency }} {{ number_format($stay->price_from, 0) }}/night
                                </span>
                            @endif
                        </div>

                        @if($stay->description)
                            <p class="mb-3">{!! $stay->description !!}</p>
                        @endif

                        @if(!empty($stay->amenities))
                            <h6 class="mb-2">Amenities</h6>
                            <ul class="list-unstyled row g-1 mb-0">
                                @foreach($stay->amenities as $amenity)
                                    <li class="col-6"><i class="isax isax-tick-circle5 text-primary me-1"></i>{{ $amenity }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('page.show', 'contact') }}" class="btn btn-primary">Enquire About This Stay</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
