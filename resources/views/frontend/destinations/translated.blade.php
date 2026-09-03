@extends('frontend.layouts.app')

@php
    // ================================================
    // Translated Blog Post View (translated.blade.php)
    // ================================================
    // $translation = instance from blog_post_translations table
    // $post = full record from blog_posts table (loaded via post_id)
  
    $displayTitle = $translation ? $translation->title : $post->title;
    $displaySlug  = $translation ? $translation->slug  : $post->slug;
    $languageCode = $translation ? $translation->language_code : 'en';

    // ================================================
    // SERVER-SIDE FULL PAGE TRANSLATOR
    // Translates ALL content + static texts
    // ================================================
    $displayContent = $post->content;   // default = original

    if ($languageCode !== 'en' && $languageCode !== '') {
        try {
            // ========================
            // 1. TRANSLATE MAIN CONTENT (ALL paragraphs & headings)
            // ========================
            $respContent = \Illuminate\Support\Facades\Http::get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => $languageCode,
                'dt' => 't',
                'q' => $post->content,
            ]);

            if ($respContent->successful()) {
                $result = $respContent->json();

                // FIXED: Join ALL translated segments (this solves the "only one paragraph" issue)
                $fullTranslatedContent = '';
                if (isset($result[0]) && is_array($result[0])) {
                    foreach ($result[0] as $segment) {
                        if (isset($segment[0])) {
                            $fullTranslatedContent .= $segment[0];
                        }
                    }
                }
                $displayContent = $fullTranslatedContent ?: $post->content;
            }

            // ========================
            // 2. TRANSLATE ALL STATIC + DYNAMIC TEXTS
            // ========================
            $textsToTranslate = [
                'blog'           => 'Blog',
                'tags'           => 'Tags :',
                'share'          => 'Share On :',
                'about_author'   => 'About Author',
                'author_bio'     => 'Hi, I’m the team at Afro-Vertex Tours. We live and breathe East Africa travel — from Serengeti sunrises to Kilimanjaro summits. Our blog shares real stories, insider tips, and inspiration to help you plan your perfect adventure.',
                'search'         => 'Search',
                'categories'     => 'Categories',
                'related_posts'  => 'Related Posts',
                'popular_tags'   => 'Popular Tags',
                'travels'        => 'Travels',
                'tips'           => 'Tips',
                'guide'          => 'Guide',
                'luxury'         => 'Luxury',
                'travel'         => 'Travel',
                'nature'         => 'Nature',
                'photography'    => 'Photography',
                'draft'          => 'Draft',
                'admin'          => 'Admin',
            ];

            // Add dynamic texts
            if ($post->category) {
                $textsToTranslate['category_name'] = $post->category->name;
            }
            foreach ($relatedPosts as $index => $related) {
                $textsToTranslate['related_title_' . $index] = $related->title;
            }

            $translated = [];
            foreach ($textsToTranslate as $key => $text) {
                $response = \Illuminate\Support\Facades\Http::get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => $languageCode,
                    'dt' => 't',
                    'q' => $text,
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $translated[$key] = $result[0][0][0] ?? $text;
                } else {
                    $translated[$key] = $text;
                }
            }

        } catch (\Exception $e) {
            // Silent fallback
            $displayContent = $post->content;
            $translated = $textsToTranslate ?? [];
        }
    } else {
        // English - no translation
        $translated = [
            'blog' => 'Blog', 'tags' => 'Tags :', 'share' => 'Share On :',
            'about_author' => 'About Author',
            'author_bio' => 'Hi, I’m the team at Afro-Vertex Tours. We live and breathe East Africa travel — from Serengeti sunrises to Kilimanjaro summits. Our blog shares real stories, insider tips, and inspiration to help you plan your perfect adventure.',
            'search' => 'Search', 'categories' => 'Categories', 'related_posts' => 'Related Posts',
            'popular_tags' => 'Popular Tags', 'travels' => 'Travels', 'tips' => 'Tips',
            'guide' => 'Guide', 'luxury' => 'Luxury', 'travel' => 'Travel',
            'nature' => 'Nature', 'photography' => 'Photography',
            'draft' => 'Draft', 'admin' => 'Admin',
        ];
        if ($post->category) $translated['category_name'] = $post->category->name;
        foreach ($relatedPosts as $index => $related) {
            $translated['related_title_' . $index] = $related->title;
        }
    }
@endphp

@section('title', ($post->meta_title ?? $displayTitle) . ' | Afro-Vertex Tours Blog')

@section('extra-head')
    <meta name="description" content="{{ $post->meta_description ?? Str::limit(strip_tags($displayContent), 160) }}">
    <meta name="keywords" content="{{ $post->meta_keywords ?? 'travel blog, safari tips, kilimanjaro, zanzibar, africa travel' }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $displayTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($displayContent), 200) }}">
    @if($post->hasFeaturedImage())
        <meta property="og:image" content="{{ $post->featuredImageUrl() }}">
    @endif
    <meta property="og:url" content="{{ request()->url() }}">

    <!-- GOOGLE TRANSLATE (invisible - auto only) -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay: false,
                includedLanguages: '{{ $languageCode }}'
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <style>
        .goog-te-banner-frame,
        .goog-te-gadget,
        .goog-te-gadget-simple,
        .goog-te-menu-value,
        .goog-te-menu-frame,
        .goog-te-spinner,
        #google_translate_element {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
        }
        body { top: 0 !important; }
    </style>
@endsection

@section('page-content')
<!-- Breadcrumb -->
<div class="breadcrumb-bar breadcrumb-bg-02 text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h1 class="breadcrumb-title mb-2">{{ $displayTitle }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="isax isax-home5"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">{{ $translated['blog'] }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($displayTitle, 40) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container">
        <div id="google_translate_element" style="display:none;"></div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card blog-details mb-4 mb-lg-0">
                    <div class="card-body">
                        <div class="blog-content">
                            <!-- Featured Image -->
                            @if($post->hasFeaturedImage())
                                <div class="blog-image mb-3">
                                    <img src="{{ $post->featuredImageUrl() }}"
                                         alt="{{ $displayTitle }}" class="img-fluid rounded">
                                </div>
                            @endif

                            <!-- Meta -->
                            <div class="d-flex align-items-center flex-wrap row-gap-2 mb-3">
                                <a href="javascript:void(0);" class="d-flex align-items-center fs-16 text-gray-9 pe-3 border-end me-3">
                                    <img src="{{ asset('front-end/html/assets/img/users/user-01.jpg') }}" alt="Author"
                                         class="img-fluid avatar avatar-sm rounded-circle me-2">
                                    {{ Auth::user()->name ?? $translated['admin'] }}
                                </a>
                                <div class="pe-3 border-end me-3">
                                    <span class="d-flex align-items-center fs-16 text-gray-9">
                                        <i class="isax isax-calendar-2 me-1"></i>
                                        {{ $post->published_at ? $post->published_at->format('d M Y') : $translated['draft'] }}
                                    </span>
                                </div>
                                @if($post->category)
                                    <span class="badge badge-sm badge-primary">{{ $translated['category_name'] ?? $post->category->name }}</span>
                                @endif
                            </div>

                            <!-- FULL CONTENT - ALL PARAGRAPHS & HEADINGS TRANSLATED -->
                            <div class="mb-3 lh-lg">
                                {!! $displayContent !!}
                            </div>

                            <!-- Tags & Share -->
                            <div class="mt-3 pb-3 border-bottom d-flex flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <p class="fs-16 text-gray-9 mb-0 me-2">{{ $translated['tags'] }}</p>
                                    <a href="#" class="badge badge-sm badge-secondary me-2">{{ $translated['travels'] }}</a>
                                    <a href="#" class="badge badge-sm badge-secondary me-2">{{ $translated['tips'] }}</a>
                                    <a href="#" class="badge badge-sm badge-secondary">{{ $translated['guide'] }}</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <p class="fs-16 text-gray-9 mb-0 me-2">{{ $translated['share'] }}</p>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="me-2">
                                        <img src="{{ asset('front-end/html/assets/img/icons/facebook.svg') }}" alt="Facebook">
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($displayTitle) }}" target="_blank" class="me-2">
                                        <img src="{{ asset('front-end/html/assets/img/icons/twitter.svg') }}" alt="Twitter">
                                    </a>
                                    <a href="https://wa.me/?text={{ urlencode($displayTitle . ' ' . request()->url()) }}" target="_blank">
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
                                        <h6 class="fs-14 text-primary mb-1">{{ $translated['about_author'] }}</h6>
                                        <p class="fs-16 text-gray-6">{{ $translated['author_bio'] }}</p>
                                    </div>
                                </div>
                            </div>
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
                                <span class="me-1 fs-16"><i class="isax isax-search-normal text-primary"></i></span> {{ $translated['search'] }}
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
                            <h5><i class="isax isax-candle text-primary fs-16 me-2"></i>{{ $translated['categories'] }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <!-- your existing categories here -->
                    </div>
                </div>

                <!-- Related Posts -->
                <div class="card mb-3">
                    <div class="card-header border-0 pb-0">
                        <div class="pb-3 border-bottom">
                            <h5><i class="ti ti-brand-blogger text-primary fs-16 me-2"></i>{{ $translated['related_posts'] }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @foreach($relatedPosts as $index => $related)
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
                                            {{ $translated['related_title_' . $index] ?? $related->title }}
                                        </a>
                                        <div class="d-flex align-items-center mt-2">
                                            <a href="#" class="d-flex align-items-center border-end pe-2 me-2">
                                                <span class="avatar avatar-xs me-1">
                                                    <img src="{{ asset('front-end/html/assets/img/users/user-01.jpg') }}"
                                                         class="blog-user-img rounded-circle border border-light" alt="img">
                                                </span>
                                                <p class="fs-14 text-truncate">{{ $translated['admin'] }}</p>
                                            </a>
                                            <p class="fs-14 text-truncate">
                                                <i class="isax isax-calendar-2 me-2"></i>
                                                {{ $related->published_at ? $related->published_at->format('d M Y') : $translated['draft'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Popular Tags -->
                <div class="card mb-0">
                    <div class="card-header border-0 pb-0">
                        <div class="pb-3 border-bottom">
                            <h5><i class="isax isax-tag text-primary fs-16 me-2"></i>{{ $translated['popular_tags'] }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-3 pb-2">
                        <div class="d-flex align-items-center flex-wrap category-tag">
                            <a href="#" class="badge badge-md fw-normal me-2 mb-2">{{ $translated['luxury'] }}</a>
                            <a href="#" class="badge badge-md fw-normal me-2 mb-2">{{ $translated['travel'] }}</a>
                            <a href="#" class="badge badge-md fw-normal me-2 mb-2">{{ $translated['nature'] }}</a>
                            <a href="#" class="badge badge-md fw-normal mb-2">{{ $translated['photography'] }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUTO-TRANSLATE SCRIPT -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const targetLang = "{{ $languageCode }}".toLowerCase();
        if (targetLang === 'en' || targetLang === '') return;
        setTimeout(() => {
            const select = document.querySelector('.goog-te-combo');
            if (select) {
                select.value = targetLang;
                select.dispatchEvent(new Event('change'));
            }
        }, 1200);
    });
</script>
@endsection