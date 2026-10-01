{{--
    Media Library button for the notepad (TinyMCE) on the tour form's Overview.

    The notepad's own | image | tool only accepts a typed or pasted URL — it cannot
    reach the gallery — which is the whole reason this exists: an admin picking an
    image that is already in the Media Library should not have to go and find out
    its file path.

    The button is registered against TinyMCE's button registry and added to the
    Overview's toolbar by the page (admin.tour-packages.partials.tinymce-notepad
    usage in create/edit), so the same notepad on other pages is left exactly as it
    was. The picker is the same searchable modal every <x-media-picker> uses, under
    its own picker id so it cannot collide with the Quill editors' picker on the
    same page.
--}}
@include('admin.media.partials.modal-picker', [
    'pickerId'  => 'overview-media',
    'multiple'  => false,
    'categories' => app(\App\Services\MediaLibraryService::class)->categoryTree(),
])

<script>
(function () {
    const PICKER_ID = 'overview-media';

    // Escaped because this is assembled as markup: the picker already returns
    // library-relative URLs, but a file name is free text.
    const esc = function (value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    };

    // Registered once. The layout's notepad config is global, so a page could
    // include this partial more than once; TinyMCE throws on a duplicate
    // registration.
    if (!tinymce.ui.registry.getAll().mediagallery) {
        tinymce.ui.registry.addButton('mediagallery', {
            icon: 'picture',
            tooltip: 'Media Library',
            onAction: function () {
                // The editor whose toolbar was clicked is the active one.
                const editor = tinymce.activeEditor;
                if (!editor) return;

                const modalEl = document.getElementById('picker-' + PICKER_ID);
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            },
        });
    }

    document.addEventListener('media-picker:selected', function (event) {
        const detail = event.detail || {};
        if (detail.pickerId !== PICKER_ID) return;

        const image = (detail.images || [])[0];
        const editor = tinymce.activeEditor;
        if (!image || !editor || editor.isDestroyed()) return;

        // preview_url is the medium-webp conversion MediaPickerController already
        // resolves, so a tour page carries a web-sized image, not the original.
        editor.insertContent(
            '<img src="' + esc(image.preview_url) + '" alt="' + esc(image.alt_text || image.name) + '"' +
            ' style="max-width:100%; height:auto;">'
        );
        editor.focus();
    });
})();
</script>
