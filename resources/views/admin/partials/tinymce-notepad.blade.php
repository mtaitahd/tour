{{--
    The admin "notepad" — TinyMCE with every tool.

    Lives in one file because two places need the same editor and they must not
    drift apart: the layout's global tinymce.init() picks up every .tinymce-editor
    on every admin page (the destination form's Description, blog bodies, pages …),
    and the tour form's Overview asks for the same notepad plus its own Media
    Library button.

    Emits a bare JS object literal, so callers merge it into their own config:

        tinymce.init(Object.assign({ selector: '.tinymce-editor' }, @include('admin.partials.tinymce-notepad')));

    The options are unchanged from the layout's original inline config — this is a
    move, not a redesign. Note the Toolbar Editor (| ) and the media button are
    deliberately left out: they are per-instance, not part of the shared notepad.
--}}
{
    height: 360,
    menubar: 'file edit view insert format tools table help',
    plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount quickbars',
    toolbar:
        'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | ' +
        'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
        'link image media table | removeformat | code | help',
    block_formats:
        'Paragraph=p; ' +
        'Heading 1=h1; Heading 2=h2; Heading 3=h3; ' +
        'Heading 4=h4; Heading 5=h5; Heading 6=h6; ' +
        'Preformatted=pre',
    quickbars_selection_toolbar: 'bold italic underline | blocks forecolor backcolor | link image',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px; line-height:1.7; color:#333; } ' +
        'h1, h2, h3, h4, h5, h6 { margin-top:1.8em; margin-bottom:0.6em; } ' +
        'ul, ol { margin-left:1.5em; } ' +
        'img { max-width:100%; height:auto; }',
    extended_valid_elements: 'div[*],span[*]',
    valid_classes: {
        '*': 'row col col-xl-7 col-lg-7 col-md-8 col-md-12 col-md-6 col-md-4 mb-4 mb-lg-0 border-bottom bg-light text-gray-6 avatar avatar-lg rounded-circle d-flex align-items-center justify-content-center flex-wrap justify-content-between align-items-center text-center text-primary fs-24 bg-light-200 shadow-none card-body mb-3 mb-1 mb-0 fs-14 fw-medium me-2 me-3 mb-2'
    },
    valid_elements: '*[*]',
    paste_retain_style_properties: 'all',
    paste_data_images: true,
    image_caption: true,
    quickbars_insert_toolbar: false,
    branding: false
}
