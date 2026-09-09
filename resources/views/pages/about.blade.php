@extends('frontend.layouts.app')

@section('title', $page->meta_title ?? 'About Us | Afro-Vertex Tours & Safaris')

@section('extra-head')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
@endsection

@section('page-content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">{{ $page->title ?? 'About Us' }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $page->title ?? 'About Us' }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumb -->

    <!-- Page Wrapper -->
    <div class="content">
        <div class="container">
            <!-- Hero / Intro Section -->
            <section class="about-hero mb-5">
                <div class="about-hero-img d-none justify-content-center align-items-center mb-4">
                    @if($page->hasHeroImage())
                        <img src="{{ $page->heroUrl('medium') }}" 
                             alt="{{ $page->title }}" 
                             class="img-fluid rounded shadow">
                    @else
                        <img src="{{ asset('asset/img/placeholder-page-hero.jpg') }}" alt="Default">
                    @endif
                </div>

                <div class="row justify-content-center">
                    <div>
                        <h1 class="display-5 fw-bold mb-4">
                            {{ $page->extra_heading ?? 'Discover the Heart of East Africa with Afro-Vertex Tours' }}
                        </h1>
                        <p class="lead text-gray-700 mb-4">
                            {!! $page->extra_subheading ?? 'We are passionate local experts dedicated to creating authentic, responsible, and unforgettable travel experiences across Tanzania, Kenya, Uganda, and Rwanda.' !!}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Our Story -->
            <section class="section about-story bg-light-100 py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="about-img">
                            @if($page->hasStoryImage())
                                <img src="{{ $page->storyUrl('medium') }}" 
                                     alt="{{ $page->story_title ?? 'Our Story' }}" 
                                     class="img-fluid rounded shadow">
                            @else
                                <img src="{{ asset('asset/img/placeholder-about.jpg') }}" alt="About Us">
                            @endif
                        </div>

                        @if(!empty($page->story_gallery))
                            <div class="row g-4 mt-1">
                                @foreach($page->story_gallery as $storyImage)
                                    @php
                                        $storyGalleryImage = $storyImage['image_id'] ?? null
                                            ? \App\Models\GalleryImage::find($storyImage['image_id'])
                                            : null;
                                    @endphp
                                    @if($storyGalleryImage)
                                        <div class="col-6">
                                            <figure class="mb-0">
                                                <img src="{{ $storyGalleryImage->getUrl('medium') }}" 
                                                     alt="{{ $storyImage['caption'] ?? ($page->story_title ?? 'Our Story') }}" 
                                                     class="img-fluid rounded shadow">
                                                @if(!empty($storyImage['caption']))
                                                    <figcaption class="small text-gray-600 text-center mt-2">
                                                        {{ $storyImage['caption'] }}
                                                    </figcaption>
                                                @endif
                                            </figure>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-6">
                        <div class="about-content lh-lg">
                            <h2 class="mb-4">{{ $page->story_title ?? 'Our Story & Passion' }}</h2>
                            <div class="prose text-gray-700">
                                {!! $page->content !!}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Counters -->
            @if(!empty($page->stats_counters))
                <section class="section stats-counters-sec py-4">
                    <div class="row text-center g-4">
                        @foreach($page->stats_counters as $counter)
                            <div class="col-6 col-md-3">
                                <h3 class="display-6 mb-1">
                                    <span class="counter">{{ $counter['value'] ?? '' }}</span>{{ $counter['suffix'] ?? '+' }}
                                </h3>
                                <p class="text-gray-600 mb-0">{{ $counter['label'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Why Choose Us -->
            <section class="section why-choose py-5">
                <div class="row justify-content-center">
                    <div class="col-xl-8 text-center mb-5">
                        <h2 class="mb-3">Why Travelers Choose Afro-Vertex</h2>
                        <p class="lead text-gray-600">
                            {{ $page->why_choose_subtitle ?? 'We don’t just organize trips — we create lifelong memories with safety, authenticity, and care.' }}
                        </p>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Benefit 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-location-tick fs-32"></i>
                            </div>
                            <h5 class="mb-3">Local Expertise</h5>
                            <p class="text-gray-700 mb-0">
                                Born and raised in Tanzania — we know every trail, every camp, every hidden gem.
                            </p>
                        </div>
                    </div>

                    <!-- Benefit 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-shield-tick fs-32"></i>
                            </div>
                            <h5 class="mb-3">Safety First</h5>
                            <p class="text-gray-700 mb-0">
                                Fully licensed, insured, and trained guides — your well-being is our top priority.
                            </p>
                        </div>
                    </div>

                    <!-- Benefit 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card benefit-card h-100 border-0 shadow-sm text-center p-4">
                            <div class="benefit-icon mb-3 mx-auto bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="isax isax-tree fs-32"></i>
                            </div>
                            <h5 class="mb-3">Responsible Travel</h5>
                            <p class="text-gray-700 mb-0">
                                We support local communities and protect wildlife — sustainable tourism is in our DNA.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Team -->
            @if(!empty($page->custom_data))
                <section class="section our-team py-5 bg-light-100">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 text-center mb-5">
                            <h2 class="mb-2">Our <span class="text-primary">Team</span></h2>
                            <p class="lead text-gray-600">The people behind every journey we plan.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach($page->custom_data as $member)
                            <div class="col-lg-3 col-md-4 col-sm-6 text-center">
                                <div class="card h-100">
                                    @php
                                        $memberPhoto = null;
                                        if (!empty($member['photo_image_id'])) {
                                            $memberPhoto = \App\Models\GalleryImage::find($member['photo_image_id']);
                                        }
                                    @endphp
                                    @if($memberPhoto)
                                        <img src="{{ $memberPhoto->getUrl('medium') }}" class="card-img-top" alt="{{ $member['name'] ?? '' }}" style="height: 240px; object-fit: cover;">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="mb-1">{{ $member['name'] ?? '' }}</h5>
                                        @if(!empty($member['role']))
                                            <p class="text-primary mb-2">{{ $member['role'] }}</p>
                                        @endif
                                        @if(!empty($member['bio']))
                                            <p class="text-gray-600 mb-0 small">{{ $member['bio'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Testimonials -->
            @if($testimonials->count() > 0)
                <section class="section about-testimonials py-5">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 text-center mb-5">
                            <h2 class="mb-2">What's Our <span class="text-primary text-decoration-underline">User</span> Says</h2>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach($testimonials as $testimonial)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center fs-12 mb-3">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="ti ti-star-filled {{ $i <= $testimonial->rating ? 'text-warning' : 'text-gray-3' }}"></i>
                                            @endfor
                                        </div>
                                        <p class="mb-4">"{{ $testimonial->content }}"</p>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avtar-lg me-2">
                                                @if($testimonial->hasAvatar())
                                                    <img src="{{ $testimonial->avatarUrl('thumb') }}" class="rounded-circle" alt="{{ $testimonial->name }}">
                                                @else
                                                    <img src="{{ asset('front-end/html/assets/img/users/user-28.jpg') }}" class="rounded-circle" alt="{{ $testimonial->name }}">
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $testimonial->name }}</h6>
                                                @if($testimonial->location)
                                                    <span class="d-block text-muted">{{ $testimonial->location }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Call to Action -->
            <section class="section cta-section bg-primary text-white text-center py-5">
                <div class="container">
                    <h2 class="mb-4  text-white">Ready for Your Next Adventure?</h2>
                    <p class="lead mb-4">Let us craft the perfect journey for you — from Kilimanjaro summit to Zanzibar beaches.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('tours.index') }}" class="btn btn-light btn-lg px-5">
                            Explore Tours
                        </a>
                        <a href="{{ route('page.show', 'contact') }}" class="btn btn-outline-light text-white btn-lg px-5">
                            Contact Us
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection