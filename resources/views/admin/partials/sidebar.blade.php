@php
    $currentPage = request()->segment(2) ?? '';
    $user = auth()->user();
    $can = fn (string $module) => $user->canAccess($module);
@endphp
<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    {{-- Brand --}}
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ $user->panelHome() }}">
        <div class="sidebar-brand-icon">
            @php $brandLogo = \App\Models\Setting::logoUrl(); @endphp
            @if($brandLogo)
                <img src="{{ $brandLogo }}" alt="Afro Vertex Tours" style="height:36px; width:auto;">
            @else
                <i class="fas fa-mountain" style="font-size:24px; color:var(--primary);"></i>
            @endif
        </div>
        <span class="sidebar-brand-text d-none d-lg-inline">Afro Vertex Tours</span>
    </a>

    <hr class="sidebar-divider my-0">

    {{-- Dashboard --}}
    @if($can('dashboard'))
        <li class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
    @endif

    @if($can('pages') || $can('tours') || $can('mega-nav') || $can('users') || $can('blog'))
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Content</div>
    @endif

    {{-- Static Pages --}}
    @if($can('pages'))
        <li class="nav-item {{ $currentPage === 'pages' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pages.index') }}">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Edit About</span>
            </a>
        </li>
    @endif

    {{-- Tours & Packages --}}
    @if($can('tours'))
        <li class="nav-item {{ in_array($currentPage, ['tour-packages', 'tour-categories']) ? 'active' : '' }}">
            <a class="nav-link {{ in_array($currentPage, ['tour-packages', 'tour-categories']) ? '' : 'collapsed' }}"
               href="#" data-bs-target="#tours-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-briefcase"></i>
                <span>Tours & Packages</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="tours-nav" class="collapse {{ in_array($currentPage, ['tour-packages', 'tour-categories']) ? 'show' : '' }}" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="{{ route('admin.tour-packages.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Tours</span>
                </a>
                <a class="nav-link" href="{{ route('admin.tour-categories.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
            </div>
        </li>
    @endif

    {{-- Mega Nav --}}
    @if($can('mega-nav'))
        @php
            $megaItems = \App\Models\NavigationMegaMenuItem::with(['image'])
                ->where('source_type', \App\Models\NavigationMegaMenuItem::SOURCE_CUSTOM)
                ->orderBy('parent_menu_key')
                ->orderBy('display_order')
                ->orderBy('menu_label')
                ->get();
            $megaParents = app(\App\Services\NavigationMegaMenuService::class)->parentDefinitions();
            $megaGrouped = $megaItems->groupBy(fn ($m) => $m->parent_menu_key);
        @endphp
        <li class="nav-item {{ $currentPage === 'mega-nav' ? 'active' : '' }}">
            <a class="nav-link {{ $currentPage === 'mega-nav' ? '' : 'collapsed' }}"
               href="#" data-bs-target="#mega-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-bars"></i>
                <span>Mega Nav</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="mega-nav" class="collapse {{ $currentPage === 'mega-nav' ? 'show' : '' }}" data-bs-parent="#accordionSidebar">
                @foreach($megaGrouped as $parentKey => $group)
                    <div class="sidebar-subheading">
                        {{ $megaParents[$parentKey]['label'] ?? $parentKey }}
                    </div>
                    @foreach($group as $megaItem)
                        <div class="d-flex align-items-center sidebar-mega-item">
                            <a class="nav-link" href="{{ route('admin.mega-nav.edit', $megaItem) }}">
                                <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                                <span class="text-truncate" title="{{ $megaItem->menu_label }}">{{ $megaItem->menu_label }}</span>
                            </a>
                            <form action="{{ route('admin.mega-nav.destroy', $megaItem) }}" method="POST"
                                  onsubmit="return confirm('Remove Mega Nav item ({{ addslashes($megaItem->menu_label) }})? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 mx-1 text-danger" title="Delete {{ $megaItem->menu_label }}">
                                    <i class="fas fa-trash" style="font-size:0.7rem;"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @endforeach
                <a class="nav-link" href="{{ route('admin.mega-nav.create') }}">
                    <i class="fas fa-fw fa-plus" style="font-size:0.6rem;"></i>
                    <span>Add New Item</span>
                </a>
            </div>
        </li>
    @endif

    {{-- User Management --}}
    @if($can('users'))
        <li class="nav-item {{ $currentPage === 'users' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>User Management</span>
            </a>
        </li>
    @endif

    {{-- Blog Management --}}
    @if($can('blog'))
        <li class="nav-item {{ in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? 'active' : '' }}">
            <a class="nav-link {{ in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? '' : 'collapsed' }}"
               href="#" data-bs-target="#blog-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-newspaper"></i>
                <span>Blog Management</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="blog-nav" class="collapse {{ in_array($currentPage, ['blog-posts', 'blog-categories', 'translated-blogs']) ? 'show' : '' }}" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="{{ route('admin.blog-posts.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Posts</span>
                </a>
                <a class="nav-link" href="{{ route('admin.blog-categories.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
                <a class="nav-link" href="{{ route('admin.translated-blogs.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Translated Posts</span>
                </a>
            </div>
        </li>
    @endif

    @if($can('destinations') || $can('accommodations') || $can('testimonials') || $can('media'))
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Management</div>
    @endif

    {{-- Destinations --}}
    @if($can('destinations'))
        <li class="nav-item {{ $currentPage === 'destinations' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.destinations.index') }}">
                <i class="fas fa-fw fa-map-marker-alt"></i>
                <span>Destinations</span>
            </a>
        </li>
    @endif

    {{-- Accommodations --}}
    @if($can('accommodations'))
        <li class="nav-item {{ $currentPage === 'accommodations' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.accommodations.index') }}">
                <i class="fas fa-fw fa-hotel"></i>
                <span>Accommodations</span>
            </a>
        </li>
    @endif

    {{-- Testimonials --}}
    @if($can('testimonials'))
        <li class="nav-item {{ $currentPage === 'testimonials' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.testimonials.index') }}">
                <i class="fas fa-fw fa-quote-right"></i>
                <span>Testimonials</span>
            </a>
        </li>
    @endif

    {{-- Media Library --}}
    @if($can('media'))
        <li class="nav-item {{ in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? 'active' : '' }}">
            <a class="nav-link {{ in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? '' : 'collapsed' }}"
               href="#" data-bs-target="#media-nav" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-images"></i>
                <span>Media Library</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div id="media-nav" class="collapse {{ in_array($currentPage, ['media', 'media-categories', 'media-tags']) ? 'show' : '' }}" data-bs-parent="#accordionSidebar">
                <a class="nav-link" href="{{ route('admin.media.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>All Media</span>
                </a>
                <a class="nav-link" href="{{ route('admin.media.compression') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Compress Images</span>
                </a>
                <a class="nav-link" href="{{ route('admin.media.categories.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Categories</span>
                </a>
                <a class="nav-link" href="{{ route('admin.media.tags.index') }}">
                    <i class="fas fa-fw fa-circle" style="font-size:0.5rem;"></i>
                    <span>Tags</span>
                </a>
            </div>
        </li>
    @endif

    @if($can('inquiries'))
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Operations</div>
    @endif

    {{-- Bookings / Inquiries --}}
    @if($can('inquiries'))
        <li class="nav-item {{ $currentPage === 'inquiries' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.inquiries.index') }}">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Bookings / Inquiries</span>
            </a>
        </li>
    @endif

    {{-- System: shown when the user has at least one of settings/profile access --}}
    @if($can('settings') || $can('profile'))
    <hr class="sidebar-divider">
    <div class="sidebar-heading">System</div>
    @endif

    {{-- Settings --}}
    @if($can('settings'))
        <li class="nav-item {{ $currentPage === 'settings' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.settings.index') }}">
                <i class="fas fa-fw fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-item {{ $currentPage === 'sitemap' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.sitemap.index') }}">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Sitemap</span>
            </a>
        </li>
    @endif

    {{-- Profile --}}
    @if($can('profile'))
        <li class="nav-item {{ $currentPage === 'profile' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.profile.show') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
    @endif

    {{-- Logout --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="version" id="version-tours">Afro Vertex Tours v1.0</div>
</ul>