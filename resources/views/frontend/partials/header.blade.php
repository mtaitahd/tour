@php
    use App\Models\Setting;
    use App\Models\TourPackage;
    use App\Models\Destination;
    use Illuminate\Support\Facades\Route;

    $siteName = Setting::get('site_name', 'Afro-Vertex Tours & Safaris');
    $logo     = Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp');

    $wa = Setting::get('whatsapp_number');
    $waDigits = $wa ? preg_replace('/[^0-9]/', '', $wa) : '';
    $waLink = $wa ? ('https://wa.me/' . $waDigits . '?text=Hello%20' . urlencode($siteName)) : '#';

    // Real Kilimanjaro tour packages (mobile dropdown fallback / nav label)
    try {
        $kiliTours = TourPackage::whereHas('categories', function ($q) {
                $q->where('slug', 'kilimanjaro-climbing');
            })
            ->where('status', 'published')
            ->orderBy('title')
            ->take(6)
            ->get();
    } catch (\Throwable $e) {
        $kiliTours = collect();
    }
    if ($kiliTours->isEmpty()) {
        $kiliTours = TourPackage::where('status', 'published')->orderBy('title')->take(6)->get();
    }

    // Real safari parks / reserves (mobile dropdown)
    try {
        $safariParks = Destination::whereIn('type', ['National Park', 'mountain'])
            ->orderBy('name')
            ->take(6)
            ->get();
    } catch (\Throwable $e) {
        $safariParks = collect();
    }

    $routeName = optional(Route::current())->getName();
    $params    = optional(Route::current())->parameters() ?? [];
    $catSlug   = $params['categorySlug'] ?? ($params['slug'] ?? null);

    $kiliActive   = $routeName === 'tours.category' && $catSlug === 'kilimanjaro-climbing';
    $safariActive = !$kiliActive && in_array($routeName, ['tours.index', 'tours.category', 'tour.show', 'destinations.index', 'destination.show'], true);

    /* ══════════════════════════════════════════════════════════════
       MEGA MENU DATA — built dynamically from navigation_mega_items.
       Every item corresponds to a tour/page the admin explicitly enabled
       for its parent (Kilimanjaro / Safari / Day Trips). The middle and
       right columns are derived per-item, so hovering/focusing a left
       item updates title, description, CTA and image without a reload.
       ══════════════════════════════════════════════════════════════ */
    try {
        $megaMenu = app(\App\Services\NavigationMegaMenuService::class)->frontendData();
    } catch (\Throwable $e) {
        $megaMenu = [];
    }

    // Fallback nav labels for parents with no configured items yet, so the
    // navigation still shows Kilimanjaro / Safari / Day Trips trigger links
    // even before the admin configures any mega-menu entries.
    $parentLabels = [
        'kilimanjaro' => ['label' => 'Kilimanjaro', 'url' => route('tours.category', 'kilimanjaro-climbing')],
        'safari'      => ['label' => 'Safari',      'url' => route('tours.index')],
        'daytrips'    => ['label' => 'Day Trips',   'url' => route('tours.index')],
    ];

    $badgeClassMap = config('navigation.badge_classes', []);
@endphp

<header class="av-nav" id="avNav">
    <div class="av-nav__inner">
        <!-- Logo -->
        <a class="av-nav__logo" href="{{ route('home') }}" aria-label="{{ $siteName }} home">
            <img src="{{ $logo }}" alt="{{ $siteName }}">
        </a>

        <!-- Center navigation -->
        <nav class="av-nav__menu" aria-label="Main navigation">
            <ul class="av-nav__list">
                @foreach($parentLabels as $key => $fallback)
                    @php
                        $menu = $megaMenu[$key] ?? null;
                        $isActive = $menu['active'] ?? ($key === 'kilimanjaro' ? $kiliActive : ($key === 'safari' ? $safariActive : false));
                        $trigger = $menu['trigger_url'] ?? $fallback['url'];
                        $items = $menu['categories'] ?? [];
                    @endphp
                    <li class="av-nav__item has-mega @if($isActive) is-active @endif">
                        <a class="av-nav__link" href="{{ $trigger }}"
                           aria-haspopup="true"
                           aria-expanded="false"
                           aria-controls="avMega-{{ $key }}">
                            {{ $menu['label'] ?? $fallback['label'] }} <i class="bi bi-chevron-down av-nav__caret" aria-hidden="true"></i>
                        </a>

                        @if(!empty($items))
                            {{-- ── Mega menu ── --}}
                            <div class="av-mega" id="avMega-{{ $key }}" aria-label="{{ $menu['label'] ?? $fallback['label'] }} menu">
                                <div class="av-mega__inner">

                                    {{-- Left column: admin-selected tours/pages --}}
                                    <div class="av-mega__cats" role="tablist" aria-label="{{ $menu['label'] ?? $fallback['label'] }} items">
                                        @foreach($items as $ci => $cat)
                                            <button type="button" role="tab"
                                                class="av-mega__cat {{ $ci === 0 ? 'is-active' : '' }}"
                                                id="avMega-{{ $key }}-tab-{{ $ci }}"
                                                aria-controls="avMega-{{ $key }}-panel-{{ $ci }}"
                                                aria-selected="{{ $ci === 0 ? 'true' : 'false' }}"
                                                tabindex="{{ $ci === 0 ? 0 : -1 }}"
                                                data-index="{{ $ci }}">
                                                <span class="av-mega__cat-label">
                                                    {{ $cat['title'] }}
                                                    @if(!empty($cat['badge']))
                                                        <span class="av-mega__badge {{ $badgeClassMap[strtolower($cat['badge'])] ?? '' }}">{{ $cat['badge'] }}</span>
                                                    @endif
                                                </span>
                                                <i class="bi bi-chevron-right av-mega__chev" aria-hidden="true"></i>
                                            </button>
                                        @endforeach
                                    </div>

                                    {{-- Middle column: active item info --}}
                                    <div class="av-mega__detail">
                                        @foreach($items as $ci => $cat)
                                            <div class="av-mega__panel {{ $ci === 0 ? 'is-active' : '' }}"
                                                 id="avMega-{{ $key }}-panel-{{ $ci }}"
                                                 role="tabpanel"
                                                 aria-labelledby="avMega-{{ $key }}-tab-{{ $ci }}"
                                                 data-index="{{ $ci }}">
                                                <h3 class="av-mega__heading">{{ $cat['heading'] }}</h3>
                                                <p class="av-mega__desc">{{ $cat['description'] }}</p>
                                                <a class="av-mega__cta" href="{{ $cat['cta']['url'] }}">
                                                    {{ $cat['cta']['label'] }}
                                                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 10h13M16.5 10l-5.5-5.5M16.5 10 11 15.5"/></svg>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Right column: active item image --}}
                                    <div class="av-mega__media">
                                        @foreach($items as $ci => $cat)
                                            <img class="av-mega__img {{ $ci === 0 ? 'is-active' : '' }}"
                                                 src="{{ $cat['image'] }}"
                                                 alt="{{ $cat['image_alt'] }}"
                                                 data-index="{{ $ci }}"
                                                 decoding="async">
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <!-- Right actions -->
        <div class="av-nav__actions">
            <button class="av-nav__hamburger" id="avNavHamburger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="avDrawer">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

@php
    // ---- Mobile drawer data (mirrors the desktop mega menu) ----
    $allToursUrl  = route('tours.index');
    $mobileSafari = $megaMenu['safari'] ?? null;
    $mobileKili   = $megaMenu['kilimanjaro'] ?? null;
    $mobileDay    = $megaMenu['daytrips'] ?? null;

    $safariUrl   = $mobileSafari['trigger_url'] ?? $allToursUrl;
    $safariItems = $mobileSafari['categories']  ?? [];
    $kiliUrl     = $mobileKili['trigger_url']   ?? route('tours.category', 'kilimanjaro-climbing');
    $kiliItems   = $mobileKili['categories']    ?? [];
    $kiliLabel   = $mobileKili['label']         ?? 'Kilimanjaro';
    $dayUrl      = $mobileDay['trigger_url']    ?? $allToursUrl;
    $dayItems    = $mobileDay['categories']     ?? [];
    $dayLabel    = $mobileDay['label']          ?? 'Day Trips';
@endphp

<!-- Mobile drawer (drops down directly below the header, above the content) -->
<div class="av-drawer" id="avDrawer" aria-hidden="true">
    <nav class="av-drawer__panel" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <ul class="av-drawer__list">
            <li>
                <a class="av-drawer__link" href="{{ route('home') }}">Home</a>
            </li>

            @if(!empty($safariItems))
                <li class="has-dropdown">
                    <div class="av-drawer__row">
                        <a href="{{ $safariUrl }}">Safaris</a>
                        <button class="av-drawer__toggle" type="button" aria-expanded="false" aria-controls="avDrawerSubSafaris" aria-label="Toggle Safaris submenu"><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                    </div>
                    <ul class="av-drawer__sub" id="avDrawerSubSafaris">
                        @foreach($safariItems as $cat)
                            <li><a href="{{ $cat['url'] }}">{{ $cat['title'] }}</a></li>
                        @endforeach
                        <li><a href="{{ $safariUrl }}">View all Safaris</a></li>
                    </ul>
                </li>
            @else
                <li><a class="av-drawer__link" href="{{ $safariUrl }}">Safaris</a></li>
            @endif

            @if(!empty($kiliItems) || !empty($dayItems))
                <li class="has-dropdown">
                    <div class="av-drawer__row">
                        <a href="{{ $allToursUrl }}">Tours</a>
                        <button class="av-drawer__toggle" type="button" aria-expanded="false" aria-controls="avDrawerSubTours" aria-label="Toggle Tours submenu"><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                    </div>
                    <ul class="av-drawer__sub" id="avDrawerSubTours">
                        @if(!empty($kiliItems))
                            <li class="av-drawer__subhead">{{ $kiliLabel }}</li>
                            @foreach($kiliItems as $cat)
                                <li><a href="{{ $cat['url'] }}">{{ $cat['title'] }}</a></li>
                            @endforeach
                            <li><a href="{{ $kiliUrl }}">View all {{ $kiliLabel }}</a></li>
                        @endif
                        @if(!empty($dayItems))
                            <li class="av-drawer__subhead">{{ $dayLabel }}</li>
                            @foreach($dayItems as $cat)
                                <li><a href="{{ $cat['url'] }}">{{ $cat['title'] }}</a></li>
                            @endforeach
                            <li><a href="{{ $dayUrl }}">View all {{ $dayLabel }}</a></li>
                        @endif
                        <li><a href="{{ $allToursUrl }}">View all Tours</a></li>
                    </ul>
                </li>
            @else
                <li><a class="av-drawer__link" href="{{ $allToursUrl }}">Tours</a></li>
            @endif

            <li>
                <a class="av-drawer__link" href="{{ route('destinations.index') }}">Destinations</a>
            </li>

            <li>
                <a class="av-drawer__link" href="{{ route('page.show', 'about-us') }}">About</a>
            </li>

            <li>
                <a class="av-drawer__link" href="{{ route('blog.index') }}">Blog</a>
            </li>

            <li>
                <a class="av-drawer__link" href="{{ route('page.show', 'contact') }}">Contact</a>
            </li>
        </ul>

        <a class="av-drawer__whatsapp" href="{{ $waLink }}" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp
        </a>
    </nav>
</div>
</header>

<script data-cfasync="false">
document.addEventListener('DOMContentLoaded', function () {
    var nav = document.querySelector('.av-nav');

    function onScroll() {
        if (!nav) return;
        if (window.scrollY > 30) nav.classList.add('is-sticky');
        else nav.classList.remove('is-sticky');
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ═══════════════════════════════════════════════════════
       Desktop mega menus (≥992px) — data lives in the DOM,
       rendered server-side; this script only wires behaviour.
       ═══════════════════════════════════════════════════════ */
    var mqDesktop = window.matchMedia('(min-width: 992px)');
    var megaItems = Array.prototype.slice.call(document.querySelectorAll('.av-nav__item.has-mega'));
    var CLOSE_DELAY = 220;
    var closeTimer = null;

    function megaLink(item)   { return item.querySelector('.av-nav__link'); }
    function megaCats(item)   { return Array.prototype.slice.call(item.querySelectorAll('.av-mega__cat')); }

    function activateCategory(item, index) {
        item.querySelectorAll('.av-mega__cat').forEach(function (btn, i) {
            var on = i === index;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.tabIndex = on ? 0 : -1;
        });
        item.querySelectorAll('.av-mega__panel').forEach(function (panel, i) {
            panel.classList.toggle('is-active', i === index);
        });
        item.querySelectorAll('.av-mega__img').forEach(function (img, i) {
            img.classList.toggle('is-active', i === index);
        });
    }

    function preloadImages(item) {
        if (item.dataset.preloaded) return;
        item.dataset.preloaded = '1';
        item.querySelectorAll('.av-mega__img').forEach(function (img) {
            var warm = new Image();
            warm.src = img.getAttribute('src');
        });
    }

    function openMega(item) {
        if (!mqDesktop.matches) return;
        /* Keep the fixed panel anchored exactly to the live header edge */
        if (nav) document.documentElement.style.setProperty('--header-bottom', Math.ceil(nav.getBoundingClientRect().bottom) + 'px');
        clearTimeout(closeTimer);
        megaItems.forEach(function (other) {
            if (other !== item) closeMega(other, true);
        });
        item.classList.add('is-open');
        megaLink(item).setAttribute('aria-expanded', 'true');
        preloadImages(item);
    }

    function closeMega(item, immediate) {
        var run = function () {
            item.classList.remove('is-open');
            megaLink(item).setAttribute('aria-expanded', 'false');
        };
        clearTimeout(closeTimer);
        if (immediate) run();
        else closeTimer = setTimeout(run, CLOSE_DELAY);
    }

    function closeAll(immediate) {
        megaItems.forEach(function (item) { closeMega(item, immediate); });
    }

    megaItems.forEach(function (item) {
        var link = megaLink(item);
        var mega = item.querySelector('.av-mega');
        var cats = megaCats(item);

        /* Hover — trigger and menu are both inside <li>, so moving the
           cursor between them never leaves the item; the transparent
           ::before bridge covers any rounding gap under the bar. */
        [link, mega].forEach(function (el) {
            el.addEventListener('mouseenter', function () { openMega(item); });
            el.addEventListener('mouseleave', function () { closeMega(item); });
        });

        /* Category switching */
        cats.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activateCategory(item, parseInt(btn.dataset.index, 10));
            });
            btn.addEventListener('keydown', function (e) {
                var idx = parseInt(btn.dataset.index, 10);
                var next = null;
                if (e.key === 'ArrowDown') next = (idx + 1) % cats.length;
                else if (e.key === 'ArrowUp') next = (idx - 1 + cats.length) % cats.length;
                else if (e.key === 'ArrowRight' || e.key === 'Tab') return;
                else if (e.key === 'Escape') { closeAll(true); link.focus(); e.preventDefault(); }
                if (next !== null) {
                    e.preventDefault();
                    activateCategory(item, next);
                    cats[next].focus();
                }
            });
        });

        /* Keyboard — open on focus, navigate with arrows */
        link.addEventListener('focus', function () { if (mqDesktop.matches) openMega(item); });
        link.addEventListener('keydown', function (e) {
            if ((e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') && mqDesktop.matches && item.classList.contains('is-open')) {
                var active = item.querySelector('.av-mega__cat.is-active') || cats[0];
                if (active) { active.focus(); e.preventDefault(); }
            } else if (e.key === 'ArrowDown' && mqDesktop.matches) {
                openMega(item);
                e.preventDefault();
            }
        });

        /* Close when tabbing past the whole item */
        item.addEventListener('focusout', function (e) {
            if (!item.contains(e.relatedTarget)) closeMega(item, true);
        });

        /* Preload images lazily on first hover-warmup */
        item.addEventListener('mouseenter', preloadImages, { once: true });
    });

    /* Escape closes everything */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll(true);
    });

    /* Click anywhere outside the header closes everything */
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.av-nav')) closeAll(true);
    });

    /* ═══════════════════════════════════════════════════════
       Mobile drawer (below-header panel)
       ═══════════════════════════════════════════════════════ */
    var drawer = document.getElementById('avDrawer');
    var burger = document.getElementById('avNavHamburger');

    function isOpen() {
        return !!drawer && drawer.classList.contains('is-open');
    }

    function openDrawer() {
        if (!drawer) return;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (burger) {
            burger.setAttribute('aria-expanded', 'true');
            burger.setAttribute('aria-label', 'Close menu');
        }
        document.body.classList.add('av-no-scroll');
    }

    function closeDrawer() {
        if (!drawer) return;
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (burger) {
            burger.setAttribute('aria-expanded', 'false');
            burger.setAttribute('aria-label', 'Open menu');
        }
        document.body.classList.remove('av-no-scroll');
    }

    if (burger) {
        burger.addEventListener('click', function () {
            if (isOpen()) closeDrawer();
            else openDrawer();
        });
    }

    /* Close when tapping outside the drawer or the burger */
    document.addEventListener('click', function (e) {
        if (!isOpen()) return;
        if (burger && e.target.closest('#avNavHamburger')) return;
        if (drawer && e.target.closest('#avDrawer')) return;
        closeDrawer();
    });

    /* Close when any link inside the drawer is tapped */
    if (drawer) {
        drawer.addEventListener('click', function (e) {
            if (e.target.closest('a')) closeDrawer();
        });
    }

    /* Close on Escape and return focus to the burger */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen()) {
            closeDrawer();
            if (burger) burger.focus();
        }
    });

    /* Accordion sub-menus */
    document.querySelectorAll('.av-drawer__toggle').forEach(function (t) {
        t.addEventListener('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var li = t.closest('li');
            if (!li) return;
            var willOpen = !li.classList.contains('is-open');
            li.classList.toggle('is-open', willOpen);
            t.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
});
</script>
