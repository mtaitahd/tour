<?php

return [
    /*
    | Top-level navigation parents that support a three-column mega menu.
    | Keys must stay stable — they're stored in navigation_mega_items.parent_menu_key.
    |
    | route/params describe where the top-level trigger link goes. This is the ONLY
    | place that knows which real routes back each parent, so adding/removing a menu
    | in the future is a config change, not a template change.
    |
    | active_routes: route names that should mark this parent as "current" on public
    | pages, keeping the same semantics the pre-dynamic header had.
    */
    'parent_menus' => [
        'kilimanjaro' => [
            'label'          => 'Kilimanjaro',
            'route'          => 'tours.category',
            'params'         => ['kilimanjaro-climbing'],
            'active_routes'  => ['tours.category', 'tour.show'],
        ],
        'safari' => [
            'label'          => 'Safari',
            'route'          => 'tours.index',
            'params'         => [],
            'active_routes'  => ['tours.index', 'tours.category', 'tour.show', 'destinations.index', 'destination.show'],
        ],
        'daytrips' => [
            'label'          => 'Day Trips',
            'route'          => 'tours.index',
            'params'         => [],
            'active_routes'  => [],
        ],
    ],

    /*
    | Hard cap on how many menu items a single parent may show. Enforced both by the
    | admin form validation (a parent can't be pushed past this) and by the public
    | renderer (a defensive take(), in case rows sneak in another way).
    */
    'max_items_per_menu' => 6,

    /*
    | Middle-column description word budget used when an admin leaves the custom
    | short description empty (the source overview/content is stripped and trimmed
    | to this many words instead).
    */
    'description_word_limit' => 45,

    /*
    | Right-column image shown when neither a menu image nor a source hero image
    | exists. Evaluated through asset() by the renderer.
    */
    'fallback_image' => 'assets/img/placeholder-page-hero.jpg',

    /*
    | Badge pill CSS variants — mirrors the classes the header style sheet provides.
    | Keys are the lowercase badge_text values admins are most likely to type; each
    | maps to an existing CSS class so an unknown badge simply renders unstyled
    | instead of generating a class that doesn't exist.
    */
    'badge_classes' => [
        'popular'      => 'av-mega__badge--popular',
        'free-pdf'     => 'av-mega__badge--pdf',
        'free pdf'     => 'av-mega__badge--pdf',
        'new'          => 'av-mega__badge--new',
        '$100-deposit' => 'av-mega__badge--deposit',
        '$100 deposit' => 'av-mega__badge--deposit',
    ],
];