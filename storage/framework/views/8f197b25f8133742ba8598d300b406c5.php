<?php
    use App\Models\Setting;
?>
<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top" id="topbar">
    <div class="d-flex align-items-center topbar-left">
        
        <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3" title="Toggle sidebar">
            <i class="fas fa-bars" style="color:var(--heading); font-size:1.2rem;"></i>
        </button>

        
        <h5 class="m-0 d-none d-md-inline font-weight-bold" style="color:var(--heading)"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h5>
    </div>

    
    <div class="global-search position-relative d-none d-md-block" id="globalSearchWrap">
        <input type="text" id="globalSearch" class="form-control form-control-sm" placeholder="Search pages..." autocomplete="off" style="border-radius:20px; padding-left:36px; border-color:var(--border); background:#F8FAFC; font-size:14px; height:2.5rem;">
        <i class="fas fa-search search-icon" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px;"></i>
        <div id="globalSearchResults" class="search-results" style="position:absolute; top:100%; z-index:9999; display:none; border-radius:8px; overflow:hidden; max-height:320px; overflow-y:auto; background:#fff; box-shadow:0 5px 30px 0 rgba(82,63,105,.2); border:1px solid var(--border); width:100%;"></div>
    </div>

    
    <ul class="navbar-nav ms-auto">
        
        <li class="nav-item no-arrow mr-2 position-relative">
            <a class="nav-link" href="#" title="Notifications" style="padding-top:12px;">
                <i class="fas fa-bell" style="font-size:1.1rem; color:var(--text-muted);"></i>
            </a>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="user-chip">
                    <?php if(Auth::user()->hasAvatar()): ?>
                        <img src="<?php echo e(Auth::user()->avatarUrl('thumb') ?: Auth::user()->avatarUrl()); ?>" alt="Profile" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <span class="role-label"><?php echo e(auth()->user()->name ?? 'Admin'); ?></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <h6 class="dropdown-header" style="font-size:15px;font-weight:700;color:var(--heading)"><?php echo e(auth()->user()->name ?? 'Admin'); ?></h6>
                <div class="dropdown-header" style="margin-top:-8px;font-size:13px;font-weight:400;color:var(--text-muted)">
                    <?php echo e(auth()->user()->isSuperAdmin() ? 'Administrator' : 'Package Editor'); ?>

                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo e(route('admin.profile.show')); ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-muted"></i> My Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo e(route('admin.profile.show')); ?>">
                    <i class="fas fa-cog fa-sm fa-fw mr-2 text-muted"></i> Account Settings
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-muted"></i> Sign Out
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\partials\header.blade.php ENDPATH**/ ?>