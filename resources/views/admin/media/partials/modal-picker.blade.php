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
              @foreach ($categories ?? [] as $category)
                <option value="{{ $category->id }}">{{ $category->full_path }}</option>
                @foreach ($category->children as $child)
                  <option value="{{ $child->id }}">&nbsp;&nbsp;{{ $child->full_path }}</option>
                @endforeach
              @endforeach
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
    document.querySelectorAll('.media-picker-modal').forEach(function (modalEl) {
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

        function updateFooter() {
            confirmBtn.disabled = selected.size === 0;
            summaryEl.textContent = selected.size === 0
                ? 'No image selected'
                : (selected.size === 1
                    ? Array.from(selected.values())[0].name
                    : `${selected.size} images selected`);
        }

        function toggleSelect(image, cardEl) {
            if (!multiple) {
                selected.clear();
                resultsEl.querySelectorAll('.picker-item').forEach(el => el.classList.remove('border-primary', 'border-3'));
            }

            if (selected.has(image.id)) {
                selected.delete(image.id);
                cardEl.classList.remove('border-primary', 'border-3');
            } else {
                selected.set(image.id, image);
                cardEl.classList.add('border-primary', 'border-3');
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
                if (selected.has(image.id)) {
                    card.classList.add('border-primary', 'border-3');
                }

                const img = document.createElement('img');
                img.src = image.thumb_url;
                img.alt = image.alt_text || image.display_title;
                img.className = 'card-img-top';
                img.style.height = '90px';
                img.style.objectFit = 'cover';
                img.loading = 'lazy';

                card.appendChild(img);
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

            fetch(`{{ route('admin.media.picker.search') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    renderResults(data);
                    renderPagination(data.pagination);
                })
                .catch(() => {
                    resultsEl.innerHTML = '<div class="col-12 text-center py-5 text-danger">Could not load images. Please try again.</div>';
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => search(1), 350);
        });
        categorySelect.addEventListener('change', () => search(1));
        sortSelect.addEventListener('change', () => search(1));

        modalEl.addEventListener('show.bs.modal', () => search(1));

        confirmBtn.addEventListener('click', function () {
            const event = new CustomEvent('media-picker:selected', {
                detail: { pickerId: modalEl.id.replace('picker-', ''), images: Array.from(selected.values()) },
            });
            document.dispatchEvent(event);
            bootstrap.Modal.getInstance(modalEl).hide();
        });
    });
});
</script>
@endpush
@endonce
