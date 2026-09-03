@extends('frontend.layouts.app')

@section('title', $post->meta_title ?? $post->title . ' | Afro-Vertex Tours Blog')

@section('extra-head')
    <meta name="description" content="{{ $post->meta_description ?? Str::limit(strip_tags($post->content), 160) }}">
    <meta name="keywords" content="{{ $post->meta_keywords ?? 'travel blog, safari tips, kilimanjaro, zanzibar, africa travel' }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($post->content), 200) }}">
    @if($post->hasFeaturedImage())
        <meta property="og:image" content="{{ $post->featuredImageUrl() }}">
    @endif
    <meta property="og:url" content="{{ request()->url() }}">
@endsection

@section('page-content')
<!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">{{ $post->title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($post->title, 40) }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container">
            <!-- Blog Details -->
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card blog-details mb-4 mb-lg-0">
                        <div class="card-body">
                            <div class="blog-content">
                                <!-- Featured Image -->
                                @if($post->hasFeaturedImage())
                                    <div class="blog-image mb-3">
                                        <img src="{{ $post->featuredImageUrl() }}" 
                                             alt="{{ $post->title }}" 
                                             class="img-fluid rounded">
                                    </div>
                                @endif

                                <!-- Meta -->
                                <div class="d-flex align-items-center flex-wrap row-gap-2 mb-3">
                                    <a href="javascript:void(0);" class="d-flex align-items-center fs-16 text-gray-9 pe-3 border-end me-3">
                                        <!-- Author avatar (placeholder for now – can link to staff later) -->
                                        <img src="{{ asset('front-end/html/assets/img/users/user-01.jpg') }}" alt="Author" 
                                             class="img-fluid avatar avatar-sm rounded-circle me-2">
                                        {{ Auth::user()->name ?? 'Admin' }} <!-- Replace with real author later -->
                                    </a>
                                    <div class="pe-3 border-end me-3">
                                        <span class="d-flex align-items-center fs-16 text-gray-9">
                                            <i class="isax isax-calendar-2 me-1"></i>
                                            {{ $post->published_at ? $post->published_at->format('d M Y') : 'Draft' }}
                                        </span>
                                    </div>
                                    <div>
                                        @if($post->category)
                                            <span class="badge badge-sm badge-primary">
                                                {{ $post->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Title -->
                                {{-- <div class="mb-3">
                                    <h2>{{ $post->title }}</h2>
                                </div> --}}

                                <!-- Content -->
                                <div class="mb-3 lh-base">
                                    {!! $post->content !!}
                                </div>

                                <!-- Tags & Share -->
                                <div class="mt-3 pb-3 border-bottom d-flex flex-wrap align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <p class="fs-16 text-gray-9 mb-0 me-2">Tags :</p>
                                        <!-- Static tags for now – later replace with dynamic tags -->
                                        <a href="#" class="badge badge-sm badge-secondary me-2">Travels</a>
                                        <a href="#" class="badge badge-sm badge-secondary me-2">Tips</a>
                                        <a href="#" class="badge badge-sm badge-secondary">Guide</a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <p class="fs-16 text-gray-9 mb-0 me-2">Share On :</p>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="me-2">
                                            <img src="{{ asset('front-end/html/assets/img/icons/facebook.svg') }}" alt="Facebook">
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="me-2">
                                            <img src="{{ asset('front-end/html/assets/img/icons/twitter.svg') }}" alt="Twitter">
                                        </a>
                                        <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->url()) }}" target="_blank">
                                            <img src="{{ asset('front-end/html/assets/img/icons/whatsapp.svg') }}" alt="WhatsApp">
                                        </a>
                                    </div>
                                </div>

                                <!-- Author Bio -->
                                <div class="my-3">
                                    <div class="border border-light br-10 p-3 d-md-flex align-items-center">
                                        <div class="blog-user-image me-md-3 mb-3 mb-md-0 flex-shrink-0">
                                            <img src="{{ asset('front-end/html/assets/img/users/user-01.jpg') }}" alt="Author" class="img-fluid rounded">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fs-14 text-primary mb-1">About Author</h6>
                                            <p class="fs-16 text-gray-6">
                                                Hi, I’m the team at Afro-Vertex Tours. We live and breathe East Africa travel — from Serengeti sunrises to Kilimanjaro summits. Our blog shares real stories, insider tips, and inspiration to help you plan your perfect adventure.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comments Section (static for now – can make dynamic later) -->
                                {{-- <h6>Comments</h6>
                                <div class="my-3">
                                    <!-- Example comment -->
                                    <div class="border border-light rounded p-3 mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('front-end/html/assets/img/users/user-21.jpg') }}" alt="User" class="img-fluid rounded-circle avatar avatar-md me-2">
                                                <div>
                                                    <h6>Charles Lozano</h6>
                                                    <span class="fs-14 fw-normal text-gray-6">a week ago</span>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="#" class="fs-14 fw-medium text-dark d-flex align-items-center">
                                                    <i class="isax isax-back-square me-1"></i> Reply
                                                </a>
                                            </div>
                                        </div>
                                        <p class="fs-14 text-gray-6">Great post! Love the practical tips for solo travelers. Super inspiring!</p>
                                    </div>

                                    <!-- Comment Form -->
                                    <h6 class="mb-3">Write A Comment</h6>
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Message</label>
                                                    <textarea class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                                        </div>
                                    </form>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 col-md-12 theiaStickySidebar">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="pb-3 border-bottom mb-3">
                                <h5 class="d-flex align-items-center">
                                    <span class="me-1 fs-16"><i class="isax isax-search-normal text-primary"></i></span> Search
                                </h5>
                            </div>
                            <div class="blog-search">
                                <div class="search-content">
                                    <div class="search-feild position-relative">
                                        <span><i class="isax isax-search-normal"></i></span>
                                        <input type="text" class="form-control" placeholder="Search">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-candle text-primary fs-16 me-2"></i>Categories</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-medium mb-0"><a href="#">Travel</a></h6>
                                <p>(12)</p>
                            </div>
                            <!-- Add dynamic categories later -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-medium mb-0"><a href="#">Guide</a></h6>
                                <p>(10)</p>
                            </div>
                            <!-- ... more categories ... -->
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="ti ti-brand-blogger text-primary fs-16 me-2"></i>Related Posts</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            @foreach($relatedPosts as $related)
                                <div class="blog-post mb-3">
                                    <div class="d-flex align-items-center">
                                        @if($related->hasFeaturedImage())
                                            <div class="d-flex">
                                                <a href="{{ route('blog.show', $related->slug) }}" class="avatar avatar-xxl me-2">
                                                    <img src="{{ $related->featuredImageUrl('thumb') }}" 
                                                         class="rounded" alt="{{ $related->title }}">
                                                </a>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('blog.show', $related->slug) }}" 
                                               class="two-line-ellipsis fs-14 fw-medium">
                                                {{ $related->title }}
                                            </a>
                                            <div class="d-flex align-items-center mt-2">
                                                <a href="#" class="d-flex align-items-center border-end pe-2 me-2">
                                                    <span class="avatar avatar-xs me-1">
                                                        <img src="{{ asset('front-end/html/assets/img/users/user-01.jpg') }}" 
                                                             class="blog-user-img rounded-circle border border-light" alt="img">
                                                    </span>
                                                    <p class="fs-14 text-truncate">Admin</p>
                                                </a>
                                                <p class="fs-14 text-truncate">
                                                    <i class="isax isax-calendar-2 me-2"></i>
                                                    {{ $related->published_at ? $related->published_at->format('d M Y') : 'Draft' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Popular Tags (static for now) -->
                    <div class="card mb-0">
                        <div class="card-header border-0 pb-0">
                            <div class="pb-3 border-bottom">
                                <h5><i class="isax isax-tag text-primary fs-16 me-2"></i>Popular Tags</h5>
                            </div>
                        </div>
                        <div class="card-body pt-3 pb-2">
                            <div class="d-flex align-items-center flex-wrap category-tag">
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Luxury</a>
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Travel</a>
                                <a href="#" class="badge badge-md fw-normal me-2 mb-2">Nature</a>
                                <a href="#" class="badge badge-md fw-normal mb-2">Photography</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection