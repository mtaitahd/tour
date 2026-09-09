@extends('admin.layouts.app')
@section('title', 'Mega Nav')

@section('content')
  <div class="pagetitle">
    <h1>All Mega Nav</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Mega Nav</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">Mega Nav Items</h5>
              <div>
                <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#addMegaNavModal">
                  <i class="bi bi-plus-circle"></i> Add Mega Nav
                </button>
                <a href="{{ route('admin.mega-nav.create') }}" class="btn btn-primary btn-sm">
                  <i class="bi bi-plus-circle"></i> Add From Page
                </a>
              </div>
            </div>

            <div class="alert alert-light border small">
              <i class="bi bi-info-circle me-1"></i>
              Each item fills one row of the navigation's three-column mega menu:
              the <strong>title</strong> in the left panel, the <strong>heading</strong>
              + <strong>short description</strong> in the middle column, and the
              <strong>image</strong> in the right column. The item appears under the
              selected parent menu (Kilimanjaro / Safari / Day Trips) exactly as it
              renders on the homepage.
            </div>

            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Menu</th>
                  <th>Left Panel Title</th>
                  <th>Middle Heading</th>
                  <th>Image</th>
                  <th>Order</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($items as $item)
                  <tr>
                    <td>
                      <span class="badge bg-secondary">{{ $parents[$item->parent_menu_key]['label'] ?? $item->parent_menu_key }}</span>
                    </td>
                    <td>{{ $item->menu_label }}</td>
                    <td>{{ $item->heading ?: $item->menu_label }}</td>
                    <td>
                      @if ($item->image)
                        <img src="{{ $item->image->getUrl('thumb-webp') ?: $item->image->getUrl() }}"
                             alt="" class="img-thumbnail" style="width: 70px; height: 50px; object-fit: cover;">
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>{{ $item->display_order }}</td>
                    <td>
                      @if ($item->is_active)
                        <span class="badge bg-success">Active</span>
                      @else
                        <span class="badge bg-secondary">Hidden</span>
                      @endif
                    </td>
                    <td class="text-center">
                      <div class="d-inline-flex gap-1">
                        <a href="{{ route('admin.mega-nav.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                          <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.mega-nav.destroy', $item) }}" method="POST"
                              onsubmit="return confirm('Remove this Mega Nav item ({{ addslashes($item->menu_label) }})? This cannot be undone.');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-center py-4">No Mega Nav items yet. Click <strong>Add Mega Nav</strong> above to populate the navigation menu.</td></tr>
                @endforelse
              </tbody>
            </table>

            {{ $items->links() }}
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Add Mega Nav Popup Modal --}}
  <div class="modal fade" id="addMegaNavModal" tabindex="-1" aria-labelledby="addMegaNavModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <form method="POST" action="{{ route('admin.mega-nav.store') }}" id="addMegaNavForm">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="addMegaNavModalLabel">Add Mega Nav Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Parent Menu <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <select name="parent_menu_key" class="form-select" required>
                  <option value="">— Select parent menu —</option>
                  @foreach ($parents as $key => $def)
                    <option value="{{ $key }}" {{ old('parent_menu_key') === $key ? 'selected' : '' }}>
                      {{ $def['label'] }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">The top-level menu this item appears under (Kilimanjaro / Safari / Day Trips).</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Left Panel Title <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <input type="text" name="menu_label" class="form-control" maxlength="120" required
                       placeholder="e.g. 4-Day Tanzania Safari"
                       value="{{ old('menu_label') }}">
                <small class="text-muted">Shown in the left column of the mega menu.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Middle Heading <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <input type="text" name="heading" class="form-control" maxlength="255" required
                       placeholder="e.g. 4-Day Tanzania Safari"
                       value="{{ old('heading') }}">
                <small class="text-muted">The heading shown at the top of the middle column.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Short Description <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <textarea name="short_description" class="form-control" rows="3" maxlength="500" required
                          placeholder="A short description shown in the middle column.">{{ old('short_description') }}</textarea>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Link URL <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <input type="url" name="button_url_override" class="form-control" maxlength="2048" required
                       placeholder="https://www.afrovertex.com/tours/..."
                       value="{{ old('button_url_override') }}">
                <small class="text-muted">Where this item links to, e.g. a tour page.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">CTA Button Label</label>
              <div class="col-sm-10">
                <input type="text" name="button_label" class="form-control" maxlength="120"
                       placeholder="e.g. View This Tour"
                       value="{{ old('button_label') }}">
                <small class="text-muted">Defaults to "Read More" if left blank.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Image <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <x-media-picker
                    name="image_id"
                    :selected="old('image_id')"
                    label="Select Mega Nav Image"
                />
                <small class="text-muted d-block mt-1">Large image shown in the right column of the mega menu.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Badge</label>
              <div class="col-sm-10">
                <input type="text" name="badge_text" class="form-control" maxlength="40"
                       placeholder="e.g. Popular, New, Free PDF"
                       value="{{ old('badge_text') }}">
                <small class="text-muted">Optional small pill shown next to the title.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Display Order</label>
              <div class="col-sm-10">
                <input type="number" name="display_order" class="form-control w-25" min="0" max="9999"
                       value="{{ old('display_order', 0) }}">
                <small>Lower number = appears higher in the menu.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label"></label>
              <div class="col-sm-10">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_active" value="1"
                         id="add_mega_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                  <label class="form-check-label" for="add_mega_active">Show in Mega Menu</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle"></i> Add Mega Nav Item
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  (function () {
    // Re-open the Add Mega Nav popup when a validation error redirects back here
    // so the admin sees the errors inside the form instead of a blank page state.
    @if ($errors->any())
      document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('addMegaNavModal')).show();
      });
    @endif

    // Reset the popup so each open starts with a clean form.
    document.addEventListener('DOMContentLoaded', function () {
      const modalEl = document.getElementById('addMegaNavModal');
      if (!modalEl) return;
      modalEl.addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('addMegaNavForm');
        if (form) form.reset();
        const input = modalEl.querySelector('.media-picker-input');
        if (input) input.value = '';
        const preview = modalEl.querySelector('.media-picker-preview');
        if (preview) preview.innerHTML = '';
      });
    });
  })();
</script>
@endpush