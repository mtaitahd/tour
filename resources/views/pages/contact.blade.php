@extends('frontend.layouts.app')

@php
    use App\Models\Setting;
@endphp

@section('page-content')
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar breadcrumb-bg-02 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="breadcrumb-title mb-2">
                        {{ $page->title ?? 'Contact Us' }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="isax isax-home5"></i></a>
                            </li>
                            <li class="breadcrumb-item">Pages</li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $page->title ?? 'Contact Us' }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumb -->

    <!-- Page Wrapper -->
    <div class="content">
        <div class="container">
            <div class="row align-items-center row-gap-4">
                <!-- Left column: Contact Info Blocks -->
                <!-- Left column: Contact Info Blocks -->
                <div class="col-xl-7 col-lg-7">
                    <div class="mb-4 mb-lg-0">
                        <div class="row">
                            <div class="col-md-8">
                                <h2 class="mb-3">
                                    {{ $page->contact_heading ?? 'Reach Out to Our Dedicated Support Team' }}
                                </h2>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-2">
                                {{ $page->contact_subheading ?? 'Our team is ready to help. Your satisfaction is our priority' }}
                            </h6>
                            <div class="prose text-gray-700">
                                {!! $page->content !!}
                            </div>
                        </div>

                        <!-- Contact Blocks -->
                        <div class="border-bottom mb-4">
                            <div class="d-flex align-items-center mb-4">
                                <span class="avatar avatar-lg rounded-circle bg-light text-gray-6 me-2">
                                    <i class="isax isax-sms5 fs-24"></i>
                                </span>
                                <div>
                                    <p class="fs-14 mb-0">Email Address</p>
                                    <h6 class="text-gray-6">
                                        <a href="mailto:{{ Setting::get('site_email', 'info@afrovertextours.com') }}">
                                            {{ Setting::get('site_email', 'info@afrovertextours.com') }}
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="border-bottom mb-4">
                            <div class="d-flex align-items-center mb-4">
                                <span class="avatar avatar-lg rounded-circle bg-light text-gray-6 me-2">
                                    <i class="isax isax-call-calling5 fs-24"></i>
                                </span>
                                <div>
                                    <p class="fs-14 mb-0">Phone / WhatsApp</p>
                                    <h6 class="text-gray-6">
                                        {{ Setting::get('footer_phone', Setting::get('footer_phone', '+255 712 345 678')) }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded-circle bg-light text-gray-6 me-2">
                                    <i class="isax isax-map-15 fs-24"></i>
                                </span>
                                <div>
                                    <p class="fs-14 mb-0">Our Location</p>
                                    <h6 class="text-gray-6">
                                        {{ Setting::get('footer_address', 'Moshi / Arusha, Tanzania') }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column: Contact Form -->
                <div class="col-xl-5 col-lg-5">
                    <div class="card bg-light-200 shadow-none mb-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <h2 class="mb-1">Get in Touch</h2>
                                <p class="text-gray-6 mb-1">
                                    How can we help you? Please write your query below
                                </p>
                            </div>

                            <!-- Contact Form -->
                            <form action="{{ route('contact.submit') }}" method="POST">
                                @csrf

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" name="first_name" class="form-control"
                                                   value="{{ old('first_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control"
                                                   value="{{ old('last_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control"
                                                   value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Phone / WhatsApp <span class="text-danger">*</span></label>
                                            <input type="tel" name="phone" class="form-control"
                                                   value="{{ old('phone') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Message <span class="text-danger">*</span></label>
                                            <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                    @error('g-recaptcha-response')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://www.google.com/recaptcha/api.js" async defer></script>

            <!-- Google Map -->
            <div class="map-grid mt-5">
                @if($page->contact_map_embed)
                    {{-- Admin-provided embed code (paste the full <iframe> from Google Maps' "Embed a map" option) --}}
                    {!! $page->contact_map_embed !!}
                @else
                    <iframe class="w-100" height="400"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3978.000000000000!2d37.343611314762!3d-3.350000000000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwMjEnMDAuMCJTIDM3wrAyMCcyNy4wIkU!5e0!3m2!1sen!2stz!4v1690000000000!5m2!1sen!2stz"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @endif
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection