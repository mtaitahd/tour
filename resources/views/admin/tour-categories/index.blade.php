@extends('admin.layouts.app')
@php
    use App\Models\TourCategory;

    // Display order: the four dynamic form groups first, then the legacy SEO
    // pages, then anything else an admin may have created.
    $groupOrder = array_merge(
        array_keys(TourCategory::FORM_GROUPS),
        [TourCategory::TYPE_CATEGORY],
        $groups->keys()->diff(array_keys(TourCategory::FORM_GROUPS))->values()->all()
    );
    $groupOrder = array_values(array_unique($groupOrder));
@endphp
@section('title', 'Tour Categories')

@section('content')
  <div class="pagetitle">
    <h1>Tour Categories</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Tour Categories</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="mb-0">Category Groups</h5>
            <small class="text-muted">Each group feeds one dropdown on the Add / Edit Tour Package form. Listing Categories are the public tour pages (e.g. <code>/tanzania-tours</code>).</small>
          </div>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTourCategoryModal" onclick="presetAddType('category')">
            <i class="bi bi-plus-circle"></i> Add New Category
          </button>
        </div>

        <div class="row g-3">
          @foreach ($groupOrder as $type)
            @php $items = $groups->get($type, collect()); @endphp
            @if ($type !== TourCategory::TYPE_CATEGORY && $items->isEmpty()) @continue @endif
            <div class="col-md-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">
                      {{ TourCategory::typeLabel($type) }}
                      <span class="badge bg-secondary">{{ $items->count() }}</span>
                    </h6>
                  </div>

                  @if ($type === TourCategory::TYPE_CATEGORY)
                    <p class="text-muted small mb-2">Public listing pages — slug is the URL (e.g. <code>/tanzania-tours</code>).</p>
                  @endif

                  <ul class="list-group list-group-flush mb-3" style="max-height: 260px; overflow-y: auto;">
                    @forelse ($items as $category)
                      <li class="list-group-item px-0 d-flex justify-content-between align-items-center py-2">
                        <div class="me-2">
                          <div class="fw-semibold">
                            {{ $category->name }}
                            @if ($category->status !== TourCategory::STATUS_ACTIVE)
                              <span class="badge bg-warning text-dark">inactive</span>
                            @endif
                          </div>
                          <small class="text-muted">
                            <code>{{ $category->slug }}</code> · {{ $category->tour_packages_count }} tour(s)
                          </small>
                        </div>
                        <div class="text-nowrap">
                          <a href="#" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editTourCategoryModal" onclick="openEditTourCategory('{{ route('admin.tour-categories.edit', $category) }}')">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <form action="{{ route('admin.tour-categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this {{ strtolower(TourCategory::typeLabel($type)) }}? Tour packages will remain but lose this assignment.')">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </div>
                      </li>
                    @empty
                      <li class="list-group-item px-0 text-muted py-3">No options yet.</li>
                    @endforelse
                  </ul>

                  <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#addTourCategoryModal" onclick="presetAddType('{{ $type }}')">
                    <i class="bi bi-plus-circle"></i> {{ TourCategory::typeAddLabel($type) }}
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  {{-- Add New Category — popup modal (pos_system-style) --}}
  <div class="modal fade" id="addTourCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--primary);"></i><span id="addModalTitle">Add New Category</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.tour-categories.store') }}" id="addTourCategoryForm">
          @csrf
          <input type="hidden" name="window" value="add-tour-category-modal">

          <div class="modal-body">
            @if($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <div class="mb-3">
              <label class="form-label">Group <span class="text-danger">*</span></label>
              <select name="type" id="addModalType" class="form-select" onchange="syncAddModalTitle()">
                @foreach (TourCategory::TYPES as $typeOption)
                  <option value="{{ $typeOption }}" @selected(old('type', 'category') === $typeOption)>
                    {{ TourCategory::typeLabel($typeOption) }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted">Which dropdown this option belongs to on the tour form.</small>
            </div>

            <div class="mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name') }}" required placeholder="e.g. Tanzania, Africa, Private, 4 Days">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                     value="{{ old('slug') }}" placeholder="Auto-generated from name if left empty">
              <small class="text-muted" id="addModalSlugHelp">This becomes the public URL, e.g. "Tanzania Tours" &rarr; <code>/tanzania-tours</code>.</small>
              @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="active" @selected(old('status') === 'active' || old('status') === null)>Active</option>
                <option value="inactive" @selected(old('status') === 'inactive')>Inactive (hidden from pickers)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
              <small class="text-muted">Optional — not shown publicly yet.</small>
            </div>

            <div class="mb-1">
              <label class="form-label">Order</label>
              <input type="number" name="order" class="form-control w-25" value="{{ old('order', 999) }}" min="0">
              <small class="text-muted">Lower numbers appear first in the dropdown.</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Edit Tour Category — popup form in an iframe (full editor with sections/media-picker) --}}
  <div class="modal fade" id="editTourCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 1000px;">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title fw-semibold" id="editTourCategoryModalLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 position-relative" style="height: calc(100vh - 140px); overflow: hidden;">
          <iframe id="editTourCategoryIframe" src="about:blank" frameborder="0"
                  class="w-100 h-100" style="border: 0;"></iframe>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
function presetAddType(type) {
  var select = document.getElementById('addModalType');
  if (select) {
    select.value = type;
    syncAddModalTitle();
  }
}

function syncAddModalTitle() {
  var select = document.getElementById('addModalType');
  if (!select) return;
  var label = select.options[select.selectedIndex].text;
  document.getElementById('addModalTitle').textContent = 'Add ' + label.replace(/ \/ /g, ' / ');
  var help = document.getElementById('addModalSlugHelp');
  if (help) {
    if (select.value === 'category') {
      help.innerHTML = 'This becomes the public URL, e.g. "Tanzania Tours" &rarr; <code>/tanzania-tours</code>.';
    } else {
      help.textContent = 'Usually left empty — generated from the name.';
    }
  }
}
syncAddModalTitle();

function openEditTourCategory(url) {
  var frame = document.getElementById('editTourCategoryIframe');
  document.getElementById('editTourCategoryModalLabel').textContent = 'Edit Category';
  var sep = url.indexOf('?') === -1 ? '?' : '&';
  frame.src = url + sep + 'modal=1';
}

document.addEventListener('DOMContentLoaded', function () {
  var reopened = {{ old('window') === 'add-tour-category-modal' ? 'true' : 'false' }};
  if (reopened) {
    var modalEl = document.getElementById('addTourCategoryModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    @if (old('type'))
      presetAddType('{{ old('type') }}');
    @endif
  }
});

(function () {
  var modalEl = document.getElementById('editTourCategoryModal');
  var frame = document.getElementById('editTourCategoryIframe');
  var wasForm = false;

  modalEl.addEventListener('hidden.bs.modal', function () {
    wasForm = false;
    frame.src = 'about:blank';
  });

  frame.addEventListener('load', function () {
    try {
      var path = frame.contentWindow.location.pathname;
      var basePath = new URL("{{ route('admin.tour-categories.index') }}", window.location.origin).pathname;
      if (path === basePath && wasForm) {
        wasForm = false;
        var inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
        window.location.reload();
        return;
      }
      if (path !== basePath && path.indexOf(basePath + '/') === 0) {
        wasForm = true;
      } else {
        wasForm = false;
      }
    } catch (e) {
      wasForm = false;
    }
  });
})();
</script>
@endpush
