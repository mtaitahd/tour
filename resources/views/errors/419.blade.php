@extends('admin.layouts.auth')

@section('title', 'Session Expired')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
  :root {
    --login-primary: #2563EB;
    --login-primary-hover: #1D4ED8;
    --login-accent: #38BDF8;
    --login-bg: #EFF6FF;
    --login-surface: #FFFFFF;
    --login-heading: #172554;
    --login-text: #475569;
    --login-text-muted: #94A3B8;
  }

  html, body {
    font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
  }

  body {
    margin: 0;
    color: var(--login-text);
    background: linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 50%, #F0F9FF 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }

  .login-card {
    background: var(--login-surface);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(37, 99, 235, 0.14);
    overflow: hidden;
    width: 100%;
    max-width: 460px;
  }

  .login-brand {
    background: linear-gradient(135deg, #1E3A8A, #2563EB);
    padding: 32px 28px;
    text-align: center;
  }
  .login-brand h4 {
    color: #fff;
    margin: 0;
    font-weight: 800;
    font-size: 22px;
    letter-spacing: 0.3px;
  }
  .login-brand__logo {
    max-width: 72px;
    margin-bottom: 12px;
    border-radius: 8px;
    background: rgba(255,255,255,0.12);
    padding: 6px;
  }

  .login-form {
    padding: 34px 30px;
    text-align: center;
  }
  .login-form__icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: #FEF3C7;
    color: #B45309;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
  }
  .login-form h5 {
    color: var(--login-heading);
    font-weight: 800;
    font-size: 24px;
    margin: 0 0 10px;
  }
  .login-form__sub {
    color: var(--login-text-muted);
    font-size: 14.5px;
    margin: 0 0 10px;
    line-height: 1.6;
  }
  .login-form__hint {
    color: var(--login-text-muted);
    font-size: 12.5px;
    margin: 22px 0 0;
  }
  .btn-brand {
    width: 100%;
    padding: 13px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    margin-top: 6px;
    transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
  }
  .btn-brand:hover {
    background: linear-gradient(135deg, #1D4ED8, #0EA5E9);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    color: #fff;
    text-decoration: none;
  }
  @media (max-width: 430px) {
    .login-form { padding: 26px 20px; }
  }
</style>
@endpush

@section('content')
<div class="login-card" role="main">
  <div class="login-brand">
    @php $brandLogo = \App\Models\Setting::logoUrl(); @endphp
    <img class="login-brand__logo"
         src="{{ $brandLogo ?: asset('public/assets/images/logo-light-icon.png') }}"
         alt="Afro Vertex Tours logo">
    <h4>Afro Vertex Tours</h4>
  </div>

  <div class="login-form">
    <div class="login-form__icon"><i class="fas fa-clock"></i></div>
    <h5>Session Expired</h5>
    <p class="login-form__sub">
      Your session has expired for security reasons.<br>
      Please log in again to continue.
    </p>
    <a class="btn-brand" href="{{ route('login') }}">
      <i class="fas fa-sign-in-alt"></i> Go to Login
    </a>
    <p class="login-form__hint">
      <i class="fas fa-shield-alt"></i> Auto-redirecting to login...
    </p>
  </div>
</div>

<script>
  setTimeout(function () {
    window.location.href = "{{ route('login') }}";
  }, 3000);
</script>
@endsection
