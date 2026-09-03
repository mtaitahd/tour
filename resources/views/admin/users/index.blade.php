@extends('admin.layouts.app')
@section('title', 'User Management')

@section('content')
  @php
    $modules = config('panel.modules', []);
  @endphp
  <div class="pagetitle">
    <h1>User Management</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">User Management</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
              <h5 class="card-title mb-0">System Users</h5>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle"></i> Add User
              </button>
            </div>

            {{-- Add User Modal (pos_system-style) --}}
            <div class="modal fade" id="addUserModal" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <input type="hidden" name="window" value="add-user-modal">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <p class="text-muted small">
                        Create an account and assign the sidebar modules the user may access. A temporary
                        password is set below — the user must change it on first login.
                      </p>

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label for="add-name" class="form-label">Full name</label>
                          <input id="add-name" type="text" name="name" class="form-control"
                                 value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="col-md-6">
                          <label for="add-email" class="form-label">Email address</label>
                          <input id="add-email" type="email" name="email" class="form-control"
                                 value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                          <label for="add-role" class="form-label">Role</label>
                          <select id="add-role" name="role" class="form-select">
                            <option value="{{ \App\Models\User::ROLE_PACKAGE_EDITOR }}" @selected(old('role') === \App\Models\User::ROLE_PACKAGE_EDITOR)>Package Editor</option>
                            <option value="{{ \App\Models\User::ROLE_SUPER_ADMIN }}" @selected(old('role') === \App\Models\User::ROLE_SUPER_ADMIN)>Admin</option>
                          </select>
                          <div class="form-text">Admin always holds every module.</div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                          <label for="add-password" class="form-label">Temporary password</label>
                          <input id="add-password" type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="col-md-6">
                          <label for="add-password-confirm" class="form-label">Confirm password</label>
                          <input id="add-password-confirm" type="password" name="password_confirmation"
                                 class="form-control" minlength="8" required>
                        </div>

                        <div class="col-12">
                          <div class="border rounded p-3" id="permsEditorWrap">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                              <div>
                                <label class="fw-semibold mb-0"><i class="bi bi-shield-check me-1"></i> Sidebar Permissions</label>
                                <div class="small text-muted">Only module granted here appears in that user's admin sidebar.</div>
                              </div>
                              <div class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllPerms(this)">
                                  <i class="bi bi-check2-square"></i> Select / Clear All
                                </button>
                              </div>
                            </div>
                            <div id="dashPermsNote" class="alert alert-info py-2 small" style="display:none;">
                              <i class="fas fa-info-circle"></i> Admin always has access to <strong>all</strong> modules.
                            </div>
                            <div class="row g-3" id="permsGrid">
                              @foreach ($modules as $key => $module)
                                @if ($key === 'profile') @continue @endif
                                <div class="col-md-6">
                                  <div class="form-check">
                                    <input class="form-check-input add-perm-check" type="checkbox" name="permissions[]"
                                           value="{{ $key }}" id="add-perm-{{ $key }}" @checked(in_array($key, old('permissions', [])))>
                                    <label class="form-check-label" for="add-perm-{{ $key }}">
                                      <i class="fas fa-fw {{ $module['icon'] }}"></i> {{ $module['label'] }}
                                    </label>
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            @if ($errors->any() && old('window') === 'add-user-modal')
              <script>window.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('addUserModal')).show(); });</script>
            @endif

            @if (session('status'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            @endif
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

            <table class="table datatable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Permissions</th>
                  <th>Created By</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($users as $user)
                  @php $isSuper = $user->isSuperAdmin(); @endphp
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                      @if ($isSuper)
                        <span class="badge bg-dark"><i class="fas fa-crown"></i> Admin</span>
                      @else
                        <span class="badge bg-info">Package Editor</span>
                      @endif
                    </td>
                    <td>
                      @if ($user->is_suspended)
                        <span class="badge bg-danger">Suspended</span>
                      @else
                        <span class="badge bg-success">Active</span>
                      @endif
                    </td>
                    <td>
                      @php
                        $granted = $user->permissionKeys();
                      @endphp
                      @if ($isSuper)
                        <span class="badge bg-dark">All modules</span>
                      @elseif (empty($granted))
                        <span class="badge bg-secondary">None</span>
                      @else
                        @foreach ($granted as $key)
                          <span class="badge bg-primary" style="margin:1px;">{{ $modules[$key]['label'] ?? $key }}</span>
                        @endforeach
                      @endif
                    </td>
                    <td>{{ $user->createdBy?->name ?? '—' }}</td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        @if ($isSuper)
                          {{-- Super Admin accounts cannot be managed/suspended here. --}}
                          <span class="text-muted small mt-1">—</span>
                        @else
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#permissions{{ $user->id }}">
                          <i class="bi bi-shield-check"></i> Permissions
                        </button>

                        <form method="POST" action="{{ $user->is_suspended ? route('admin.users.activate', $user) : route('admin.users.suspend', $user) }}">
                          @csrf
                          @if ($user->is_suspended)
                            <button type="submit" class="btn btn-sm btn-success">Reactivate</button>
                          @else
                            <button type="submit" class="btn btn-sm btn-warning">Suspend</button>
                          @endif
                        </form>

                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#resetPassword{{ $user->id }}">
                          Reset Password
                        </button>

                        <div class="modal fade" id="permissions{{ $user->id }}" tabindex="-1">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <form method="POST" action="{{ route('admin.users.permissions', $user) }}">
                                @csrf
                                <div class="modal-header">
                                  <h5 class="modal-title">Permissions for {{ $user->name }}</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <p class="text-muted small">
                                    Grant sidebar modules for <strong>{{ $user->name }}</strong>. Only granted modules
                                    appear in their admin sidebar; undisplayed ones return 403. Super Admin always
                                    holds every module.
                                  </p>
                                  <div class="row g-3">
                                    @foreach ($modules as $key => $module)
                                      @if ($key === 'profile') @continue @endif
                                      <div class="col-md-6">
                                        <div class="form-check">
                                          <input class="form-check-input" type="checkbox" name="permissions[]"
                                                 value="{{ $key }}" id="perm-{{ $user->id }}-{{ $key }}"
                                                 @checked(in_array($key, $user->permissionKeys()))>
                                          <label class="form-check-label" for="perm-{{ $user->id }}-{{ $key }}">
                                            <i class="fas fa-fw {{ $module['icon'] }}"></i> {{ $module['label'] }}
                                            <div class="small text-muted">{{ $module['description'] }}</div>
                                          </label>
                                        </div>
                                      </div>
                                    @endforeach
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-primary">Save Permissions</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                        <div class="modal fade" id="resetPassword{{ $user->id }}" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                @csrf
                                <div class="modal-header">
                                  <h5 class="modal-title">Reset password for {{ $user->name }}</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <p class="text-muted small">They will be required to change this temporary password on next login.</p>
                                  <div class="mb-2">
                                    <label for="password-{{ $user->id }}" class="form-label">New temporary password</label>
                                    <input id="password-{{ $user->id }}" type="password" name="password"
                                           class="form-control" minlength="8" required>
                                  </div>
                                  <div class="mb-2">
                                    <label for="password-confirm-{{ $user->id }}" class="form-label">Confirm password</label>
                                    <input id="password-confirm-{{ $user->id }}" type="password"
                                           name="password_confirmation" class="form-control" minlength="8" required>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        @endif
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                      No system users yet — click <strong>Add User</strong> to create one.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  @push('scripts')
  <script>
    // Add-User modal: role select toggles the permission grid (pos_system-style).
    function addUserRoleChanged() {
      const role = document.getElementById('add-role').value;
      const isAdmin = role === '{{ \App\Models\User::ROLE_SUPER_ADMIN }}';
      document.getElementById('permsGrid').style.display = isAdmin ? 'none' : '';
      document.getElementById('dashPermsNote').style.display = isAdmin ? 'block' : 'none';
    }
    function toggleAllPerms(btn) {
      const boxes = document.querySelectorAll('.add-perm-check');
      if (!boxes.length) return;
      const allChecked = Array.prototype.every.call(boxes, (b) => b.checked);
      boxes.forEach((b) => { b.checked = !allChecked; });
    }
    document.addEventListener('DOMContentLoaded', function () {
      const roleSelect = document.getElementById('add-role');
      if (roleSelect) roleSelect.addEventListener('change', addUserRoleChanged);
      const modalEl = document.getElementById('addUserModal');
      if (modalEl) modalEl.addEventListener('shown.bs.modal', addUserRoleChanged);
    });
  </script>
  @endpush
@endsection