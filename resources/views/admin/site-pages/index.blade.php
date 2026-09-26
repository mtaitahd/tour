@extends('admin.layouts.app')
@section('title', 'Site Information')

@section('content')
  <div class="pagetitle">
    <h1>Site Information</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item">CMS</li>
        <li class="breadcrumb-item active">Site Information</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show my-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show my-2" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
              <h5 class="card-title mb-0">Site Information Pages</h5>
              <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-files"></i> All Pages
              </a>
            </div>

            <p class="text-muted">
              These pages describe the site itself, so they are managed here rather than
              alongside ordinary content pages. They are linked directly from the header
              and footer, which is why they cannot be deleted &mdash; set a page to
              <strong>Draft</strong> to take it off the site.
            </p>

            <div class="table-responsive">
              <table class="table datatable align-middle">
                <thead>
                  <tr>
                    <th>Page</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($sitePages as $entry)
                    <tr>
                      <td>
                        <strong>{{ $entry['label'] }}</strong>
                        @if (! $entry['page'])
                          <span class="badge bg-warning text-dark ms-1">Not created yet</span>
                        @endif
                      </td>
                      <td><code>{{ $entry['slug'] }}</code></td>
                      <td>
                        @if (! $entry['page'])
                          <span class="text-muted">&mdash;</span>
                        @elseif ($entry['page']->status === 'published')
                          <span class="badge bg-success">Published</span>
                        @else
                          <span class="badge bg-secondary">Draft</span>
                        @endif
                      </td>
                      <td>
                        @if ($entry['page'] && $entry['page']->updated_at)
                          {{ $entry['page']->updated_at->format('d M Y') }}
                        @else
                          <span class="text-muted">&mdash;</span>
                        @endif
                      </td>
                      <td class="text-end">
                        @if ($entry['page'])
                          <a href="{{ route('page.show', $entry['page']->slug) }}" target="_blank"
                             rel="noopener" class="btn btn-info btn-sm" title="View on site">
                            <i class="bi bi-eye"></i>
                          </a>
                          <a href="{{ route('admin.site-pages.edit', $entry['slug']) }}"
                             class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                          </a>
                        @else
                          {{-- No row for this slug yet. Creating it is optional: the
                               screen stays useful without it, and a missing page
                               simply is not linked anywhere. --}}
                          <a href="{{ route('admin.pages.create') }}?title={{ urlencode($entry['label']) }}&slug={{ urlencode($entry['slug']) }}"
                             class="btn btn-sm btn-outline-success" title="Create this page">
                            <i class="bi bi-plus-circle"></i> Create
                          </a>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">
                        No site information pages are configured.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
