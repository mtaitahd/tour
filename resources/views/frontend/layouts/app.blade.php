<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Important: CSRF token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Title -->
    <title>
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_title ?? $post->title . ' | Afro-Vertex Tours & Safaris' }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_title ?? $tour->title . ' | Afro-Vertex Tours & Safaris' }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_title ?? $destination->name . ' | Afro-Vertex Tours & Safaris' }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ $tourCategory->meta_title ?? $tourCategory->name . ' Tours | Afro-Vertex Tours & Safaris' }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_title ?? $page->title . ' | Afro-Vertex Tours & Safaris' }}
        @else
            {{ $meta['title'] ?? config('app.name', 'Afro-Vertex Tours & Safaris') }}
        @endif
    </title>

    <!-- Meta Description -->
    <meta name="description" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_description ?? Str::limit(strip_tags($post->content), 160) }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_description ?? Str::limit(strip_tags($tour->overview ?? ''), 160) }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_description ?? Str::limit(strip_tags($destination->description ?? ''), 160) }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ $tourCategory->meta_description ?? Str::limit(strip_tags($tourCategory->description ?? ''), 160) }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_description ?? Str::limit(strip_tags($page->content ?? ''), 160) }}
        @else
            {{ $meta['description'] ?? 'Discover the best safaris, climbs, and beach holidays in East Africa with Afro-Vertex Tours & Safaris.' }}
        @endif
    ">

    <!-- Meta Keywords -->
    <meta name="keywords" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_keywords ?? 'travel blog, safari tips, tanzania travel, africa adventures' }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_keywords ?? 'safari, kilimanjaro, zanzibar, tanzania tours' }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_keywords ?? $destination->name . ', safari, tanzania, africa travel' }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ $tourCategory->meta_keywords ?? $tourCategory->name . ', safari, tanzania, africa travel' }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_keywords ?? 'tanzania travel, africa tours, safari' }}
        @else
            {{ $meta['keywords'] ?? 'safari, kilimanjaro, zanzibar, tanzania tours, africa travel' }}
        @endif
    ">

    <!-- Robots -->
    @php
        $noIndex = false;
        if (isset($tour) && $tour instanceof \App\Models\TourPackage && !empty($tour->no_robots)) {
            $noIndex = true;
        } elseif (isset($post) && $post instanceof \App\Models\BlogPost && !empty($post->no_robots)) {
            $noIndex = true;
        } elseif (isset($page) && $page instanceof \App\Models\Page && !empty($page->no_robots)) {
            $noIndex = true;
        }
    @endphp
    @if($noIndex)
        <meta name="robots" content="noindex, nofollow">
    @endif

    <!-- Open Graph -->
    <meta property="og:title" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_title ?? $post->title }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_title ?? $tour->title }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_title ?? $destination->name }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ $tourCategory->meta_title ?? $tourCategory->name . ' Tours' }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_title ?? $page->title }}
        @else
            {{ $meta['title'] ?? config('app.name') }}
        @endif
    ">

    <meta property="og:description" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_description ?? Str::limit(strip_tags($post->content), 200) }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_description ?? Str::limit(strip_tags($tour->overview ?? ''), 200) }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_description ?? Str::limit(strip_tags($destination->description ?? ''), 200) }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ $tourCategory->meta_description ?? Str::limit(strip_tags($tourCategory->description ?? ''), 200) }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_description ?? Str::limit(strip_tags($page->content ?? ''), 200) }}
        @else
            {{ $meta['description'] ?? 'Discover East Africa adventures with Afro-Vertex Tours & Safaris' }}
        @endif
    ">

    @php
        $ogImage = $meta['og_image'] ?? asset('assets/img/og-default.jpg');

        if (isset($post) && $post instanceof \App\Models\BlogPost && $post->hasFeaturedImage()) {
            $ogImage = $post->featuredImageUrl();
        } elseif (isset($tour) && $tour instanceof \App\Models\TourPackage && $tour->hasHeroImage()) {
            $ogImage = $tour->heroUrl();
        } elseif (isset($destination) && $destination instanceof \App\Models\Destination && $destination->hasHeroImage()) {
            $ogImage = $destination->heroUrl();
        } elseif (isset($page) && $page instanceof \App\Models\Page && $page->hasHeroImage()) {
            $ogImage = $page->heroUrl();
        }
    @endphp

    <!-- Canonical URL -->
    <link rel="canonical" href="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ route('blog.show', $post->slug) }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ route('tour.show', $tour->slug) }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ route('destination.show', $destination->slug) }}
        @elseif(isset($tourCategory) && $tourCategory instanceof \App\Models\TourCategory)
            {{ route('tours.category', $tourCategory->slug) }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ route('page.show', $page->slug) }}
        @else
            {{ $meta['canonical'] ?? request()->url() }}
        @endif
    ">

    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Afro-Vertex Tours & Safaris">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_title ?? $post->title }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_title ?? $tour->title }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_title ?? $destination->name }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_title ?? $page->title }}
        @else
            {{ $meta['title'] ?? config('app.name') }}
        @endif
    ">
    <meta name="twitter:description" content="
        @if(isset($post) && $post instanceof \App\Models\BlogPost)
            {{ $post->meta_description ?? Str::limit(strip_tags($post->content), 200) }}
        @elseif(isset($tour) && $tour instanceof \App\Models\TourPackage)
            {{ $tour->meta_description ?? Str::limit(strip_tags($tour->overview ?? ''), 200) }}
        @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
            {{ $destination->meta_description ?? Str::limit(strip_tags($destination->description ?? ''), 200) }}
        @elseif(isset($page) && $page instanceof \App\Models\Page)
            {{ $page->meta_description ?? Str::limit(strip_tags($page->content ?? ''), 200) }}
        @else
            {{ $meta['description'] ?? 'Discover East Africa adventures with Afro-Vertex Tours & Safaris' }}
        @endif
    ">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- JSON-LD Structured Data -->
    @if(isset($tour) && $tour instanceof \App\Models\TourPackage)
        @php
            $tourPrice = null;
            if ($tour->packagePrices->count()) {
                $tourPrice = $tour->packagePrices->min('price_2p');
            }
        @endphp
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $tour->title,
            'description' => $tour->meta_description ?? Str::limit(strip_tags($tour->overview ?? ''), 300),
            'url' => route('tour.show', $tour->slug),
            'image' => $tour->hasHeroImage() ? $tour->heroUrl() : asset('assets/img/og-default.jpg'),
            'touristType' => 'Leisure traveler',
            'itinerary' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($tour->itinerary ?? []),
                'itemListElement' => collect($tour->itinerary ?? [])->map(function ($day, $i) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $day['title'] ?? 'Day ' . ($i + 1),
                        'description' => Str::limit(strip_tags($day['description'] ?? ''), 200),
                    ];
                })->toArray(),
            ],
            'provider' => [
                '@type' => 'TravelAgency',
                'name' => 'Afro-Vertex Tours & Safaris',
                'url' => config('app.url'),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @elseif(isset($destination) && $destination instanceof \App\Models\Destination)
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TouristDestination',
            'name' => $destination->name,
            'description' => $destination->meta_description ?? Str::limit(strip_tags($destination->description ?? ''), 300),
            'url' => route('destination.show', $destination->slug),
            'image' => $destination->hasHeroImage() ? $destination->heroUrl() : asset('assets/img/og-default.jpg'),
            'touristType' => 'Leisure traveler',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    <!-- Organization Schema (appears on every page) -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        'name' => 'Afro-Vertex Tours & Safaris',
        'url' => config('app.url'),
        'logo' => asset('assets/img/logo.png'),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+255-760-096-715',
            'contactType' => 'customer service',
            'availableLanguage' => ['English', 'Swahili'],
        ],
        'sameAs' => [
            'https://www.facebook.com/afrovertextours',
            'https://www.instagram.com/afrovertextours',
            'https://www.youtube.com/@afrovertextours',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <!-- Other head content (favicon, css, etc.) -->
    <!-- ... your existing links and scripts ... -->
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Font Awesome (for whatsapp, facebook, twitter, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@4.0.8/css/line.css">
    <!-- CSS links -->
  <!-- Apple Touch Icon -->
  <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('front-end/html/assets/img/apple-touch-icon.png') }}?v=2">

  <!-- Favicon (v=2 busts the old 404 the browser cached) -->
  <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('front-end/html/assets/img/favicon-32x32.png') }}?v=2">
  <link rel="icon"
        href="{{ asset('front-end/html/assets/img/favicon.ico') }}?v=2"
        type="image/x-icon">
  <link rel="shortcut icon"
        href="{{ asset('front-end/html/assets/img/favicon.ico') }}?v=2"
        type="image/x-icon">

   {{-- Theme Settings JS (belongs in <head>) --}}
  <script src="{{ asset('front-end/html/assets/js/theme-script.js') }}"></script>

  <!-- Animate CSS -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/animate.css') }}">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/bootstrap.min.css') }}">

  <!-- MeanMenu CSS -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/meanmenu.css') }}">

  <!-- Tabler Icons -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/tabler-icons/tabler-icons.css') }}">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/fontawesome/css/all.min.css') }}">

  <!-- Owl Carousel -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/owlcarousel/owl.carousel.min.css') }}">

  <!-- Slick Slider -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/slick/slick.css') }}">

  <!-- FancyBox -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/plugins/fancybox/jquery.fancybox.min.css') }}">

  <!-- Iconsax -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/iconsax.css') }}">

  <!-- Datepicker -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/bootstrap-datetimepicker.min.css') }}">

  <!-- Main Style -->
  <link rel="stylesheet"
        href="{{ asset('front-end/html/assets/css/style.css') }}?v={{ time() }}">

  <!-- Cormorant Garamond (serif) + Inter (body) + Montserrat (sans) + Caveat (About) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&family=Caveat:wght@400;500;600;700&display=swap" rel="stylesheet">


    @yield('extra-head')
</head>
<body class="@yield('body-class')">

    @include('frontend.partials.header')

    

    <!-- Page content -->
    @yield('page-content')

    @include('frontend.partials.youtube-subscribe')

    @include('frontend.partials.footer')

    <!-- Scripts -->
    {{-- Cloudflare Email Decode (keep as-is) --}}
    <script data-cfasync="false"
            src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>

    <!-- jQuery -->
    <script src="{{ asset('front-end/html/assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('front-end/html/assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- WOW JS -->
    <script src="{{ asset('front-end/html/assets/js/wow.min.js') }}"></script>

    <!-- Slick Slider (already in your template assets) -->
    <script src="{{ asset('front-end/html/assets/plugins/slick/slick.min.js') }}"></script>
    <!-- Fancybox (for lightbox) -->
    <script src="{{ asset('front-end/html/assets/plugins/fancybox/jquery.fancybox.min.js') }}"></script>

    <!-- MeanMenu -->
    <script src="{{ asset('front-end/html/assets/js/jquery.meanmenu.min.js') }}"></script>

    <!-- Owl Carousel -->
    <script src="{{ asset('front-end/html/assets/plugins/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- Sticky Sidebar -->
    <script src="{{ asset('front-end/html/assets/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
    <script src="{{ asset('front-end/html/assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>

    <!-- Ion RangeSlider -->
    <script src="{{ asset('front-end/html/assets/plugins/ion-rangeslider/js/ion.rangeSlider.js') }}"></script>
    <script src="{{ asset('front-end/html/assets/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('front-end/html/assets/plugins/ion-rangeslider/js/custom-rangeslider.js') }}"></script>

    <!-- Datepicker -->
    <script src="{{ asset('front-end/html/assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('front-end/html/assets/js/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Cursor -->
    <script src="{{ asset('front-end/html/assets/js/cursor.js') }}"></script>

    <!-- Main Script -->
    <script src="{{ asset('front-end/html/assets/js/script.js') }}?v={{ time() }}"></script>

    {{-- Cloudflare Rocket Loader (optional in Laravel) --}}
    <script src="../cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js"
            defer></script>

    {{-- Cloudflare Insights (optional) --}}
    <script defer
            src="https://static.cloudflareinsights.com/beacon.min.js"
            crossorigin="anonymous"></script>


    @yield('extra-scripts')

    <!-- Floating Contact Buttons -->
    <div class="floating-buttons position-fixed bottom-0 end-0 m-4 d-flex flex-column gap-3" style="z-index: 9999;">
        <!-- WhatsApp Button -->
        <a href="https://wa.me/+255760096715?text=Hello!%20I'm%20interested%20in%20your%20tours.%20I'm%20currently%20viewing:%20{{ urlencode(request()->url()) }}" 
           target="_blank" 
           class="btn btn-success btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
           style="width: 60px; height: 60px; transition: all 0.3s;">
            <i class="bi bi-whatsapp"></i>
        </a>

        <!-- Email Button -->
        <a href="mailto:info@afrovertextours.com?subject=Inquiry%20from%20website&body=Hello,%20I'm%20interested%20in%20your%20services.%20I'm%20currently%20viewing:%20{{ urlencode(request()->url()) }}" 
           class="btn btn-primary btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
           style="width: 60px; height: 60px; transition: all 0.3s;">
            <i class="bi bi-envelope-fill"></i>
        </a>
    </div>

    <!-- Optional hover animation -->
    <style>
        .floating-buttons a {
            position: relative;
        }

        /* Pulsing indicator ring — draws attention to WhatsApp & Email */
        @keyframes fb-pulse {
            0%   { transform: scale(1);    opacity: 0.55; }
            70%  { transform: scale(1.7);  opacity: 0; }
            100% { transform: scale(1.7);  opacity: 0; }
        }

        .floating-buttons a::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: currentColor;
            animation: fb-pulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            pointer-events: none;
        }

        /* Ring colours match each button */
        .floating-buttons a[href^="https://wa.me"]::before { color: #25D366; }
        .floating-buttons a[href^="mailto"]::before        { color: #0d6efd; }

        .floating-buttons a:hover::before { animation-play-state: paused; opacity: 0; }

        @media (prefers-reduced-motion: reduce) {
            .floating-buttons a::before { animation: none; opacity: 0; }
        }

        .floating-buttons a:hover {
            transform: scale(1.15);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
        }
        
        @media (max-width: 768px) {
            .floating-buttons {
                flex-direction: row !important;
                bottom: 20px;
                right: 20px;
                gap: 15px;
            }
            .floating-buttons a {
                width: 50px !important;
                height: 50px !important;
            }
        }
    </style>
</body>
</html>