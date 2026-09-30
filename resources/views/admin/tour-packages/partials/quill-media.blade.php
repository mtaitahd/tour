{{--
    Media Library button for the rich-text editors on the tour form.

    The tour form's rich text fields (Overview, itinerary day descriptions, extra
    sections) are Quill, and their toolbar had no image button at all — so the only
    way to get an image into a tour's details was to hand-type <img> HTML, which
    Quill then discards on the next edit because it re-serialises content from its
    own document model. TinyMCE's editors elsewhere in the admin do offer an image
    button, but only device-upload or a pasted URL, never the Media Library.

    This adds a "Media Library" button that opens the same picker modal every
    <x-media-picker> already uses (search, category filter, sort, pagination) and
    inserts the chosen image at the cursor as a real Quill image embed. The medium
    WebP conversion the picker already resolves is what gets inserted, so a tour
    page doesn't carry full-size originals.

    Included by tour-packages/{create,edit}.blade.php before their initQuill()
    script. It defines window.QuillMedia; the pages call QuillMedia.galleryButton()
    in their toolbar config and QuillMedia.attach() right after creating each
    instance.
--}}

{{-- The one shared picker modal, in single-select mode: this button inserts one image
     at a time, at the cursor. --}}
@include('admin.media.partials.modal-picker', [
    'pickerId'  => 'quill-media',
    'multiple'  => false,
    'categories' => app(\App\Services\MediaLibraryService::class)->categoryTree(),
])

<style>
  /* Quill's snow theme only styles buttons it knows about, so the custom one is
     given the same box as its neighbours (28x24, hover fill) to sit flush in the
     toolbar. The icon is inline SVG rather than an icon font so it cannot depend
     on a stylesheet that may or may not be loaded. */
  .ql-toolbar .ql-media-library {
    width: 28px;
    height: 24px;
    margin: 0 1px;
    padding: 0;
    border: none;
    border-radius: 2px;
    background: transparent;
    color: #444;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .ql-toolbar .ql-media-library:hover { background: #e8e8e8; }
  .ql-toolbar .ql-media-library svg { width: 18px; height: 18px; }
</style>

<script>
window.QuillMedia = (function () {
  const PICKER_ID = 'quill-media';

  // A tour form can hold several Quill editors (overview, one per itinerary day,
  // one per extra section), so the button needs to know which one the person was
  // last typing in. Tracked on focus/click/keystroke rather than assumed, because
  // the toolbar button itself takes the click.
  let active = null;

  function galleryButton() {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'ql-media-library';
    button.title = 'Insert image from Media Library';
    button.setAttribute('aria-label', 'Insert image from Media Library');
    button.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
      'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<rect x="3" y="3" width="18" height="18" rx="2"></rect>' +
      '<circle cx="8.5" cy="8.5" r="1.5"></circle>' +
      '<path d="M21 15l-5-5L5 21"></path></svg>';

    button.addEventListener('click', function () {
      if (!active) {
        window.alert('Click inside the text you want the image in first, then choose Media Library.');
        return;
      }
      const modalEl = document.getElementById('picker-' + PICKER_ID);
      if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    });

    return button;
  }

  /**
   * Register an instance and remember its hidden input, so an insert can be synced
   * back to the field the form actually submits.
   */
  function attach(quill, hiddenInput) {
    if (!active) active = { quill: quill, hiddenInput: hiddenInput };

    const remember = function () { active = { quill: quill, hiddenInput: hiddenInput }; };

    quill.root.addEventListener('focus', remember);
    quill.root.addEventListener('mouseup', remember);
    quill.root.addEventListener('keyup', remember);
  }

  document.addEventListener('media-picker:selected', function (event) {
    const detail = event.detail || {};
    if (detail.pickerId !== PICKER_ID || !active) return;

    const image = (detail.images || [])[0];
    if (!image) return;

    const quill = active.quill;

    // A brand new editor holds a single empty paragraph; inserting an embed at
    // index 0 there is not a valid position, so give it somewhere to land.
    if (quill.getLength() < 2) quill.setText('\n');

    let range = quill.getSelection();
    if (!range) {
      quill.focus();
      range = quill.getSelection(true);
    }
    if (!range) return;

    const index = range.index;

    quill.insertEmbed(index, 'image', {
      src: image.preview_url,
      alt: image.alt_text || image.name || '',
    });

    // Park the cursor after the image so typing continues underneath it rather
    // than before it.
    quill.setSelection(index + 1, 0);

    // The pages' own text-change handler normally does this; syncing here as well
    // keeps the submit value correct even if that handler is ever changed.
    if (active.hiddenInput && active.hiddenInput.length) {
      active.hiddenInput.val(quill.root.innerHTML);
    }
  });

  return { galleryButton: galleryButton, attach: attach };
})();
</script>
