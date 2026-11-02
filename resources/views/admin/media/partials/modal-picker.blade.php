{{--
    Reusable Media Picker modal — the brief's explicitly-named
    admin/media/partials/modal-picker.blade.php deliverable.

    This is included once per picker instance via the <x-media-picker> Blade component
    (see app/View/Components/MediaPicker.php), which passes in a unique $pickerId so
    multiple pickers can exist on the same page (e.g. a TourPackage edit form picking a
    hero image AND several gallery images) without colliding.

    Expects:
      $pickerId   string  unique id for this picker instance (e.g. "hero_image_id")
      $inputName  string  the form field name the selected image id(s) should populate
      $multiple   bool    whether more than one image can be selected
      $selectedIds array  currently-selected media id(s), for pre-populating on open
--}}
<div class="modal fade media-picker-modal" id="picker-{{ $pickerId }}" tabindex="-1" data-multiple="{{ $multiple ? '1' : '0' }}">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select {{ $multiple ? 'Images' : 'an Image' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-5">
            <input type="text" class="form-control picker-search" placeholder="Search images&hellip;">
          </div>
          <div class="col-md-4">
            <select class="form-select picker-category-filter">
              <option value="">All categories</option>
              @include('admin.media.partials.category-options', ['categories' => $categories ?? collect()])
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select picker-sort">
              <option value="newest">Newest first</option>
              <option value="name">Name (A&ndash;Z)</option>
              <option value="most_used">Most used</option>
            </select>
          </div>
        </div>

        <div class="picker-results row g-2" style="min-height: 300px;">
          <div class="col-12 text-center py-5 picker-loading">
            <div class="spinner-border text-primary"></div>
          </div>
        </div>

        <nav class="picker-pagination mt-3 d-flex justify-content-center"></nav>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <div class="picker-selected-summary small text-muted">No image selected</div>
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary picker-confirm-btn" disabled>Use Selected</button>
        </div>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
/**
 * Drives every media picker instance on the page. One delegated set of listeners
 * handles all picker modals (matched by the .media-picker-modal class) rather than
 * binding separately per instance, so adding more pickers to a page never needs new JS.
 */
document.addEventListener('DOMContentLoaded', function () {
    const initializedPickers = new WeakSet();
    function initializePicker(modalEl) {
        if (initializedPickers.has(modalEl)) return;
        initializedPickers.add(modalEl);
        const multiple = modalEl.dataset.multiple === '1';
        const resultsEl = modalEl.querySelector('.picker-results');
        const paginationEl = modalEl.querySelector('.picker-pagination');
        const searchInput = modalEl.querySelector('.picker-search');
        const categorySelect = modalEl.querySelector('.picker-category-filter');
        const sortSelect = modalEl.querySelector('.picker-sort');
        const confirmBtn = modalEl.querySelector('.picker-confirm-btn');
        const summaryEl = modalEl.querySelector('.picker-selected-summary');

        let selected = new Map(); // id -> {id, name, thumb_url}
        let searchTimeout = null;
        let activeRequest = null;

        function updateFooter() {
            confirmBtn.disabled = !multiple && selected.size === 0;
            summaryEl.textContent = selected.size === 0
                ? 'No image selected'
                : (selected.size === 1
                    ? Array.from(selected.values())[0].name
                    : `${selected.size} images selected`);
        }

        function setCardSelected(cardEl, isSelected) {
            cardEl.classList.toggle('border-primary', isSelected);
            cardEl.classList.toggle('border-3', isSelected);
            const existingMark = cardEl.querySelector('.picker-selected-check');
            if (existingMark) existingMark.remove();
            if (!isSelected) return;

            const mark = document.createElement('span');
            mark.className = 'picker-selected-check';
            mark.setAttribute('role', 'img');
            mark.setAttribute('aria-label', 'Selected');
            mark.textContent = '✓';
            Object.assign(mark.style, {
                position: 'absolute', top: '6px', right: '6px', zIndex: '2',
                width: '28px', height: '28px', borderRadius: '50%',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                background: '#198754', color: '#fff', fontSize: '18px',
                fontWeight: '700', boxShadow: '0 1px 5px rgba(0,0,0,.35)',
            });
            cardEl.appendChild(mark);
        }

        function toggleSelect(image, cardEl) {
            if (!multiple) {
                selected.clear();
                resultsEl.querySelectorAll('.picker-item').forEach(el => setCardSelected(el, false));
            }

            if (selected.has(image.id)) {
                selected.delete(image.id);
                setCardSelected(cardEl, false);
            } else {
                selected.set(image.id, image);
                setCardSelected(cardEl, true);
            }

            updateFooter();
        }

        function renderResults(data) {
            resultsEl.innerHTML = '';

            if (data.images.length === 0) {
                resultsEl.innerHTML = '<div class="col-12 text-center py-5 text-muted">No images match these filters.</div>';
                return;
            }

            data.images.forEach(function (image) {
                const col = document.createElement('div');
                col.className = 'col-md-2 col-sm-3 col-4';

                const card = document.createElement('div');
                card.className = 'card picker-item border';
                card.style.cursor = 'pointer';
                card.style.position = 'relative';

                const img = document.createElement('img');
                img.src = image.thumb_url;
                img.alt = image.alt_text || image.display_title;
                img.className = 'card-img-top';
                img.style.height = '90px';
                img.style.objectFit = 'cover';
                img.loading = 'lazy';

                card.appendChild(img);
                setCardSelected(card, selected.has(image.id));
                card.addEventListener('click', () => toggleSelect(image, card));
                col.appendChild(card);
                resultsEl.appendChild(col);
            });
        }

        function renderPagination(pagination) {
            paginationEl.innerHTML = '';
            if (pagination.last_page <= 1) return;

            for (let page = 1; page <= pagination.last_page; page++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm ' + (page === pagination.current_page ? 'btn-primary' : 'btn-outline-secondary') + ' mx-1';
                btn.textContent = page;
                btn.addEventListener('click', () => search(page));
                paginationEl.appendChild(btn);
            }
        }

        function search(page = 1) {
            resultsEl.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

            const params = new URLSearchParams({
                search: searchInput.value,
                category_id: categorySelect.value,
                sort: sortSelect.value,
                page: page,
            });

            if (activeRequest) activeRequest.abort();
            const controller = new AbortController();
            activeRequest = controller;
            const timeout = setTimeout(() => controller.abort(), 25000);
            fetch(`{{ route('admin.media.picker.search', [], false) }}?${params.toString()}`, {
                credentials: 'same-origin', signal: controller.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(async response => {
                    if (!response.ok) throw new Error([401,419].includes(response.status) ? 'Session expired. Refresh and sign in again.' : `Images could not load (HTTP ${response.status}).`);
                    const data = await response.json();
                    if (!Array.isArray(data.images) || !data.pagination) throw new Error('Invalid image response. Refresh the page.');
                    return data;
                })
                .then(data => {
                    if (activeRequest !== controller) return;
                    renderResults(data);
                    renderPagination(data.pagination);
                })
                .catch(error => {
                    if (activeRequest !== controller) return;
                    resultsEl.replaceChildren();
                    const message = document.createElement('p');
                    message.className = 'col-12 text-center text-danger py-4';
                    message.textContent = error.name === 'AbortError' ? 'Image loading timed out. Please retry.' : error.message;
                    const retry = document.createElement('button');
                    retry.type = 'button'; retry.className = 'btn btn-outline-primary'; retry.textContent = 'Retry';
                    retry.addEventListener('click', () => search(page));
                    resultsEl.append(message, retry);
                })
                .finally(() => clearTimeout(timeout));
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => search(1), 350);
        });
        categorySelect.addEventListener('change', () => search(1));
        sortSelect.addEventListener('change', () => search(1));

        modalEl.addEventListener('show.bs.modal', function () {
            selected.clear();
            const pickerId = modalEl.id.replace('picker-', '');
            const widget = document.querySelector(`.media-picker[data-picker-id="${pickerId}"]`);
            widget?.querySelectorAll('.media-picker-thumb[data-id]').forEach(function (thumb) {
                const id = Number(thumb.dataset.id);
                const image = thumb.querySelector('img');
                selected.set(id, {
                    id: id,
                    name: thumb.dataset.name || image?.alt || 'Selected image',
                    thumb_url: thumb.dataset.thumbUrl || image?.src || '',
                });
            });
            updateFooter();
            search(1);
        });

        confirmBtn.addEventListener('click', function () {
            const event = new CustomEvent('media-picker:selected', {
                detail: { pickerId: modalEl.id.replace('picker-', ''), images: Array.from(selected.values()) },
            });
            document.dispatchEvent(event);
            bootstrap.Modal.getInstance(modalEl).hide();
        });
    }
    document.querySelectorAll('.media-picker-modal').forEach(initializePicker);
    new MutationObserver(function (mutations) {
        mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
            if (node.nodeType !== 1) return;
            if (node.matches('.media-picker-modal')) initializePicker(node);
            node.querySelectorAll('.media-picker-modal').forEach(initializePicker);
        }));
    }).observe(document.body, {childList: true, subtree: true});
});
</script>
@endpush
@endonce
