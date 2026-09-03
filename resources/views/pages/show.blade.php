@extends('frontend.layouts.app')

@section('title', $page->meta_title ?? ($page->title . ' | ' . \App\Models\Setting::get('site_name', 'Afro-Vertex Tours & Safaris')))

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
                    <h1 class="breadcrumb-title mb-2">{{ $page->title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
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
            <!-- Optional hero image — most content-only pages (Terms, Refund Policy)
                 won't set one, so this simply doesn't render for them. -->
            @if($page->hasHeroImage())
                <div class="text-center mb-5 h-200">
                    <img src="{{ $page->heroUrl('medium') }}" alt="{{ $page->title }}" class="img-fluid rounded shadow">
                </div>
            @endif

            @if($page->extra_heading)
                <h1 class="mb-3">{{ $page->extra_heading }}</h1>
            @endif

            @if($page->extra_subheading)
                <p class="lead text-gray-700 mb-4">{!! $page->extra_subheading !!}</p>
            @endif

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="prose text-gray-700">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>

            <!-- Optional CTA — uses the page's own cta_text/cta_link fields (already
                 in the admin form's "General extra fields" section) rather than
                 hardcoded copy, so it's meaningful for whichever page uses it. -->
            @if($page->cta_text && $page->cta_link)
                <div class="text-center mt-5 pt-4 border-top">
                    <a href="{{ $page->cta_link }}" class="btn btn-primary btn-lg px-5">
                        {{ $page->cta_text }}
                    </a>
                </div>
            @endif
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection
