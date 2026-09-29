@extends('frontend.layouts.app')

{{--
    Public 404 — "page not found".

    Laravel renders resources/views/errors/404.blade.php automatically for any
    NotFoundHttpException, so no controller or route change is needed. It reuses
    the normal public layout (header, footer, floating contact buttons) so the
    visitor is never dumped onto a bare framework page and always has the site
    navigation available to get them out of the dead end.
--}}

@php
    // The layout reads $meta for the <title> / description / OG tags. Public
    // controllers don't currently pass it, so without this the tab would just
    // read the app name on a page that should be clearly labelled.
    $meta = [
        'title'       => 'Page Not Found | Afro-Vertex Tours & Safaris',
        'description' => 'The page you were looking for could not be found. Browse our safari tours, climbs and destinations across East Africa.',
    ];
@endphp

@section('body-class', 'sfb-404-page')

@section('extra-head')
{{-- A 404 should never be indexed, and the URL it was requested at should not
     be treated as a canonical destination. --}}
<meta name="robots" content="noindex, nofollow">
<style>
  .sfb-404 {
    padding: clamp(60px, 10vw, 120px) 20px;
    text-align: center;
  }
  .sfb-404__code {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(90px, 18vw, 190px);
    font-weight: 700;
    line-height: .9;
    color: #c9a227;
    margin: 0;
    letter-spacing: -2px;
  }
  .sfb-404__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(26px, 4vw, 40px);
    font-weight: 600;
    color: #1f2937;
    margin: 8px 0 14px;
  }
  .sfb-404__text {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #6b7280;
    font-size: 16.5px;
    line-height: 1.7;
    max-width: 560px;
    margin: 0 auto 34px;
  }
  .sfb-404__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin-bottom: 42px;
  }
  .sfb-404__btn {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 600;
    padding: 13px 30px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    border: 1px solid transparent;
    transition: background .2s ease, color .2s ease, border-color .2s ease, transform .15s ease;
  }
  .sfb-404__btn--primary {
    background: #1f2937;
    color: #fff;
  }
  .sfb-404__btn--primary:hover {
    background: #c9a227;
    color: #1f2937;
    transform: translateY(-1px);
  }
  .sfb-404__btn--ghost {
    background: transparent;
    color: #1f2937;
    border-color: #d1d5db;
  }
  .sfb-404__btn--ghost:hover {
    border-color: #1f2937;
    background: #1f2937;
    color: #fff;
  }
  .sfb-404__links {
    border-top: 1px solid #e5e7eb;
    padding-top: 30px;
    max-width: 620px;
    margin: 0 auto;
  }
  .sfb-404__links-title {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 18px;
  }
  .sfb-404__links-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px 26px;
    justify-content: center;
  }
  .sfb-404__links-list a {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: #1f2937;
    text-decoration: none;
    border-bottom: 1px solid #d1d5db;
    padding-bottom: 2px;
  }
  .sfb-404__links-list a:hover {
    color: #c9a227;
    border-bottom-color: #c9a227;
  }
  @media (max-width: 575px) {
    .sfb-404__btn { width: 100%; justify-content: center; }
  }
</style>
@endsection

@section('page-content')
<main class="sfb-404" role="main">
  <p class="sfb-404__code">404</p>
  <h1 class="sfb-404__title">We couldn't find that page</h1>
  <p class="sfb-404__text">
    The page you were looking for may have been moved, renamed, or it never existed.
    The links below should get you back on your way.
  </p>

  <div class="sfb-404__actions">
    <a class="sfb-404__btn sfb-404__btn--primary" href="{{ route('home') }}">
      <i class="fas fa-home"></i> Back to Home
    </a>
    <a class="sfb-404__btn sfb-404__btn--ghost" href="{{ route('tours.index') }}">
      <i class="fas fa-route"></i> Browse Tours
    </a>
  </div>

  <div class="sfb-404__links">
    <p class="sfb-404__links-title">You might be looking for</p>
    <ul class="sfb-404__links-list">
      <li><a href="{{ route('destinations.index') }}">Destinations</a></li>
      <li><a href="{{ route('tours.index') }}">Tours &amp; Packages</a></li>
      <li><a href="{{ route('accommodations.index') }}">Accommodations</a></li>
      <li><a href="{{ route('blog.index') }}">Travel Blog</a></li>
      <li><a href="{{ route('home') }}">Homepage</a></li>
    </ul>
  </div>
</main>
@endsection
