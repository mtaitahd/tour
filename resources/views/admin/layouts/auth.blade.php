<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>@yield('title', 'Auth Page') - AVT</title>
  
  <!-- Favicons -->

  <link rel="apple-touch-icon" sizes="180x180" href="{{asset('asset/img/apple-touch-icon.png')}}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{asset('asset/img/favicon-32x32.png')}}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{asset('asset/img/favicon-16x16.pngfavicon-32x32.png')}}">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('asset/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('asset/vendor/simple-datatables/style.css') }}" rel="stylesheet">

  <!-- Main CSS -->
  <link href="{{ asset('asset/css/style.css') }}" rel="stylesheet">

  @stack('styles')
</head>

<body>

  <main>
    @yield('content')
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('asset/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('asset/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('asset/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('asset/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('asset/vendor/quill/quill.js') }}"></script>
  <script src="{{ asset('asset/vendor/simple-datatables/simple-datatables.js') }}"></script>
  <script src="{{ asset('asset/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('asset/vendor/php-email-form/validate.js') }}"></script>

  <!-- Main JS -->
  <script src="{{ asset('asset/js/main.js') }}"></script>

  @stack('scripts')
</body>

</html>
