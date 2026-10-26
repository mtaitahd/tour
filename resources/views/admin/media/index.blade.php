@extends('admin.layouts.app')
@section('title', 'Media Library')

@section('content')
  <div class="pagetitle">
    <h1>Media Library</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Media Library</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      {{ session('warning') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="row">
      {{-- Sidebar: stats + category/tag filters --}}
      <div class="col-lg-3">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small mb-3">Library Stats</h6>
            <div class="d-flex justify-content-between small mb-2">
              <span>Total images</span>
              <strong>{{ number_format($stats['total_images']) }}</strong>
            </div>
            <div class="d-flex justify-content-between small mb-2">
              <span>Storage used</span>
              <strong>{{ $stats['total_size_bytes'] >= 1048576 ? number_format($stats['total_size_bytes'] / 1048576, 1) . ' MB' : number_format($stats['total_size_bytes'] / 1024, 1) . ' KB' }}</strong>
            </div>
            <div class="d-flex justify-content-between small">
              <span>Unused images</span>
              <strong class="{{ $stats['unused_count'] > 0 ? 'text-warning' : '' }}">{{ number_format($stats['unused_count']) }}</strong>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="card-title text-uppercase text-muted small mb-0">Categories</h6>
              <a href="{{ route('admin.media.categories.index') }}" class="small text-decoration-none">Manage</a>
            </div>

            @if ($categories->isEmpty())
              <p class="small text-muted mb-0">No categories yet.</p>
            @else
              <label for="category-search" class="visually-hidden">Find a category</label>
              <input type="search" id="category-search" class="form-control form-control-sm mb-2" placeholder="Find a category or camp…">
              <p class="small text-muted mb-2">Open a folder to see its subcategories.</p>
              <ul class="list-unstyled mb-0" id="media-category-tree" style="max-height: 420px; overflow-y: auto;">
                <li class="mb-1">
                  <a href="{{ route('admin.media.index', array_merge(request()->query(), ['category_id' => null, 'page' => null])) }}"
                     class="d-block px-2 py-1 rounded small text-decoration-none {{ empty($filters['category_id']) ? 'bg-primary text-white' : 'text-body' }}">
                    All images
                  </a>
                </li>
                @include('admin.media.partials.category-tree', ['categories' => $categories, 'activeCategoryId' => $filters['category_id'] ?? null])
              </ul>
              <p id="category-search-empty" class="small text-muted mt-2" hidden>No categories match.</p>
            @endif
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="card-title text-uppercase text-muted small mb-0">Tags</h6>
              <a href="{{ route('admin.media.tags.index') }}" class="small text-decoration-none">Manage</a>
            </div>

            @if ($tags->isEmpty())
              <p class="small text-muted mb-0">No tags yet.</p>
            @else
              <div class="d-flex flex-wrap gap-1">
                @foreach ($tags as $tag)
                  <a href="{{ route('admin.media.index', array_merge(request()->query(), ['tag_id' => $tag->id, 'page' => null])) }}"
                     class="badge text-decoration-none {{ (int) ($filters['tag_id'] ?? null) === $tag->id ? 'bg-primary' : 'bg-light text-dark' }}">
                    {{ $tag->name }} <span class="opacity-75">{{ $tag->media_count }}</span>
                  </a>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Main grid --}}
      <div class="col-lg-9">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
              <h5 class="card-title mb-0">All Uploaded Media ({{ $media->total() }})</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-cloud-upload"></i> Upload New Media
              </button>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <p class="small text-muted mb-0">Showing {{ $media->firstItem() ?? 0 }}–{{ $media->lastItem() ?? 0 }} of {{ $media->total() }} images. Parent categories include their subcategories.</p>
              @if ($media->hasPages())
                <nav aria-label="Image pages at top">{{ $media->onEachSide(1)->links('pagination::bootstrap-5') }}</nav>
              @endif
            </div>

            {{-- Search + sort --}}
            <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 mb-4">
              @if (!empty($filters['category_id']))
                <input type="hidden" name="category_id" value="{{ $filters['category_id'] }}">
              @endif
              @if (!empty($filters['tag_id']))
                <input type="hidden" name="tag_id" value="{{ $filters['tag_id'] }}">
              @endif

              <div class="col-md-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       class="form-control" placeholder="Search by title, filename, or tag&hellip;">
              </div>
              <div class="col-md-2">
                <label class="visually-hidden" for="uploaded-from">Uploaded from</label>
                <input type="date" name="uploaded_from" value="{{ $filters['uploaded_from'] ?? '' }}"
                       id="uploaded-from" class="form-control" title="Uploaded from">
              </div>
              <div class="col-md-3">
                <select name="sort" class="form-select">
                  <option value="newest" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest first</option>
                  <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                  <option value="name" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Name (A&ndash;Z)</option>
                  <option value="most_used" {{ ($filters['sort'] ?? '') === 'most_used' ? 'selected' : '' }}>Most used</option>
                  <option value="least_used" {{ ($filters['sort'] ?? '') === 'least_used' ? 'selected' : '' }}>Least used</option>
                </select>
              </div>
              <div class="col-md-2">
                <label for="media-per-page" class="visually-hidden">Images per page</label>
                <select name="per_page" id="media-per-page" class="form-select">
                  @foreach ([24, 48, 96] as $pageSize)
                    <option value="{{ $pageSize }}" @selected($media->perPage() === $pageSize)>{{ $pageSize }} / page</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-outline-secondary w-100" aria-label="Apply image filters"><i class="bi bi-search"></i></button>
              </div>
              <div class="col-md-4">
                <label for="uploaded-to" class="form-label small">Uploaded on or before (find old images)</label>
                <input type="date" name="uploaded_to" id="uploaded-to" value="{{ $filters['uploaded_to'] ?? '' }}" class="form-control">
              </div>
            </form>

            @if (($filters['search'] ?? null) || ($filters['category_id'] ?? null) || ($filters['tag_id'] ?? null) || ($filters['uploaded_from'] ?? null) || ($filters['uploaded_to'] ?? null))
              <div class="mb-3">
                <a href="{{ route('admin.media.index') }}" class="small text-decoration-none">
                  <i class="bi bi-x-circle"></i> Clear filters
                </a>
              </div>
            @endif

            @if ($media->isNotEmpty())
              <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                  <label class="form-check-label"><input type="checkbox" id="select-page-media" class="form-check-input me-1"> Select images on this page only</label>
                  <span id="selected-media-count" class="small text-muted" aria-live="polite">0 selected</span>
                  <button type="button" id="delete-selected-media" class="btn btn-outline-danger btn-sm" disabled>Delete selected</button>
                </div>
                @if (auth()->user()?->isSuperAdmin())
                  <label class="form-check-label small mt-2"><input type="checkbox" id="include-used-media" class="form-check-input me-1"> Also delete selected images that are in use</label>
                  <p class="small text-muted mt-1 mb-0">Permanent deletion removes the files. Blog posts, tours or pages using them may lose their images. Only selected images are deleted.</p>
                @endif
                <div id="bulk-delete-results" class="small mt-2" role="status" aria-live="polite"></div>
              </div>
            @endif

            {{-- Media Grid --}}
            <div class="row g-3 media-grid">
              @forelse ($media as $item)
                <div class="col-xl-3 col-lg-4 col-md-6">
                  <div class="card media-item shadow-sm position-relative h-100">
                    <label class="position-absolute bottom-0 end-0 m-2 bg-white rounded p-1" style="z-index: 2;">
                      <input type="checkbox" class="form-check-input m-0 select-media" value="{{ $item->id }}" data-url="{{ route('admin.media.destroy.ajax', $item) }}" data-name="{{ $item->name }}" data-used="{{ $item->usage_count > 0 ? '1' : '0' }}" aria-label="Select {{ $item->name }}">
                    </label>
                    <a href="{{ route('admin.media.show', $item) }}" class="text-decoration-none">
                      <img src="{{ $item->getUrl('thumb-webp') ?: $item->getUrl('thumb') ?: $item->getUrl() }}"
                           class="card-img-top" alt="{{ $item->meta?->alt_text ?: $item->name }}"
                           style="height: 160px; object-fit: cover;" loading="lazy">
                    </a>
                    <div class="card-body p-2">
                      <p class="mb-1 small text-truncate fw-medium text-body" title="{{ $item->display_title }}">
                        {{ $item->display_title }}
                      </p>
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $item->human_readable_size }}</span>
                        @if ($item->usage_count > 0)
                          <span class="badge bg-light text-dark" style="font-size: 0.7rem;" title="Used in {{ $item->usage_count }} location(s)">
                            <i class="bi bi-link-45deg"></i> {{ $item->usage_count }}
                          </span>
                        @else
                          <span class="badge bg-light text-muted" style="font-size: 0.7rem;">Unused</span>
                        @endif
                      </div>
                      <p class="mb-0 mt-1 text-truncate text-muted" style="font-size: 0.7rem;" title="Uploaded by {{ $item->uploaded_by_name }}">
                        <i class="bi bi-person-up me-1"></i>{{ $item->uploaded_by_name }}
                      </p>
                      <p class="mb-0 mt-1 text-muted" style="font-size: 0.7rem;">Uploaded {{ $item->created_at?->format('d M Y') ?? 'date unavailable' }}</p>
                      @if ($item->categories->isNotEmpty())
                        <p class="mb-0 mt-1 text-truncate" style="font-size: 0.7rem;">
                          @foreach ($item->categories as $category)
                            <span class="text-muted">{{ $category->name }}</span>@if (!$loop->last), @endif
                          @endforeach
                        </p>
                      @endif
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-media"
                            data-id="{{ $item->id }}"
                            data-url="{{ route('admin.media.destroy.ajax', $item) }}"
                            title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                    <a href="{{ route('admin.media.edit', $item) }}"
                       class="btn btn-sm btn-light position-absolute top-0 start-0 m-2"
                       title="Quick edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </div>
              @empty
                <div class="col-12 text-center py-5">
                  <i class="bi bi-images" style="font-size: 2.5rem; color: #ccc;"></i>
                  <h5 class="mt-3">No images found</h5>
                  <p class="text-muted">
                    @if (($filters['search'] ?? null) || ($filters['category_id'] ?? null) || ($filters['tag_id'] ?? null))
                      Nothing matches these filters. <a href="{{ route('admin.media.index') }}">Clear filters</a> to see everything.
                    @else
                      Click "Upload New Media" to add your first image.
                    @endif
                  </p>
                </div>
              @endforelse
            </div>

            <div class="mt-4">
              {{ $media->links('pagination::bootstrap-5', ['size' => 'sm']) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @include('admin.media.partials.upload-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryTree = document.getElementById('media-category-tree');
    const categorySearch = document.getElementById('category-search');
    if (categoryTree && categorySearch) {
        const activeLink = categoryTree.querySelector('[data-category-link="{{ (int) ($filters['category_id'] ?? 0) }}"]');
        let ancestor = activeLink?.parentElement;
        while (ancestor && ancestor !== categoryTree) {
            if (ancestor.tagName === 'DETAILS') ancestor.open = true;
            ancestor = ancestor.parentElement;
        }
        let savedOpen = [];
        categorySearch.addEventListener('input', function () {
            const term = this.value.trim().toLocaleLowerCase();
            if (term && !savedOpen.length) {
                savedOpen = Array.from(categoryTree.querySelectorAll('details')).map(el => [el, el.open]);
            }
            const filterNode = (node, parentMatches = false) => {
                const matches = node.dataset.categoryName.toLocaleLowerCase().includes(term);
                const children = Array.from(node.querySelectorAll(':scope > details > div > ul > [data-category-node]'));
                const childMatches = children.map(child => filterNode(child, parentMatches || matches)).some(Boolean);
                const visible = !term || parentMatches || matches || childMatches;
                node.hidden = !visible;
                const branch = node.querySelector(':scope > details');
                if (branch && term) branch.open = visible;
                return visible;
            };
            const visible = Array.from(categoryTree.querySelectorAll(':scope > [data-category-node]')).map(node => filterNode(node)).some(Boolean);
            document.getElementById('category-search-empty').hidden = visible;
            if (!term) {
                savedOpen.forEach(([el, open]) => { el.open = open; });
                savedOpen = [];
            }
        });
    }
    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        document.body.appendChild(toastContainer);
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        setTimeout(() => {
            toast.hide();
            setTimeout(() => toastContainer.remove(), 500);
        }, 4000);
    }

    @include('admin.media.partials.delete-script')
});
</script>
@endpush
