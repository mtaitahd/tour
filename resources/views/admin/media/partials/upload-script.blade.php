function initializeMediaUpload() {
    const form = document.getElementById('media-upload-form');
    if (!form || form.dataset.mediaUploadReady === 'true') return;
    form.dataset.mediaUploadReady = 'true';

    const modal = document.getElementById('uploadModal');
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('upload-file-input');
    const preview = document.getElementById('upload-preview');
    const count = document.getElementById('upload-selection-count');
    const submit = document.getElementById('upload-submit-btn');
    const spinner = document.getElementById('upload-submit-spinner');
    const label = document.getElementById('upload-submit-label');
    const progress = document.getElementById('upload-progress-wrap');
    const bar = document.getElementById('upload-progress-bar');
    const status = document.getElementById('upload-progress-text');
    const results = document.getElementById('upload-results');
    const category = document.getElementById('upload-category');
    const categorySearch = document.getElementById('upload-category-search');
    const categoryOptions = Array.from(category.options);
    let selected = [];
    let busy = false;
    let changed = false;
    let previewUrls = [];

    categorySearch.addEventListener('input', function () {
        const term = this.value.trim().toLocaleLowerCase();
        const matches = categoryOptions.filter(option => !option.value || option.textContent.toLocaleLowerCase().includes(term));
        // Keep the chosen category visible even when the search changes.
        const chosen = categoryOptions.find(option => option.value === category.value);
        if (chosen && !matches.includes(chosen)) matches.unshift(chosen);
        const value = category.value;
        category.replaceChildren(...matches);
        category.value = value;
        document.getElementById('upload-category-empty').hidden = categoryOptions.some(option => option.value && option.textContent.toLocaleLowerCase().includes(term));
    });

    function renderPreview() {
        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];
        preview.replaceChildren();
        count.textContent = selected.length ? `${selected.length} images selected. ${selected.length > 12 ? 'Showing the first 12 previews.' : ''}` : '';
        selected.slice(0, 12).forEach((file, index) => {
            const col = document.createElement('div');
            col.className = 'col-3 position-relative';
            const image = document.createElement('img');
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            image.src = url;
            image.alt = file.name;
            image.title = file.name;
            image.className = 'img-thumbnail w-100';
            image.style.cssText = 'height:90px;object-fit:cover';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.disabled = busy;
            remove.addEventListener('click', () => {
                if (busy) return;
                selected.splice(index, 1);
                renderPreview();
            });
            col.append(image, remove);
            preview.append(col);
        });
        submit.disabled = busy || !selected.length;
    }

    function addFiles(files) {
        if (busy) return;
        selected.push(...Array.from(files));
        fileInput.value = '';
        renderPreview();
    }
    dropzone.addEventListener('click', () => { if (!busy) fileInput.click(); });
    fileInput.addEventListener('click', event => event.stopPropagation());
    fileInput.addEventListener('change', () => addFiles(fileInput.files));
    dropzone.addEventListener('dragover', event => { event.preventDefault(); });
    dropzone.addEventListener('drop', event => {
        event.preventDefault();
        addFiles(event.dataTransfer.files);
    });

    function message(text, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} py-2 mb-2`;
        alert.textContent = text;
        results.append(alert);
    }

    // One file per request avoids PHP max_file_uploads and combined post-size limits.
    function uploadFile(file, categoryId, onProgress) {
        return new Promise((resolve, reject) => {
            const data = new FormData();
            data.append('files[]', file);
            data.append('category_id', categoryId);
            data.append('_token', form.querySelector('[name="_token"]').value);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action);
            xhr.timeout = 180000;
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', event => {
                if (event.lengthComputable) onProgress(event.loaded / event.total);
            });
            xhr.onload = () => {
                let response;
                try { response = JSON.parse(xhr.responseText); }
                catch { reject(new Error('Unexpected server response.')); return; }
                if (xhr.status >= 200 && xhr.status < 300 && response.uploaded?.length === 1) {
                    resolve(response.uploaded[0]);
                } else {
                    const errors = Object.values(response.errors || {}).flat().join(' ');
                    reject(new Error(errors || response.message || `Upload failed (${xhr.status}).`));
                }
            };
            xhr.onerror = () => reject(new Error('Connection failed. Please check whether this image arrived before retrying.'));
            xhr.ontimeout = () => reject(new Error('Upload timed out. Please check whether this image arrived before retrying.'));
            xhr.onabort = () => reject(new Error('Upload interrupted.'));
            xhr.send(data);
        });
    }

    modal.addEventListener('hide.bs.modal', event => { if (busy) event.preventDefault(); });
    modal.addEventListener('hidden.bs.modal', () => {
        if (changed) window.location.reload();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (busy || !selected.length) return;
        const queue = selected.slice();
        const categoryId = category.value;
        const failed = [];
        let uploaded = 0;
        busy = true;
        results.replaceChildren();
        category.disabled = categorySearch.disabled = fileInput.disabled = true;
        modal.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => { button.disabled = true; });
        spinner.classList.remove('d-none');
        label.textContent = 'Uploading…';
        progress.classList.remove('d-none');
        bar.style.width = '0%';
        renderPreview();
        try {
            for (const [index, file] of queue.entries()) {
                status.textContent = `Uploading ${index + 1} of ${queue.length}: ${file.name} · ${uploaded} uploaded, ${failed.length} failed`;
                try {
                    await uploadFile(file, categoryId, fraction => {
                        bar.style.width = `${Math.round((index + fraction) / queue.length * 100)}%`;
                    });
                    uploaded++;
                    changed = true;
                } catch (error) {
                    failed.push(file);
                    message(`${file.name}: ${error.message}`, 'danger');
                }
                bar.style.width = `${Math.round((index + 1) / queue.length * 100)}%`;
            }
            status.textContent = `${uploaded} of ${queue.length} images uploaded. ${failed.length} failed.`;
            if (uploaded) {
                message(`${uploaded} images uploaded successfully. Close this window to refresh the library.`, 'success');
                const link = document.createElement('a');
                const url = new URL(window.location.href);
                url.search = '';
                if (categoryId) url.searchParams.set('category_id', categoryId);
                link.href = url.toString();
                link.className = 'btn btn-outline-primary btn-sm mb-2';
                link.textContent = 'View uploaded images';
                results.append(link);
            }
        } finally {
            selected = failed;
            busy = false;
            category.disabled = categorySearch.disabled = fileInput.disabled = false;
            modal.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => { button.disabled = false; });
            spinner.classList.add('d-none');
            label.textContent = failed.length ? 'Retry remaining files' : 'Upload Files';
            renderPreview();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMediaUpload, { once: true });
} else {
    initializeMediaUpload();
}
