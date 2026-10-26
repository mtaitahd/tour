@extends('admin.layouts.app')
@section('title', 'Media Categories')

@section('content')
  <div class="pagetitle">
    <h1>Media Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
        <li class="breadcrumb-item active">Categories</li>
      </ol>
    </nav>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <section class="section">
    <div class="row">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">All Categories</h5>
              <a href="{{ route('admin.media.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Category
              </a>
            </div>

            @if ($categories->isEmpty())
              <p class="text-muted mb-0">No categories yet. Create one to start organizing your media — for example "Destinations" with sub-categories like "Kilimanjaro" and "Serengeti".</p>
            @else
              <div class="mb-3">
                <label for="media-category-search" class="form-label">Search categories</label>
                <div class="input-group">
                  <input type="search" id="media-category-search" class="form-control" placeholder="Search a category, subcategory or camp…" aria-controls="media-category-list">
                  <button type="button" id="clear-category-search" class="btn btn-outline-secondary" disabled>Clear</button>
                </div>
                <p id="category-search-count" class="small text-muted mt-2 mb-0" role="status" aria-live="polite"></p>
              </div>
              <ul class="list-unstyled mb-0" id="media-category-list">
                @include('admin.media.categories.partials.tree-row', ['categories' => $categories, 'depth' => 0])
              </ul>
              <p id="no-category-results" class="text-muted py-3 mb-0" hidden>No categories match your search.</p>
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small">About Categories</h6>
            <p class="small text-muted mb-2">
              Categories are nested — a category can have sub-categories underneath it
              (for example, "Destinations" containing "Kilimanjaro", "Serengeti", and
              "Zanzibar"). An image can belong to more than one category.
            </p>
            <p class="small text-muted mb-0">
              Deleting a category removes the category itself and any sub-categories
              under it, but never deletes the images in it — they just lose that
              category assignment.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script data-media-category-search>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('media-category-search');
    if (!search) return;
    const rows = Array.from(document.querySelectorAll('#media-category-list [data-category-id]'));
    const byId = new Map(rows.map(row => [row.dataset.categoryId, row]));
    const clear = document.getElementById('clear-category-search');

    function filterCategories() {
        const terms = search.value.trim().toLocaleLowerCase().split(/\s+/).filter(Boolean);
        const visible = new Set();
        rows.forEach(row => {
            const path = row.dataset.categoryPath.toLocaleLowerCase();
            if (!terms.every(term => path.includes(term))) return;
            let current = row;
            while (current && !visible.has(current.dataset.categoryId)) {
                visible.add(current.dataset.categoryId);
                current = byId.get(current.dataset.parentId);
            }
        });
        rows.forEach(row => row.classList.toggle('d-none', !visible.has(row.dataset.categoryId)));
        document.getElementById('no-category-results').hidden = visible.size > 0;
        document.getElementById('category-search-count').textContent = terms.length
            ? `Showing ${visible.size} of ${rows.length} categories, including parent categories.`
            : `${rows.length} categories. Search includes all subcategories.`;
        clear.disabled = search.value.length === 0;
    }

    search.addEventListener('input', filterCategories);
    clear.addEventListener('click', function () {
        search.value = '';
        filterCategories();
        search.focus();
    });
    filterCategories();
});
</script>
@endpush
