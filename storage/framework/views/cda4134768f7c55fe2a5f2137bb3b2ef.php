<?php $__env->startSection('title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
  <?php
    $modules = config('panel.modules', []);
  ?>
  <div class="pagetitle">
    <h1>User Management</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Home</a></li>
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

            
            <div class="modal fade" id="addUserModal" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="POST" action="<?php echo e(route('admin.users.store')); ?>">
                    <?php echo csrf_field(); ?>
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
                                 value="<?php echo e(old('name')); ?>" required autofocus>
                        </div>
                        <div class="col-md-6">
                          <label for="add-email" class="form-label">Email address</label>
                          <input id="add-email" type="email" name="email" class="form-control"
                                 value="<?php echo e(old('email')); ?>" required>
                        </div>
                        <div class="col-md-6">
                          <label for="add-role" class="form-label">Role</label>
                          <select id="add-role" name="role" class="form-select">
                            <option value="<?php echo e(\App\Models\User::ROLE_PACKAGE_EDITOR); ?>" <?php if(old('role') === \App\Models\User::ROLE_PACKAGE_EDITOR): echo 'selected'; endif; ?>>Package Editor</option>
                            <option value="<?php echo e(\App\Models\User::ROLE_SUPER_ADMIN); ?>" <?php if(old('role') === \App\Models\User::ROLE_SUPER_ADMIN): echo 'selected'; endif; ?>>Admin</option>
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
                              <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($key === 'profile'): ?> <?php continue; ?> <?php endif; ?>
                                <div class="col-md-6">
                                  <div class="form-check">
                                    <input class="form-check-input add-perm-check" type="checkbox" name="permissions[]"
                                           value="<?php echo e($key); ?>" id="add-perm-<?php echo e($key); ?>" <?php if(in_array($key, old('permissions', []))): echo 'checked'; endif; ?>>
                                    <label class="form-check-label" for="add-perm-<?php echo e($key); ?>">
                                      <i class="fas fa-fw <?php echo e($module['icon']); ?>"></i> <?php echo e($module['label']); ?>

                                    </label>
                                  </div>
                                </div>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

            <?php if($errors->any() && old('window') === 'add-user-modal'): ?>
              <script>window.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('addUserModal')).show(); });</script>
            <?php endif; ?>

            <?php if(session('status')): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('status')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

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
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php $isSuper = $user->isSuperAdmin(); ?>
                  <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($user->name); ?></td>
                    <td><?php echo e($user->email); ?></td>
                    <td>
                      <?php if($isSuper): ?>
                        <span class="badge bg-dark"><i class="fas fa-crown"></i> Admin</span>
                      <?php else: ?>
                        <span class="badge bg-info">Package Editor</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if($user->is_suspended): ?>
                        <span class="badge bg-danger">Suspended</span>
                      <?php else: ?>
                        <span class="badge bg-success">Active</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                        $granted = $user->permissionKeys();
                      ?>
                      <?php if($isSuper): ?>
                        <span class="badge bg-dark">All modules</span>
                      <?php elseif(empty($granted)): ?>
                        <span class="badge bg-secondary">None</span>
                      <?php else: ?>
                        <?php $__currentLoopData = $granted; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <span class="badge bg-primary" style="margin:1px;"><?php echo e($modules[$key]['label'] ?? $key); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      <?php endif; ?>
                    </td>
                    <td><?php echo e($user->createdBy?->name ?? '—'); ?></td>
                    <td><?php echo e($user->created_at?->format('d M Y')); ?></td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <?php if($isSuper): ?>
                          
                          <span class="text-muted small mt-1">—</span>
                        <?php else: ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#permissions<?php echo e($user->id); ?>">
                          <i class="bi bi-shield-check"></i> Permissions
                        </button>

                        <form method="POST" action="<?php echo e($user->is_suspended ? route('admin.users.activate', $user) : route('admin.users.suspend', $user)); ?>">
                          <?php echo csrf_field(); ?>
                          <?php if($user->is_suspended): ?>
                            <button type="submit" class="btn btn-sm btn-success">Reactivate</button>
                          <?php else: ?>
                            <button type="submit" class="btn btn-sm btn-warning">Suspend</button>
                          <?php endif; ?>
                        </form>

                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#resetPassword<?php echo e($user->id); ?>">
                          Reset Password
                        </button>

                        <div class="modal fade" id="permissions<?php echo e($user->id); ?>" tabindex="-1">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <form method="POST" action="<?php echo e(route('admin.users.permissions', $user)); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title">Permissions for <?php echo e($user->name); ?></h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <p class="text-muted small">
                                    Grant sidebar modules for <strong><?php echo e($user->name); ?></strong>. Only granted modules
                                    appear in their admin sidebar; undisplayed ones return 403. Super Admin always
                                    holds every module.
                                  </p>
                                  <div class="row g-3">
                                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                      <?php if($key === 'profile'): ?> <?php continue; ?> <?php endif; ?>
                                      <div class="col-md-6">
                                        <div class="form-check">
                                          <input class="form-check-input" type="checkbox" name="permissions[]"
                                                 value="<?php echo e($key); ?>" id="perm-<?php echo e($user->id); ?>-<?php echo e($key); ?>"
                                                 <?php if(in_array($key, $user->permissionKeys())): echo 'checked'; endif; ?>>
                                          <label class="form-check-label" for="perm-<?php echo e($user->id); ?>-<?php echo e($key); ?>">
                                            <i class="fas fa-fw <?php echo e($module['icon']); ?>"></i> <?php echo e($module['label']); ?>

                                            <div class="small text-muted"><?php echo e($module['description']); ?></div>
                                          </label>
                                        </div>
                                      </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

                        <div class="modal fade" id="resetPassword<?php echo e($user->id); ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST" action="<?php echo e(route('admin.users.reset-password', $user)); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title">Reset password for <?php echo e($user->name); ?></h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <p class="text-muted small">They will be required to change this temporary password on next login.</p>
                                  <div class="mb-2">
                                    <label for="password-<?php echo e($user->id); ?>" class="form-label">New temporary password</label>
                                    <input id="password-<?php echo e($user->id); ?>" type="password" name="password"
                                           class="form-control" minlength="8" required>
                                  </div>
                                  <div class="mb-2">
                                    <label for="password-confirm-<?php echo e($user->id); ?>" class="form-label">Confirm password</label>
                                    <input id="password-confirm-<?php echo e($user->id); ?>" type="password"
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
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                      No system users yet — click <strong>Add User</strong> to create one.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php $__env->startPush('scripts'); ?>
  <script>
    // Add-User modal: role select toggles the permission grid (pos_system-style).
    function addUserRoleChanged() {
      const role = document.getElementById('add-role').value;
      const isAdmin = role === '<?php echo e(\App\Models\User::ROLE_SUPER_ADMIN); ?>';
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
  <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\users\index.blade.php ENDPATH**/ ?>