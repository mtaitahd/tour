@extends('admin.layouts.app')
@section('title', 'Sitemap')

@section('content')
  <div class="pagetitle">
    <h1>Sitemap</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Sitemap</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Sitemap Status</h5>
          <p class="text-muted">
            The sitemap lets Google find and index your tours, destinations, pages and blog posts.
            It is generated automatically when you save a tour, page or blog post — or you can rebuild it manually below.
          </p>

          <dl class="row mb-0">
            <dt class="col-sm-4">Generated file</dt>
            <dd class="col-sm-8">
              @if($fileExists)
                <span class="badge text-bg-success">Exists</span>
                <code>{{ $filePath }}</code><br>
                <small class="text-muted">Last generated: {{ $lastMod }}</small>
              @else
                <span class="badge text-bg-warning">Not yet generated</span>
                <code>{{ $filePath }}</code>
              @endif
            </dd>

            <dt class="col-sm-4">Public URL</dt>
            <dd class="col-sm-8"><a href="{{ $fileUrl }}" target="_blank">{{ $fileUrl }}</a></dd>

            <dt class="col-sm-4">XML endpoint</dt>
            <dd class="col-sm-8"><a href="{{ url('/sitemap.xml') }}" target="_blank">{{ url('/sitemap.xml') }}</a>
              <small class="text-muted d-block">Served dynamically (respects the "Enable Sitemap.xml" setting).</small></dd>

            <dt class="col-sm-4">Total URLs</dt>
            <dd class="col-sm-8">{{ $counts['total'] }}</dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">URL Breakdown</h5>
          <table class="table table-sm">
            <tbody>
              <tr><td>Static pages + listings</td><td class="text-end">{{ $counts['total'] - ($counts['tours'] + $counts['categories'] + $counts['destinations'] + $counts['pages'] + $counts['blog']) }}</td></tr>
              <tr><td>Tours</td><td class="text-end">{{ $counts['tours'] }}</td></tr>
              <tr><td>Tour Categories</td><td class="text-end">{{ $counts['categories'] }}</td></tr>
              <tr><td>Destinations</td><td class="text-end">{{ $counts['destinations'] }}</td></tr>
              <tr><td>Pages</td><td class="text-end">{{ $counts['pages'] }}</td></tr>
              <tr><td>Blog posts</td><td class="text-end">{{ $counts['blog'] }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Actions</h5>
          <form method="POST" action="{{ route('admin.sitemap.generate') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-magic"></i> Generate Sitemap
            </button>
          </form>
          <div class="mt-2">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary w-100">Open SEO Settings</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
