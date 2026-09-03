<?php
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\TourPackageController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\MediaPickerController;
use App\Http\Controllers\Admin\MediaCategoryController;
use App\Http\Controllers\Admin\MediaTagController;
use App\Http\Controllers\Admin\MediaCompressionController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\ContactController;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
// Login, Register, Logout routes...
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
// Protected routes
// Unified admin panel home — visible to any authenticated panel member
// (super admin, or editor with at least one sidebar permission).
Route::middleware(['auth', 'panel-access', 'active', 'force-password-change'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->middleware('permission:dashboard')
        ->name('dashboard');
});
// Profile is available to any authenticated panel user after their forced
// password change (if one is outstanding).
Route::middleware(['auth', 'active', 'force-password-change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('admin.profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('admin.profile.avatar');
});
// First-login forced password change (no role required, must stay auth/active only).
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/must-change-password', [\App\Http\Controllers\Auth\MustChangePasswordController::class, 'create'])
        ->name('forced-password-change');
    Route::post('/must-change-password', [\App\Http\Controllers\Auth\MustChangePasswordController::class, 'store']);
});
// Unified Admin panel — super admins implicitly access everything; package
// editors access only the modules their admin has granted (see config/panel.php).
Route::middleware(['auth', 'panel-access', 'active', 'force-password-change'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect(request()->user()->panelHome());
    })->name('dashboard');

    // Package Editor / user account management — controls user permissions,
    // deliberately requiring the dedicated 'users' module.
    Route::middleware('permission:users')->group(function () {
        Route::get('users', [\App\Http\Controllers\Admin\ManageUsersController::class, 'index'])->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\Admin\ManageUsersController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\Admin\ManageUsersController::class, 'store'])->name('users.store');
        Route::post('users/{user}/suspend', [\App\Http\Controllers\Admin\ManageUsersController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/activate', [\App\Http\Controllers\Admin\ManageUsersController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/reset-password', [\App\Http\Controllers\Admin\ManageUsersController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/permissions', [\App\Http\Controllers\Admin\ManageUsersController::class, 'updatePermissions'])->name('users.permissions');
    });

    // ── Static Pages (module: pages) ─────────────────────────────────────
    Route::middleware('permission:pages')->group(function () {
        Route::resource('pages', PageController::class);
    });

    // ── Tours & Packages (module: tours) ────────────────────────────────
    Route::middleware('permission:tours')->group(function () {
        Route::post('location-search', [\App\Http\Controllers\Admin\LocationSearchController::class, '__invoke'])
            ->middleware('throttle:10,1')
            ->name('location-search');
        Route::resource('tour-packages', TourPackageController::class);
        // Phase 2: server-side price calculator preview (POST-only, throttled).
        Route::post('tour-price-calculator/preview', [\App\Http\Controllers\Admin\TourPriceCalculatorController::class, 'preview'])
            ->name('tour-price-calculator.preview')
            ->middleware('throttle:30,1');
        Route::resource('tour-categories', \App\Http\Controllers\Admin\TourCategoryController::class);
    });

    // ── Inquiries & Bookings (module: inquiries) ────────────────────────
    Route::middleware('permission:inquiries')->group(function () {
        Route::resource('inquiries', \App\Http\Controllers\Admin\InquiryController::class);
    });

    // ── Destinations (module: destinations) ─────────────────────────────
    Route::middleware('permission:destinations')->group(function () {
        Route::resource('destinations', \App\Http\Controllers\Admin\DestinationController::class);
    });

    // ── Accommodations (module: accommodations) ─────────────────────────
    Route::middleware('permission:accommodations')->group(function () {
        Route::resource('accommodations', \App\Http\Controllers\Admin\AccommodationController::class);
    });

    // ── Testimonials (module: testimonials) ─────────────────────────────
    Route::middleware('permission:testimonials')->group(function () {
        Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);
    });

    // ── Media Library (module: media) ───────────────────────────────────
    // Replaces the original Admin\MediaController and routes — see Phase 1 analysis
    // and MediaLibraryController's docblock for the two bugs fixed here: a missing
    // detach() method, and that same route previously being double-prefixed —
    // '/admin/media/{media}/detach' registered *inside* a group already prefixed
    // 'admin', which resolved to '/admin/admin/media/{media}/detach'. All paths below
    // are written relative to this group's existing 'admin' prefix, so none of them
    // repeat it.
    Route::middleware('permission:media')->group(function () {
        Route::get('/media', [MediaLibraryController::class, 'index'])->name('media.index');

        // Image compression tool — see MediaCompressionController.
        // NOTE: these must stay BEFORE `/media/{media}` below, otherwise the
        // parametrised /media/{media} route swallows /media/compress → 404.
        Route::get('/media/compress', [MediaCompressionController::class, 'index'])->name('media.compression');
        Route::post('/media/compress', [MediaCompressionController::class, 'run'])->name('media.compression.run');

        Route::get('/media/{media}', [MediaLibraryController::class, 'show'])->name('media.show');
        Route::get('/media/{media}/edit', [MediaLibraryController::class, 'edit'])->name('media.edit');
        Route::put('/media/{media}', [MediaLibraryController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}', [MediaLibraryController::class, 'destroy'])->name('media.destroy');
        Route::delete('/media/{media}/ajax', [MediaLibraryController::class, 'destroyAjax'])->name('media.destroy.ajax');
        Route::post('/media/{media}/detach', [MediaLibraryController::class, 'detach'])->name('media.detach');

        Route::post('/media/upload', [MediaUploadController::class, 'store'])->name('media.upload');

        Route::get('/media-picker/search', [MediaPickerController::class, 'search'])->name('media.picker.search');
        Route::get('/media-picker/{media}', [MediaPickerController::class, 'show'])->name('media.picker.show');

        Route::get('/media-categories', [MediaCategoryController::class, 'index'])->name('media.categories.index');
        Route::get('/media-categories/create', [MediaCategoryController::class, 'create'])->name('media.categories.create');
        Route::post('/media-categories', [MediaCategoryController::class, 'store'])->name('media.categories.store');
        Route::get('/media-categories/{category}/edit', [MediaCategoryController::class, 'edit'])->name('media.categories.edit');
        Route::put('/media-categories/{category}', [MediaCategoryController::class, 'update'])->name('media.categories.update');
        Route::delete('/media-categories/{category}', [MediaCategoryController::class, 'destroy'])->name('media.categories.destroy');

        Route::get('/media-tags', [MediaTagController::class, 'index'])->name('media.tags.index');
        Route::post('/media-tags', [MediaTagController::class, 'store'])->name('media.tags.store');
        Route::put('/media-tags/{tag}', [MediaTagController::class, 'update'])->name('media.tags.update');
        Route::delete('/media-tags/{tag}', [MediaTagController::class, 'destroy'])->name('media.tags.destroy');
    });

    // ── Blog Management (module: blog) ──────────────────────────────────
    Route::middleware('permission:blog')->group(function () {
        Route::resource('blog-categories', \App\Http\Controllers\Admin\BlogCategoryController::class);
        Route::resource('blog-posts', \App\Http\Controllers\Admin\BlogPostController::class);

        // ====================== NEW: Translated Blogs ======================
        // Added exactly as requested – does NOT affect any other route
        Route::resource('translated-blogs', \App\Http\Controllers\Admin\TranslatedBlogController::class);
        // ==================================================================
    });

    // ── Settings (module: settings) ─────────────────────────────────────
    Route::middleware('permission:settings')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('/sitemap', [\App\Http\Controllers\Admin\SitemapController::class, 'index'])->name('sitemap.index');
        Route::post('/sitemap/generate', [\App\Http\Controllers\Admin\SitemapController::class, 'generate'])->name('sitemap.generate');
    });
});
// ====================== PUBLIC ROUTES ======================
Route::get('/sitemap.xml', function () {
    if (!Setting::get('enable_sitemap')) {
        abort(404);
    }
    return response(\App\Services\SitemapGenerator::build(), 200)
        ->header('Content-Type', 'application/xml');
});
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/search-destinations', [TourController::class, 'searchDestinations'])->name('tours.searchDestinations');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tour.show');
// Phase 3: server-side price lookup (season from date, package level, exact
// group size). Read-only; answers from configured package_prices/season tiers,
// never from browser-calculated numbers. Three segments so it cannot collide
// with the two-segment /tours/{slug} show route above.
Route::get('/tours/{tour}/price-lookup', [TourController::class, 'priceLookup'])
    ->name('tours.priceLookup')
    ->middleware('throttle:30,1');

Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
Route::get('/destinations/{slug}', [DestinationController::class, 'showPublic'])->name('destination.show');
Route::get('/destinations', [DestinationController::class, 'indexPublic'])->name('destinations.index');
Route::get('/accommodations', [HomeController::class, 'accommodations'])->name('accommodations.index');
Route::get('/blog', [BlogPostController::class, 'indexPublic'])->name('blog.index');

// ====================== NEW TRANSLATED BLOG ROUTE ======================
Route::get('/blog/{language_code}/{slug}', [BlogPostController::class, 'showTranslated'])
     ->name('blog.translated');
// ======================================================================

Route::get('/blog/{slug}', [BlogPostController::class, 'showPublic'])->name('blog.show');
// ====================== IMPROVED TRANSLATION ROUTES ======================
// These routes are smart: they only translate missing languages (safe to run multiple times)
use App\Services\BlogPostTranslator;
Route::get('/translate-post/{id}/{secret}', function ($id, $secret) {
    if ($secret !== 'myStrongSecret789') { // ← CHANGE THIS SECRET TO YOUR OWN STRONG ONE
        abort(403, 'Access denied');
    }
    $post = BlogPost::findOrFail($id);
    $translator = new BlogPostTranslator();
    $translator->translatePost($post);
    return "✅ Post ID {$id} has been processed. Only missing languages were translated.";
});
Route::get('/translate-all-posts/{secret}', function ($secret) {
    if ($secret !== 'myStrongSecret789') { // ← Use the same secret
        abort(403, 'Access denied');
    }
    $translator = new BlogPostTranslator();
    $translator->translateAllPosts();
    return "✅ All blog posts have been processed successfully!<br>
            Only missing language translations (title + slug) were added for:<br>
            en, sw, fr, es, de, zh, it, pt, ru, nl";
});
// =====================================================================
// Package Editor panel retired in favor of the unified admin panel — package
// editors now use the same /admin panel, with their sidebar/menu filtered to
// the sidebar-module permissions granted by the Super Admin (config/panel.php).
Route::get('/editor', function () {
    return redirect()->route('dashboard');
})->name('editor.dashboard');

// Require the auth routes defined in auth.php. Public registration has been
// removed: only the Super Admin creates accounts (see admin.users.* routes).
require __DIR__.'/auth.php';

// Tour category listing pages — e.g. /tanzania-tours, /kilimanjaro-climbing-package,
// /zanzibar-holiday-tour, /day-trip. Reuses TourController::index() (the same
// view/filtering logic as the main /tours page), just with the category slug bound
// directly from the URL instead of a query string, replacing the old (incorrect)
// page.show route the footer previously pointed at — these are tour listings, not
// standalone Pages.
//
// Deliberately NOT a fixed "-tours" suffix pattern: the category list includes
// slugs that don't end in "-tours" at all (kilimanjaro-climbing-package, day-trip),
// so this matches any single path segment instead, with TourController::category()
// resolving it against real TourCategory rows — an unrecognized slug falls through
// to a normal 404 rather than ever being misinterpreted as something else.
//
// MUST be the very last route registered (after even the auth.php include below) —
// a single-segment wildcard at the site root would otherwise shadow every other
// single-segment route in the app, including /login, /register, /forgot-password,
// /verify-email, and /confirm-password from auth.php, which all load via that
// require statement and would never be reachable if this route came first. Verified
// directly: traced every single-segment GET route in both web.php and auth.php
// before deciding where this could safely go, rather than assuming "near the bottom
// of web.php" was good enough — it would not have been, since auth.php's routes
// load after everything already in web.php regardless of where in web.php they're
// required from.
Route::get('/{categorySlug}', [TourController::class, 'category'])
    ->name('tours.category')
    ->where('categorySlug', '[a-z0-9-]+');