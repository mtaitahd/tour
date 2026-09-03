<?php
    $currentPage = request()->segment(2) ?? '';
    $user = auth()->user();
    $can = fn (string $module) => $user->canAccess($module);
?>
<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo e($user->panelHome()); ?>">
        <div class="sidebar-brand-icon">
            <?php $brandLogo = \App\Models\Setting::logoUrl(); ?>
            <?php if($brandLogo): ?>
                <img src="<?php echo e($brandLogo); ?>" alt="Afro Vertex Tours" style="height:36px; width:auto;">
            <?php else: ?>
                <i class="fas fa-mountain" style="font-size:24px; color:var(--primary);"></i>
            <?php endif; ?>
        </div>
        <span class="sidebar-brand-text d-none d-lg-inline">Afro Vertex Tours</span>
    </a>

    <hr class="sidebar-divider my-0">

    
    <?php if($can('dashboard')): ?>
        <li class="nav-item <?php echo e($currentPage === 'dashboard' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('dashboard')); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if($can('pages') || $can('tours') || $can('users') || $can('blog')): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Content</div>
    <?php endif; ?>

    
    <?php if($can('pages')): ?>
        <li class="nav-item <?php echo e($currentPage === 'pages' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.pages.index')); ?>">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Edit About</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('tours')): ?>
        <li class="nav-item <?php echo e(in_array($currentPage, ['tour-packages', 'tour-categories']) ? 'active' : ''); ?>">
            <a class="nav-link <?php echo e(in_array($currentPage, ['tour-packages', 'tour-categories']) ? '' : 'collapsed'); ?>"
               href="#" data-bs-target="#tours-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-briefcase"></i>
                <span>Tours & Packages</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="tours-nav" class="collapse <?php echo e(in_array($currentPage, ['tour-packages', 'tour-categories']) ? 'show' : ''); ?>" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="<?php echo e(route('admin.tour-packages.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Tours</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.tour-categories.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
            </div>
        </li>
    <?php endif; ?>

    
    <?php if($can('users')): ?>
        <li class="nav-item <?php echo e($currentPage === 'users' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.users.index')); ?>">
                <i class="fas fa-fw fa-users"></i>
                <span>User Management</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('blog')): ?>
        <li class="nav-item <?php echo e(in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? 'active' : ''); ?>">
            <a class="nav-link <?php echo e(in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? '' : 'collapsed'); ?>"
               href="#" data-bs-target="#blog-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-newspaper"></i>
                <span>Blog Management</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="blog-nav" class="collapse <?php echo e(in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? 'show' : ''); ?>" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="<?php echo e(route('admin.blog-posts.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Posts</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.blog-categories.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.translated-blogs.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Translated Posts</span>
                </a>
            </div>
        </li>
    <?php endif; ?>

    <?php if($can('destinations') || $can('accommodations') || $can('testimonials') || $can('media')): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Management</div>
    <?php endif; ?>

    
    <?php if($can('destinations')): ?>
        <li class="nav-item <?php echo e($currentPage === 'destinations' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.destinations.index')); ?>">
                <i class="fas fa-fw fa-map-marker-alt"></i>
                <span>Destinations</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('accommodations')): ?>
        <li class="nav-item <?php echo e($currentPage === 'accommodations' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.accommodations.index')); ?>">
                <i class="fas fa-fw fa-hotel"></i>
                <span>Accommodations</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('testimonials')): ?>
        <li class="nav-item <?php echo e($currentPage === 'testimonials' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.testimonials.index')); ?>">
                <i class="fas fa-fw fa-quote-right"></i>
                <span>Testimonials</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('media')): ?>
        <li class="nav-item <?php echo e(in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? 'active' : ''); ?>">
            <a class="nav-link <?php echo e(in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? '' : 'collapsed'); ?>"
               href="#" data-bs-target="#media-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-images"></i>
                <span>Media Library</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="media-nav" class="collapse <?php echo e(in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? 'show' : ''); ?>" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="<?php echo e(route('admin.media.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Media</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.media.compression')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Compress Images</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.media.categories.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
                <a class="nav-link" href="<?php echo e(route('admin.media.tags.index')); ?>">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Tags</span>
                </a>
            </div>
        </li>
    <?php endif; ?>

    <?php if($can('inquiries')): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Operations</div>
    <?php endif; ?>

    
    <?php if($can('inquiries')): ?>
        <li class="nav-item <?php echo e($currentPage === 'inquiries' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.inquiries.index')); ?>">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Bookings / Inquiries</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('settings') || $can('profile')): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">System</div>
    <?php endif; ?>

    
    <?php if($can('settings')): ?>
        <li class="nav-item <?php echo e($currentPage === 'settings' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.settings.index')); ?>">
                <i class="fas fa-fw fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-item <?php echo e($currentPage === 'sitemap' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.sitemap.index')); ?>">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Sitemap</span>
            </a>
        </li>
    <?php endif; ?>

    
    <?php if($can('profile')): ?>
        <li class="nav-item <?php echo e($currentPage === 'profile' ? 'active' : ''); ?>">
            <a class="nav-link" href="<?php echo e(route('admin.profile.show')); ?>">
                <i class="fas fa-fw fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
    <?php endif; ?>

    
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('logout')); ?>"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
            <?php echo csrf_field(); ?>
        </form>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="version" id="version-tours">Afro Vertex Tours v1.0</div>
</ul><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\partials\sidebar.blade.php ENDPATH**/ ?>