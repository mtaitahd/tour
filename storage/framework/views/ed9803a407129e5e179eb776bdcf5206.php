<?php $__env->startSection('title', 'Change Your Password'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
  /* ============ Admin Forced Password Change - same split-panel design as Login ============ */
  :root {
    --login-primary: #2563EB;
    --login-primary-hover: #1D4ED8;
    --login-accent: #38BDF8;
    --login-bg: #EFF6FF;
    --login-dashboard-bg: #F8FAFC;
    --login-surface: #FFFFFF;
    --login-heading: #172554;
    --login-text: #475569;
    --login-text-muted: #94A3B8;
    --login-border: #E2E8F0;
  }

  html, body {
    font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
  }

  body {
    margin: 0;
    color: var(--login-text);
    background: linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 50%, #F0F9FF 100%);
    min-height: 100vh;
  }

  .login-wrapper {
    min-height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 50%, #F0F9FF 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    box-sizing: border-box;
  }

  .login-card {
    width: 100%;
    max-width: 800px;
    display: flex;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3), 0 8px 30px rgba(37, 99, 235, 0.15);
    background: var(--login-surface);
  }

  /* ---- Left branding panel ---- */
  .login-brand {
    width: 35%;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
    text-align: center;
    padding: 40px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
  }
  .login-brand__logo {
    max-width: 120px;
    width: 100%;
    height: auto;
    margin-bottom: 16px;
    object-fit: contain;
  }
  .login-brand h4 {
    font-weight: 800;
    margin-bottom: 8px;
    color: #fff;
    font-size: 20px;
  }
  .login-brand p {
    font-size: 13px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 22px;
    font-weight: 600;
  }
  .login-brand__foot {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    margin: 6px 0 0;
  }

  /* ---- Right form panel ---- */
  .login-form {
    width: 65%;
    background: var(--login-surface);
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    box-sizing: border-box;
  }
  .login-form h5 {
    font-size: 22px;
    font-weight: 800;
    color: var(--login-heading);
    text-align: center;
    margin: 0 0 8px;
  }
  .login-form .login-form__sub {
    text-align: center;
    font-size: 13.5px;
    color: var(--login-text-muted);
    margin: 0 0 26px;
  }

  .login-form label {
    color: var(--login-heading);
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .login-form .form-control {
    border: 2px solid var(--login-primary);
    border-left: 0;
    border-radius: 0;
    padding: 12px 15px;
    color: var(--login-primary);
    font-size: 15px;
    font-weight: 700;
    height: auto;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .login-form .form-control::placeholder {
    color: #A5B8D9;
    font-weight: 600;
  }
  .login-form .form-control:focus {
    border-color: var(--login-primary);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    outline: none;
  }
  .login-form .form-control.is-invalid {
    border-color: #dc3545;
  }
  .login-form .form-control.is-invalid:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
  }
  .login-form .input-group-text {
    background: var(--login-dashboard-bg);
    border: 2px solid var(--login-primary);
    border-right: 0;
    color: var(--login-primary);
    border-radius: 0;
    padding: 12px 18px;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-weight: 700;
  }
  .login-form .input-group-text i {
    font-size: 18px;
    color: var(--login-primary);
    font-weight: 700;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .login-form .invalid-feedback {
    font-size: 13px;
  }

  .login-form .login-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fdecec;
    color: #a63a2f;
    border: 1px solid #f5c6c2;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13.5px;
    margin-bottom: 20px;
  }

  .login-form .btn-brand {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .01em;
    color: #fff;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
    border: none;
    border-radius: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
    margin-top: 4px;
  }
  .login-form .btn-brand:hover {
    background: linear-gradient(135deg, #1D4ED8, #0EA5E9);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
  }
  .login-form .login-form__foot {
    text-align: center;
    font-size: 12.5px;
    color: var(--login-text-muted);
    margin-top: 24px;
  }

  .login-form .logout-link {
    display: inline-block;
    margin-top: 18px;
    width: 100%;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: var(--login-text-muted);
    text-decoration: none;
  }
  .login-form .logout-link:hover {
    color: var(--login-text);
    text-decoration: underline;
  }

  /* ---- Responsive ---- */
  @media (max-width: 768px) {
    .login-card { flex-direction: column; max-width: 95%; }
    .login-brand { width: 100%; padding: 25px 20px; }
    .login-form { width: 100%; padding: 26px 22px; }
    .login-brand__logo { max-width: 90px; }
  }
  @media (max-width: 430px) {
    .login-wrapper { padding: 16px; }
    .login-card { max-width: 100%; }
    .login-brand { padding: 20px 16px; }
    .login-form { padding: 24px 18px; }
    .login-form .form-control { padding: 11px 13px; font-size: 14px; }
    .login-form .btn-brand { padding: 11px; font-size: 15px; }
    .login-form h5 { font-size: 20px; }
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="login-wrapper" role="main">
  <div class="login-card">

    
    <div class="login-brand">
      <?php $brandLogo = \App\Models\Setting::logoUrl(); ?>
      <img class="login-brand__logo"
           src="<?php echo e($brandLogo ?: asset('public/assets/images/logo-light-icon.png')); ?>"
           alt="Afro Vertex Tours logo">
      <h4>Afro Vertex Tours</h4>
      <p>Choose a new password to continue.</p>
      <p class="login-brand__foot">&copy; <?php echo e(date('Y')); ?> Afro Vertex Tours. All rights reserved.</p>
    </div>

    
    <div class="login-form">
      <h5>Change Your Password</h5>
      <p class="login-form__sub">Your temporary password must be replaced before you can continue.</p>

      <?php if($errors->any()): ?>
        <div class="login-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <span>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php echo e($error); ?><?php if(!$loop->last): ?><br><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </span>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('forced-password-change')); ?>" novalidate>
        <?php echo csrf_field(); ?>

        <div class="form-group mb-3">
          <label for="current_password">Current (temporary) password</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
            </div>
            <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                   class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="Enter your temporary password" required autofocus>
          </div>
          <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group mb-3">
          <label for="password">New password</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="8"
                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="Enter a new password (min 8 characters)" required>
          </div>
          <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group mb-3">
          <label for="password_confirmation">Confirm new password</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                   class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="Re-enter the new password" required>
          </div>
          <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="btn-brand">
          <i class="fas fa-check-circle"></i> Save new password
        </button>
      </form>

      <form method="POST" action="<?php echo e(route('logout')); ?>" class="text-center">
        <?php echo csrf_field(); ?>
        <button type="submit" class="logout-link">Cancel and sign out</button>
      </form>

      <p class="login-form__foot">Protected area &middot; Afro Vertex Tours</p>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\auth\forced-password-change.blade.php ENDPATH**/ ?>