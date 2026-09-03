
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Media</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="media-upload-form" action="<?php echo e(route('admin.media.upload')); ?>" method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>

          <div id="upload-dropzone" class="border-2 border-dashed rounded p-4 text-center bg-light mb-3" style="border-style: dashed; cursor: pointer;">
            <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; color: #6c757d;"></i>
            <p class="mb-1 mt-2">Drag and drop images here, or click to browse</p>
            <small class="text-muted">JPG, JPEG, PNG, or WEBP. Max 5MB per file.</small>
            <input type="file" name="files[]" id="upload-file-input" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none">
          </div>

          <div id="upload-preview" class="row g-2 mb-3"></div>

          <div id="upload-progress-wrap" class="mb-3 d-none">
            <div class="progress" style="height: 6px;">
              <div id="upload-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
            </div>
          </div>

          <div id="upload-results" class="mb-3"></div>

          <div class="mb-3">
            <label class="form-label">Category (optional)</label>
            <select name="category_id" class="form-select">
              <option value="">No category</option>
              <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>"><?php echo e($category->full_path); ?></option>
                <?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($child->id); ?>">&nbsp;&nbsp;<?php echo e($child->full_path); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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

<?php if (! $__env->hasRenderedOnce('f7e2981a-b494-4da4-b561-c711e9fb9f0c')): $__env->markAsRenderedOnce('f7e2981a-b494-4da4-b561-c711e9fb9f0c'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('upload-file-input');
    const preview = document.getElementById('upload-preview');
    const submitBtn = document.getElementById('upload-submit-btn');
    const submitSpinner = document.getElementById('upload-submit-spinner');
    const submitLabel = document.getElementById('upload-submit-label');
    const progressWrap = document.getElementById('upload-progress-wrap');
    const progressBar = document.getElementById('upload-progress-bar');
    const resultsEl = document.getElementById('upload-results');
    const form = document.getElementById('media-upload-form');

    if (!dropzone) return;

    let selectedFiles = [];

    function renderPreview() {
        preview.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const col = document.createElement('div');
            col.className = 'col-3';
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'img-thumbnail';
            img.style.height = '90px';
            img.style.width = '100%';
            img.style.objectFit = 'cover';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncFileInput();
                renderPreview();
            });

            wrapper.appendChild(img);
            wrapper.appendChild(removeBtn);
            col.appendChild(wrapper);
            preview.appendChild(col);
        });

        submitBtn.disabled = selectedFiles.length === 0;
    }

    function syncFileInput() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    function addFiles(fileList) {
        Array.from(fileList).forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
            }
        });
        syncFileInput();
        renderPreview();
    }

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('bg-light-subtle', 'border-primary');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-primary');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
        addFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => addFiles(e.target.files));

    function setSubmitting(isSubmitting) {
        submitBtn.disabled = isSubmitting || selectedFiles.length === 0;
        submitSpinner.classList.toggle('d-none', !isSubmitting);
        submitLabel.textContent = isSubmitting ? 'Uploading\u2026' : 'Upload Files';
        progressWrap.classList.toggle('d-none', !isSubmitting);
        if (!isSubmitting) {
            progressBar.style.width = '0%';
        }
    }

    function renderResults(data) {
        resultsEl.innerHTML = '';

        const uploadedCount = (data.uploaded || []).length;
        const errorEntries = Object.entries(data.errors || {});

        if (uploadedCount > 0) {
            const success = document.createElement('div');
            success.className = 'alert alert-success py-2 mb-2';
            success.textContent = `${uploadedCount} file${uploadedCount === 1 ? '' : 's'} uploaded successfully.`;
            resultsEl.appendChild(success);
        }

        // Per-file errors, named explicitly rather than a single generic message —
        // someone uploading 10 files needs to know *which* 2 failed and why, not just
        // that "some files failed."
        errorEntries.forEach(([filename, messages]) => {
            const error = document.createElement('div');
            error.className = 'alert alert-danger py-2 mb-2';
            error.innerHTML = `<strong>${filename}:</strong> ${messages.join(' ')}`;
            resultsEl.appendChild(error);
        });
    }

    function prependToGrid(uploaded) {
        const grid = document.querySelector('.media-grid');
        if (!grid || !uploaded.length) return;

        // Remove the "no images found" empty state, if it's currently showing, since
        // there's now at least one image.
        const emptyState = grid.querySelector('.col-12');
        if (emptyState && emptyState.querySelector('.bi-images')) {
            emptyState.remove();
        }

        uploaded.forEach((image) => {
            const col = document.createElement('div');
            col.className = 'col-xl-3 col-lg-4 col-md-6';
            // Mirrors the server-rendered card in admin/media/index.blade.php exactly —
            // same structure, same delete/edit/view affordances. The delete button
            // works immediately with no extra binding needed: the grid's click handler
            // (see index.blade.php) uses event delegation rather than binding each
            // .remove-media button individually, so it already covers buttons added
            // after the page loaded.
            col.innerHTML = `
                <div class="card media-item shadow-sm position-relative h-100">
                    <a href="${image.show_url}" class="text-decoration-none">
                        <img src="${image.thumb_url}" class="card-img-top" alt="${image.name}" style="height: 160px; object-fit: cover;" loading="lazy">
                    </a>
                    <div class="card-body p-2">
                        <p class="mb-1 small text-truncate fw-medium text-body" title="${image.name}">${image.name}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size: 0.75rem;">${image.human_readable_size}</span>
                            <span class="badge bg-light text-muted" style="font-size: 0.7rem;">Unused</span>
                        </div>
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-media"
                            data-id="${image.id}"
                            data-url="${image.destroy_url}"
                            title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <a href="${image.edit_url}" class="btn btn-sm btn-light position-absolute top-0 start-0 m-2" title="Quick edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>`;
            grid.prepend(col);
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (selectedFiles.length === 0) return;

        setSubmitting(true);
        resultsEl.innerHTML = '';

        const formData = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                const percent = Math.round((event.loaded / event.total) * 100);
                progressBar.style.width = percent + '%';
            }
        });

        xhr.addEventListener('load', function () {
            setSubmitting(false);

            let data;
            try {
                data = JSON.parse(xhr.responseText);
            } catch (err) {
                resultsEl.innerHTML = '<div class="alert alert-danger py-2">Upload failed: unexpected response from the server.</div>';
                return;
            }

            // A 422 here means the request never reached the controller at all — it was
            // rejected by StoreMediaRequest's own validation (e.g. no files selected, or
            // every file failed the top-level 'files' array rule). That response has a
            // different shape ({message, errors: {field: [...]}}) than the controller's
            // own per-file success-path response ({success, uploaded, errors: {filename:
            // [...]}}), so it needs its own branch rather than being run through
            // renderResults(), which expects errors keyed by filename, not by field name.
            if (xhr.status === 422 && data.errors && !('uploaded' in data)) {
                const messages = Object.values(data.errors).flat();
                resultsEl.innerHTML = `<div class="alert alert-danger py-2">${messages.join(' ')}</div>`;
                return;
            }

            if (xhr.status >= 400) {
                resultsEl.innerHTML = `<div class="alert alert-danger py-2">${data.message || 'Upload failed. Please try again.'}</div>`;
                return;
            }

            renderResults(data);
            prependToGrid(data.uploaded || []);

            if ((data.uploaded || []).length > 0) {
                // Reset the form for another upload, but keep the modal open so the
                // person can see the results and the new thumbnails in the grid.
                selectedFiles = [];
                syncFileInput();
                renderPreview();
            }
        });

        xhr.addEventListener('error', function () {
            setSubmitting(false);
            resultsEl.innerHTML = '<div class="alert alert-danger py-2">Upload failed. Please check your connection and try again.</div>';
        });

        xhr.send(formData);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\media\partials\upload-modal.blade.php ENDPATH**/ ?>