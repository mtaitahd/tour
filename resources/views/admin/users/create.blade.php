@extends('admin.layouts.app')
@section('title', 'Add Package Editor')

@section('content')
  @php
    $modules = config('panel.modules', []);
  @endphp
  <div class="pagetitle">
    <h1>Add Package Editor</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Package Editors</a></li>
        <li class="breadcrumb-item active">Add Editor</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mt-3">New Package Editor Account</h5>
            <p class="text-muted small">
              The editor signs in with this temporary password and is required to change it on first login.
            </p>

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
              @csrf

              <div class="col-md-12">
                <label for="name" class="form-label">Full name</label>
                <input id="name" type="text" name="name" class="form-control"
                       value="{{ old('name') }}" required autofocus>
              </div>

              <div class="col-md-12">
                <label for="email" class="form-label">Email address</label>
                <input id="email" type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required>
              </div>

              <div class="col-md-12">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
                  <option value="{{ \App\Models\User::ROLE_PACKAGE_EDITOR }}" @selected(old('role') === \App\Models\User::ROLE_PACKAGE_EDITOR)>Package Editor</option>
                  <option value="{{ \App\Models\User::ROLE_SUPER_ADMIN }}" @selected(old('role') === \App\Models\User::ROLE_SUPER_ADMIN)>Admin</option>
                </select>
                <div class="form-text">Admin always holds every module.</div>
              </div>

              <div class="col-md-6">
                <label for="password" class="form-label">Temporary password</label>
                <input id="password" type="password" name="password" class="form-control"
                       minlength="8" required>
              </div>

              <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirm temporary password</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="form-control" minlength="8" required>
              </div>

              <div class="col-12">
                <div class="card border">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-shield-check me-1"></i> Sidebar Permissions</h6>
                  </div>
                  <div class="card-body">
                    <p class="text-muted small">
                      Choose the modules this user may access. Only granted modules appear in their
                      admin sidebar; everything else is hidden (403). They can be adjusted later from
                      User Management.
                    </p>
                    <div id="createPermsNote" class="alert alert-info py-2 small" style="display:none;">
                      <i class="fas fa-info-circle"></i> Admin always has access to <strong>all</strong> modules.
                    </div>
                    <div class="row g-3" id="permsGrid">
                      @foreach ($modules as $key => $module)
                        @if ($key === 'profile') @continue @endif
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                   value="{{ $key }}" id="perm-{{ $key }}"
                                   @checked(in_array($key, old('permissions', [])))>
                            <label class="form-check-label" for="perm-{{ $key }}">
                              <i class="fas fa-fw {{ $module['icon'] }}"></i> {{ $module['label'] }}
                              <div class="small text-muted">{{ $module['description'] }}</div>
                            </label>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 text-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const grid = document.getElementById('permsGrid');
    const note = document.getElementById('createPermsNote');
    if (roleSelect && grid) {
      const update = () => {
        const isAdmin = roleSelect.value === '{{ \App\Models\User::ROLE_SUPER_ADMIN }}';
        grid.style.display = isAdmin ? 'none' : '';
        if (note) note.style.display = isAdmin ? 'block' : 'none';
      };
      roleSelect.addEventListener('change', update);
      update();
    }
  });
</script>
@endpush