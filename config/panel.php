<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Modules
    |--------------------------------------------------------------------------
    |
    | Each key is a permission that maps 1:1 to an item (or sub-menu) in the
    | admin sidebar. A user is granted a subset of these permissions; whenever
    | a permission is not granted the corresponding sidebar item is hidden and
    | its routes return 403.
    |
    | 'super_admin' users implicitly hold every permission.
    |
    */

    'modules' => [

        'dashboard' => [
            'label'       => 'Dashboard',
            'icon'        => 'fa-tachometer-alt',
            'route'       => 'dashboard',
            'description' => 'Overview of site metrics and quick stats.',
        ],

        'pages' => [
            'label'       => 'Edit About',
            'icon'        => 'fa-file-alt',
            'route'       => 'admin.pages.index',
            'description' => 'Edit static pages such as the About page.',
        ],

        'tours' => [
            'label'       => 'Tours & Packages',
            'icon'        => 'fa-briefcase',
            'route'       => 'admin.tour-packages.index',
            'description' => 'Manage tour packages and their categories.',
        ],

        'users' => [
            'label'       => 'User Management',
            'icon'        => 'fa-users',
            'route'       => 'admin.users.index',
            'description' => 'Manage user accounts, roles and their permissions.',
        ],

        'blog' => [
            'label'       => 'Blog Management',
            'icon'        => 'fa-newspaper',
            'route'       => 'admin.blog-posts.index',
            'description' => 'Manage blog posts, categories and translations.',
        ],

        'destinations' => [
            'label'       => 'Destinations',
            'icon'        => 'fa-map-marker-alt',
            'route'       => 'admin.destinations.index',
            'description' => 'Manage tour destinations and locations.',
        ],

        'accommodations' => [
            'label'       => 'Accommodations',
            'icon'        => 'fa-hotel',
            'route'       => 'admin.accommodations.index',
            'description' => 'Manage hotels and accommodation options.',
        ],

        'testimonials' => [
            'label'       => 'Testimonials',
            'icon'        => 'fa-quote-right',
            'route'       => 'admin.testimonials.index',
            'description' => 'Manage customer testimonials and reviews.',
        ],

        'media' => [
            'label'       => 'Media Library',
            'icon'        => 'fa-images',
            'route'       => 'admin.media.index',
            'description' => 'Upload and organize images in the media library.',
        ],

        'inquiries' => [
            'label'       => 'Bookings / Inquiries',
            'icon'        => 'fa-clipboard-list',
            'route'       => 'admin.inquiries.index',
            'description' => 'Review bookings and contact inquiries.',
        ],

        'settings' => [
            'label'       => 'Settings',
            'icon'        => 'fa-cog',
            'route'       => 'admin.settings.index',
            'description' => 'Site-wide configuration and preferences.',
        ],

        'profile' => [
            'label'       => 'Profile',
            'icon'        => 'fa-user',
            'route'       => 'admin.profile.show',
            'description' => 'Manage your own profile and password.',
        ],
    ],

];