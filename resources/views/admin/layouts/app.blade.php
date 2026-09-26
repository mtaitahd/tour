<!DOCTYPE html>
<html lang="en">
@php $modalView = request()->boolean('modal'); @endphp
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin Dashboard') - Afro Vertex Tours</title>

<link rel="apple-touch-icon" sizes="180x180" href="{{asset('asset/img/apple-touch-icon.png')}}">
<link rel="icon" type="image/png" sizes="32x32" href="{{asset('asset/img/favicon-32x32.png')}}">
<link rel="icon" type="image/png" sizes="16x16" href="{{asset('asset/img/favicon-16x16.png')}}">

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="{{ asset('asset/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('asset/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('asset/css/style.css') }}" rel="stylesheet">
<script src="{{ asset('asset/js/tinymce/tinymce.min.js') }}?v=6"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

@stack('styles')
</head>
<body id="page-top" class="{{ $modalView ? 'modal-view' : '' }}">

<div id="wrapper">
  @if(! $modalView)
  <!-- Sidebar -->
  @include('admin.partials.sidebar')
  <!-- End Sidebar -->

  <!-- Sidebar Overlay (mobile) -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  @endif

  <!-- Content Wrapper -->
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      @if(! $modalView)
      <!-- TopBar -->
      @include('admin.partials.header')
      <!-- End TopBar -->
      @endif

      <!-- Page Content -->
      <div class="container-fluid {{ $modalView ? 'px-3 py-3' : '' }}" id="container-wrapper">
        @yield('content')

      @if(! $modalView)
      <!-- Footer (closes wrapper divs) -->
      @include('admin.partials.footer')
      @endif

<!-- Vendor JS -->
<script src="{{ asset('asset/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('asset/js/main.js') }}"></script>

<!-- Denied-module click handling: cards/links the user can see but has no
     permission for show a SweetAlert instead of navigating (pos_system-style). -->
<script>
document.addEventListener('click', function (e) {
  var link = e.target.closest('a[data-perm-denied]');
  if (!link) return;
  e.preventDefault();
  Swal.fire({
    icon: 'error',
    title: 'No Permission',
    html: 'You do not have permission to access <strong>' + (link.getAttribute('data-perm-label') || 'this area') + '</strong>.<br><small>Contact the administrator to grant it.</small>',
    confirmButtonText: 'OK',
    confirmButtonColor: '#4e73df'
  });
});
</script>

<!-- TinyMCE Initialization Script (global for all editors) -->
<script>
tinymce.init({
selector: '.tinymce-editor',
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
branding: false,

setup: function (editor) {
editor.on('change', function () {
editor.save();
});
}
});
</script>
{{--
    Removed: a legacy itinerary/accommodation repeater script used to live here.
    It bound #add-itinerary-day and a document-delegated .add-accommodation
    handler on EVERY admin page, appending file-input-based rows. Both were
    superseded by the per-form scripts in tour-packages/{create,edit}.blade.php
    and the .add-accommodation delegation fired alongside the edit form's own
    handler, so a single click could append duplicate rows. The forms now own
    their own repeater logic end to end.
--}}
<script>
tinymce.init({
selector: '.tinymce-editor-mini',
height: 250,
menubar: false,
plugins: 'lists link code',
toolbar: 'undo redo | bold italic | bullist numlist | link | code',
content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
block_formats:
'Paragraph=p; ' +
'Heading 1=h1; Heading 2=h2; Heading 3=h3; ' +
'Heading 4=h4; Heading 5=h5; Heading 6=h6; ' +
'Preformatted=pre',
});
</script>
<script>
$(document).ready(function () {
$('#add-inclusion').click(function () {
let newInclusion = `
<div class="input-group mb-2 inclusion-item">
<input type="text" name="inclusions_items[]" class="form-control" placeholder="e.g. Airport transfers">
<button type="button" class="btn btn-outline-danger remove-inclusion">
<i class="bi bi-trash"></i>
</button>
</div>`;
$('#inclusions-repeater').append(newInclusion);
});
$(document).on('click', '.remove-inclusion', function () {
$(this).closest('.inclusion-item').remove();
});
$('#add-exclusion').click(function () {
let newExclusion = `
<div class="input-group mb-2 exclusion-item">
<input type="text" name="exclusions_items[]" class="form-control" placeholder="e.g. Visa fees">
<button type="button" class="btn btn-outline-danger remove-exclusion">
<i class="bi bi-trash"></i>
</button>
</div>`;
$('#exclusions-repeater').append(newExclusion);
});
$(document).on('click', '.remove-exclusion', function () {
$(this).closest('.exclusion-item').remove();
});
});
</script>

@stack('scripts')
</body>
</html>
