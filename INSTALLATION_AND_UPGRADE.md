# Media Library — Installation & Upgrade Instructions

These instructions assume you're applying the Phase 1–9 Media Library work to the existing Afro-Vertex Tours & Safaris CMS codebase. Nothing here requires a fresh Laravel install — everything was built additively on top of what already exists.

---

## Before You Start: Two Pre-Existing Issues to Resolve First

These were found during Phase 10 testing and **predate the Media Library work entirely**, but they will block a fresh `php artisan migrate` if you haven't already worked around them in your environment (your live production database appears to already have a working schema, so this is most likely to matter for a fresh clone, a new staging environment, or CI):

1. **Duplicate `tour_destinations` migration.** Two files create this table: `2026_02_04_093124_create_tour_destinations_table.php` (a near-empty stub) and `2026_02_07_221053_create_tour_destinations_table.php` (the real version). Decide whether to delete the stub file — check first that no other environment's `migrations` table depends on it being present.
2. **Missing `doctrine/dbal`.** `2026_02_10_093021_change_meta_fields_to_string_in_tour_packages.php` uses `->change()`, which requires it. Either run `composer require doctrine/dbal` or rewrite that migration to use raw SQL (`DB::statement(...)`) instead.

See `TESTING_CHECKLIST.md` for full detail on both, including exactly how they were confirmed.

---

## Installation

### 1. Copy in the new files

All new files are additive — nothing here overwrites a file that didn't already need to change for this feature:

- **Migrations** (`database/migrations/2026_06_26_*.php`) — 10 files, see Phase 2.
- **Models** (`app/Models/*.php`) — `GalleryImage`, `MediaCategory`, `MediaTag`, `MediaMeta`, `MediaUsage`, plus the `HasStandardMediaConversions` trait in `app/Models/Concerns/`. The four existing content models (`Destination`, `TourPackage`, `Page`, `BlogPost`) and `GlobalMedia` were modified in place, not replaced — see Phase 3.
- **Services** (`app/Services/ImageProcessorService.php`, `app/Services/MediaLibraryService.php`) — Phase 4.
- **Console command** (`app/Console/Commands/BackfillMediaLibrary.php`) — Phase 4.
- **Policy** (`app/Policies/MediaPolicy.php`) — Phase 5.
- **Form Requests** (`app/Http/Requests/Media/*.php`) — 5 files, Phase 5.
- **Controllers** (`app/Http/Controllers/Admin/Media*.php`) — 5 new files. **`app/Http/Controllers/Admin/MediaController.php` was deleted** — it's fully replaced by `MediaLibraryController` + `MediaUploadController`. Phase 5.
- **Views** (`resources/views/admin/media/**`, `resources/views/components/media-*.blade.php`) — Phase 7. The original `resources/views/admin/media/index.blade.php` was overwritten with the new version.
- **Blade components** (`app/View/Components/MediaPicker.php`, `app/View/Components/MediaImage.php`) — Phase 7.

### 2. Update modified existing files

These pre-existing files have real, non-destructive changes (each documented in detail in the phase that touched them):

- `config/media-library.php` — `media_model` now points at `App\Models\GalleryImage::class` instead of Spatie's default.
- `app/Providers/AuthServiceProvider.php` — registers `MediaPolicy` against `GalleryImage`.
- `routes/web.php` — replaces the old, buggy media routes (see below) with the full new route set; also fixes an unrelated double-prefix bug on the same lines.
- `resources/views/admin/partials/sidebar.blade.php` — Media Library nav link becomes a dropdown (All Media / Categories / Tags).
- `app/Http/Controllers/Admin/DestinationController.php`, `PageController.php`, `BlogPostController.php`, `TourPackageController.php` — each gets `*_image_id` validation/handling added to `store()`/`update()`, additive alongside existing upload logic. `PageController.php` also has two unrelated pre-existing bugs fixed (see Phase 9).
- `resources/views/admin/destinations/edit.blade.php`, `pages/edit.blade.php`, `blog-posts/{create,edit}.blade.php`, `tour-packages/{create,edit}.blade.php` — each gets a `<x-media-picker>` added alongside its existing upload field. `pages/edit.blade.php` also has one unrelated pre-existing bug fixed (a broken reference to a dropped column).

### 3. Run the migrations

```bash
php artisan migrate
```

This creates the 6 new tables and adds the 5 new FK columns. All 10 migrations were verified to run successfully end-to-end against a real (SQLite, for testing purposes) database during Phase 10 — see the Testing Checklist for exactly how.

### 4. Run the backfill command

```bash
# See what it would do first, with no writes:
php artisan media:backfill-library --dry-run

# Then run it for real:
php artisan media:backfill-library
```

This is the slow step — it regenerates missing image conversions for roughly 350 existing images (per the Phase 1 production-data estimate) and backfills `media_meta`/`media_usages` for every existing media row, including the informal `image_id` references already living inside `tour_packages.itinerary`/`extra_sections` JSON columns. Runs synchronously with progress output; no queue worker required.

Useful flags:
- `--dry-run` — report counts, write nothing.
- `--skip-conversions` — re-run the metadata/usage backfill steps without repeating the slow conversion regeneration (useful if you need to re-run after an interruption).
- `--chunk=N` — adjust the batch size (default 25).

### 5. Clear caches (recommended after any config/route change)

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 6. Verify

Visit `/admin/media` — you should see the new grid, search/filter sidebar, and stats panel. Visit `/admin/media-categories` and `/admin/media-tags` to confirm both are reachable from the sidebar dropdown. Work through the Testing Checklist's manual items before considering this live.

---

## Upgrade Notes for Existing Data

Nothing about this feature requires re-uploading or re-organizing any existing image. Every existing destination/tour/page/post keeps showing its current hero/gallery/featured images exactly as before — the new `<x-media-picker>` fields are empty until someone actively chooses to use them, and the old direct-upload fields keep working unchanged. There is no "migration day" risk where existing content could lose its images; the additive design across Phases 2–9 was specifically chosen to avoid that.

The one thing worth doing deliberately, on your own schedule rather than urgently: after the backfill command runs, go through the Media Library's grid and start adding titles/alt text/categories/tags to your existing images — they all have empty metadata to start with (by design, since nothing in the old system tracked any of that), and filling it in is what makes search/filtering across the library actually useful.

---

## Rolling Back

Every new migration has a working `down()` method. To remove the Media Library schema entirely:

```bash
php artisan migrate:rollback --step=10
```

This drops the 6 new tables and removes the 5 new FK columns, in the correct reverse order, without touching any existing data in `media`, `destinations`, `tour_packages`, `pages`, or `blog_posts`. You would also need to revert `config/media-library.php`'s `media_model` line back to Spatie's default, and restore `app/Http/Controllers/Admin/MediaController.php` plus the original route block in `routes/web.php`, if you wanted to fully revert the feature rather than just its database schema.
