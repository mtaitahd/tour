{{--
    Upload modal — supports single, multiple, and drag-and-drop upload (the brief's
    "Single Upload / Multiple Upload / Drag & Drop Upload / Bulk Upload" requirements),
    through the existing StoreMediaRequest 'files[]' field. The upload queue sends
    one image per request to avoid server limits on bulk uploads.
--}}
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Media</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="media-upload-form" action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div id="upload-dropzone" class="border-2 border-dashed rounded p-4 text-center bg-light mb-3" style="border-style: dashed; cursor: pointer;">
            <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; color: #6c757d;"></i>
            <p class="mb-1 mt-2">Drag and drop images here, or click to browse</p>
            <small class="text-muted">JPG, JPEG, PNG, or WEBP. Max 5MB per file.</small>
            <input type="file" name="files[]" id="upload-file-input" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none">
          </div>

          <p id="upload-selection-count" class="small text-muted mb-2" aria-live="polite"></p>
          <div id="upload-preview" class="row g-2 mb-3" style="max-height: 220px; overflow-y: auto;"></div>

          <div id="upload-progress-wrap" class="mb-3 d-none">
            <div class="progress" style="height: 6px;">
              <div id="upload-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
            </div>
            <p id="upload-progress-text" class="small mt-2 mb-0" role="status" aria-live="polite"></p>
          </div>

          <div id="upload-results" class="mb-3"></div>

          <div class="mb-3">
            <label class="form-label" for="upload-category">Category or subcategory (optional)</label>
            <input type="search" id="upload-category-search" class="form-control mb-2" placeholder="Find a category, mountain or camp…" aria-label="Find an upload category">
            <select name="category_id" id="upload-category" class="form-select" size="6">
              <option value="">No category</option>
              @include('admin.media.partials.category-options', ['categories' => $categories ?? collect(), 'selectedCategoryId' => request('category_id')])
            </select>
            <p id="upload-category-empty" class="small text-muted mt-1" hidden>No categories match.</p>
            <div class="form-text">Choose the exact folder for these images. All levels of subcategories are available.</div>
          </div>

          <button type="submit" class="btn btn-primary" id="upload-submit-btn" disabled>
            <span id="upload-submit-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            <span id="upload-submit-label">Upload Files</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script src="{{ asset('js/media-library-upload.js') }}" defer></script>
@endpush
@endonce
