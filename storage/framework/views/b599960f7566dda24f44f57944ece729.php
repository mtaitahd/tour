<?php $__env->startSection('title', 'Add Package Editor'); ?>

<?php $__env->startSection('content'); ?>
  <?php
    $modules = config('panel.modules', []);
  ?>
  <div class="pagetitle">
    <h1>Add Package Editor</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.users.index')); ?>">Package Editors</a></li>
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

            <form method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="row g-3">
              <?php echo csrf_field(); ?>

              <div class="col-md-12">
                <label for="name" class="form-label">Full name</label>
                <input id="name" type="text" name="name" class="form-control"
                       value="<?php echo e(old('name')); ?>" required autofocus>
              </div>

              <div class="col-md-12">
                <label for="email" class="form-label">Email address</label>
                <input id="email" type="email" name="email" class="form-control"
                       value="<?php echo e(old('email')); ?>" required>
              </div>

              <div class="col-md-12">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
                  <option value="<?php echo e(\App\Models\User::ROLE_PACKAGE_EDITOR); ?>" <?php if(old('role') === \App\Models\User::ROLE_PACKAGE_EDITOR): echo 'selected'; endif; ?>>Package Editor</option>
                  <option value="<?php echo e(\App\Models\User::ROLE_SUPER_ADMIN); ?>" <?php if(old('role') === \App\Models\User::ROLE_SUPER_ADMIN): echo 'selected'; endif; ?>>Admin</option>
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
                      <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($key === 'profile'): ?> <?php continue; ?> <?php endif; ?>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                   value="<?php echo e($key); ?>" id="perm-<?php echo e($key); ?>"
                                   <?php if(in_array($key, old('permissions', []))): echo 'checked'; endif; ?>>
                            <label class="form-check-label" for="perm-<?php echo e($key); ?>">
                              <i class="fas fa-fw <?php echo e($module['icon']); ?>"></i> <?php echo e($module['label']); ?>

                              <div class="small text-muted"><?php echo e($module['description']); ?></div>
                            </label>
                          </div>
                        </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 text-end">
                <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const grid = document.getElementById('permsGrid');
    const note = document.getElementById('createPermsNote');
    if (roleSelect && grid) {
      const update = () => {
        const isAdmin = roleSelect.value === '<?php echo e(\App\Models\User::ROLE_SUPER_ADMIN); ?>';
        grid.style.display = isAdmin ? 'none' : '';
        if (note) note.style.display = isAdmin ? 'block' : 'none';
      };
      roleSelect.addEventListener('change', update);
      update();
    }
  });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\users\create.blade.php ENDPATH**/ ?>