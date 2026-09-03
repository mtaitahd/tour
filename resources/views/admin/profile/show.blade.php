@extends('admin.layouts.app')
@section('title', 'My Profile')

@section('content')
  <div class="pagetitle">
    <h1>My Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div>

  <section class="section profile">
    <div class="row">
      <div class="col-xl-4">
        <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
            @if(Auth::user()->hasAvatar())
              <img src="{{ Auth::user()->avatarUrl('thumb') ?: Auth::user()->avatarUrl() }}" alt="Profile" class="rounded-circle" >
            @else
              <img src="{{ asset('assets/img/default-avatar.png') }}" alt="Profile" class="rounded-circle" >
            @endif

            <h2 class="mt-3">{{ Auth::user()->name }}</h2>
            <h3>{{ Auth::user()->email }}</h3>
            <div class="social-links mt-2">
              <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
              <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
              <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
              <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-8">
        <div class="card">
          <div class="card-body pt-3">
            <!-- Tabs -->
            <ul class="nav nav-tabs nav-tabs-bordered">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
              </li>
            </ul>

            <div class="tab-content pt-2">
              <!-- Overview Tab -->
              <div class="tab-pane fade show active profile-overview" id="profile-overview">
                <h5 class="card-title">About</h5>
                <p class="small fst-italic">Admin / Manager at Afro-Vertex Tours & Safaris</p>

                <h5 class="card-title">Profile Details</h5>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label ">Full Name</div>
                  <div class="col-lg-9 col-md-8">{{ Auth::user()->name }}</div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Email</div>
                  <div class="col-lg-9 col-md-8">{{ Auth::user()->email }}</div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Profile Picture</label>
                    <div class="col-md-8 col-lg-9">
                        <form method="POST" action="{{ route('admin.profile.avatar') }}">
                            @csrf
                            <x-media-picker
                                name="avatar_image_id"
                                :selected="Auth::user()->avatar_image_id"
                                label="Select from Media Library"
                            />
                            <button type="submit" class="btn btn-outline-primary btn-sm mt-2">Save Profile Picture</button>
                        </form>
                    </div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Role</div>
                  <div class="col-lg-9 col-md-8">Administrator</div>
                </div>
              </div>

              <!-- Edit Profile Tab -->
              <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                <form method="POST" action="{{ route('admin.profile.update') }}">
                  @csrf

                  <div class="row mb-3">
                    <label for="name" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="name" type="text" class="form-control" id="name" value="{{ old('name', Auth::user()->name) }}" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="email" type="email" class="form-control" id="email" value="{{ old('email', Auth::user()->email) }}" required>
                    </div>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>

              <!-- Change Password Tab -->
              <div class="tab-pane fade pt-3" id="profile-change-password">
                  <form method="POST" action="{{ route('admin.profile.password') }}">
                      @csrf

                      <div class="row mb-3">
                          <label for="current-password" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                          <div class="col-md-8 col-lg-9">
                              <input name="current_password" type="password" class="form-control" id="current-password" required>
                              @error('current_password')
                                  <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label for="new-password" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                          <div class="col-md-8 col-lg-9">
                              <input name="password" type="password" class="form-control" id="new-password" required>
                              @error('password')
                                  <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>

                      <div class="row mb-3">
                          <label for="confirm-password" class="col-md-4 col-lg-3 col-form-label">Confirm New Password</label>
                          <div class="col-md-8 col-lg-9">
                              <input name="password_confirmation" type="password" class="form-control" id="confirm-password" required>
                              @error('password_confirmation')
                                  <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>

                      <div class="text-center">
                          <button type="submit" class="btn btn-primary">Change Password</button>
                      </div>
                  </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection