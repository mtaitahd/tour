# Media Library — Testing Checklist

This checklist covers the Media Library feature built across Phases 1–9. Items marked **[Verified]** were actually executed in the build environment (PHP 8.3.6, real `php -l` syntax checks, a real SQLite database, real PHPUnit runs) — not just reviewed by eye. Items marked **[Manual]** need a real MySQL connection and a browser, which weren't available in the build environment, and should be checked by you before considering this production-ready.

---

## 1. Environment & Setup

- [x] **[Verified]** Every PHP file in the project (152 files, including all 19 new Media Library files) passes `php -l` syntax validation against PHP 8.3.6.
- [x] **[Verified]** All 35 migrations (25 pre-existing + 10 new from Phase 2) run successfully against a fresh database — see **Critical Pre-Existing Issues** below for two unrelated migrations that needed to be temporarily set aside to prove this, and must be fixed before a fresh install will work end-to-end.
- [x] **[Verified]** `composer.json` validates with `composer validate` (passes, with pre-existing warnings unrelated to the Media Library — see below).
- [ ] **[Manual]** Run `php artisan migrate` against your actual MySQL database and confirm all 10 new tables/columns appear (`media_categories`, `media_tags`, `media_category_media`, `media_tag_media`, `media_meta`, `media_usages`, plus `hero_image_id`/`story_image_id`/`featured_image_id` on the four content tables).
- [ ] **[Manual]** Run `php artisan media:backfill-library --dry-run` first to see the counts it reports, then run it for real (no `--dry-run`). Expect it to take a while on the conversion-regeneration step (~351 images per the Phase 1 estimate).
- [ ] **[Manual]** After the backfill, spot-check a few older BlogPost images (which previously had zero conversions) and confirm `thumb-webp`/`medium-webp`/`large-webp` files now exist in their `conversions/` folder on disk.

## 2. Upload (Single, Multiple, Drag & Drop, Bulk)

- [ ] **[Manual]** From `/admin/media`, click "Upload New Media," select a single JPG — confirm it uploads, appears in the grid, and shows the right file size.
- [ ] **[Manual]** Select multiple files at once (5+) — confirm all are previewed before upload, all upload, and the progress bar moves.
- [ ] **[Manual]** Drag and drop 3 images directly onto the dropzone — confirm they're added to the preview without needing the file browser.
- [ ] **[Manual]** Try uploading a `.gif` — confirm it's rejected (the brief specifies JPG/JPEG/PNG/WEBP only; GIF is intentionally excluded, see Phase 1 analysis).
- [ ] **[Manual]** Try uploading a file over 5MB — confirm it's rejected with a clear per-file message, and that other valid files in the same batch still upload successfully.
- [ ] **[Manual]** Try uploading a 50×50px image — confirm it's rejected by the minimum-dimension rule (100px).
- [ ] **[Manual]** Upload with a category selected in the modal's dropdown — confirm the image lands in that category afterward.

## 3. Browsing, Search, and Filtering

- [ ] **[Manual]** Confirm the grid shows thumbnail, name, category (if any), upload-adjacent size, and usage badge per the brief's Grid View spec.
- [ ] **[Manual]** Search by a word that appears in a title/filename — confirm matching results, and that the title/alt-text/caption/tag search (not just filename) actually works for an image with custom metadata set.
- [ ] **[Manual]** Filter by category — confirm only images in that category (and not its siblings) appear.
- [ ] **[Manual]** Filter by tag — confirm the same.
- [ ] **[Manual]** Sort by "Most used" / "Least used" — confirm ordering reflects actual usage counts (you can cross-check against the Detail View's usage count for a couple of images).
- [ ] **[Manual]** Filter by an upload date range — confirm older/newer images are correctly included/excluded.
- [ ] **[Manual]** With all filters cleared, confirm pagination works past the first page (24 per page).

## 4. Detail View & Metadata Editing

- [ ] **[Manual]** Open an image's detail page — confirm Title, Alt Text, Caption, Description, File Size, Width, Height, MIME Type, Upload Date, and Usage Count all display per the brief's spec.
- [ ] **[Manual]** Specifically confirm Width/Height display correctly — these are read from the actual file via `getimagesize()` (Spatie's own model has no width/height columns at all, confirmed against the package source in Phase 7), so this is worth a deliberate check on a few different images.
- [ ] **[Manual]** Edit Title/Alt Text/Caption/Description and save — confirm changes persist and the grid's display title updates to match.
- [ ] **[Manual]** Assign categories and tags from the detail view — confirm both save correctly and the grid/filter sidebar reflect the change.
- [ ] **[Manual]** For an image attached to a real Destination/Tour/Page/BlogPost, confirm "Used In" lists the correct location(s) with a working "unlink" button per location.
- [ ] **[Manual]** Click "unlink" on one usage of a multi-use image — confirm only that usage is removed, the image stays in the library, and remaining usages are untouched.

## 5. Deletion & Usage Protection

- [ ] **[Manual]** Attempt to delete an image that's currently in use (via the Detail View's disabled Delete button, and via the grid's delete icon) — confirm both correctly block deletion and explain why.
- [ ] **[Manual]** Unlink an image from every location it's used in, then delete it — confirm it deletes successfully once truly unused.
- [ ] **[Manual]** Confirm a deleted image's files (original + all conversions) are actually removed from disk, not just the database row.

## 6. Categories & Tags Management

- [ ] **[Manual]** Create a top-level category (e.g. "Destinations"), then a child category under it (e.g. "Kilimanjaro") — confirm the nesting displays correctly in both the management page and the filter sidebar.
- [ ] **[Manual]** Edit a category and try to set it as its own parent — confirm it's excluded from the dropdown (per the Phase 5 Form Request rule) and can't be selected at all.
- [ ] **[Manual]** Delete a parent category that has children — confirm the children are also deleted (cascade, by design — see Phase 7's explicit callout of this), but confirm separately that the *images* that were in those categories are NOT deleted, just unassigned.
- [ ] **[Manual]** Create, rename, and delete a tag — confirm usage counts update correctly throughout.

## 7. Media Picker (`<x-media-picker>`)

- [ ] **[Manual]** On a Destination's edit page, open the picker, search for an image, select it, and save — confirm `hero_image_id` is set and the destination's public-facing hero now shows that image.
- [ ] **[Manual]** Change the picker selection to a different image and save — confirm the old usage is unlinked (check the old image's Detail View — it should no longer list this destination) and the new one is recorded.
- [ ] **[Manual]** Repeat the picker flow on a Page (both hero and story images), a BlogPost (featured image, both create and edit), and a TourPackage (hero image, both create and edit).
- [ ] **[Manual]** Open two different pickers on the same page (if any view has more than one) and confirm selecting in one never affects the other.
- [ ] **[Manual]** Confirm the picker and the original direct-upload `<input type="file">` field can both be used independently — uploading a new file directly should still work exactly as it did before this feature existed, since the two paths were built to coexist rather than one replacing the other.

## 8. `<x-media-image>` Component

- [ ] **[Manual]** Use `<x-media-image :image="..." />` somewhere on the public-facing site and confirm it renders a `<picture>` element with a WebP source and a working `srcset`.
- [ ] **[Manual]** Pass `:image="null"` and confirm it renders nothing (no broken image icon).
- [ ] **[Manual]** Inspect the rendered `srcset` for an older, pre-backfill image versus a freshly uploaded one — both should have the full set after running the backfill command.

## 9. Automated Tests

- [x] **[Verified]** `php vendor/bin/phpunit tests/Unit` — 7 tests, 14 assertions, all passing. Covers `ImageProcessorService`'s validation constants and `MediaLibraryController`'s `ALLOWED_USAGE_MODELS` allow-list + `aliasForModel()` reverse lookup (the Phase 5 security fix), since both are pure logic with no database dependency.
- [ ] **[Manual]** `php vendor/bin/phpunit tests/Feature` — could not be run in the build environment (no MySQL server available, and `phpunit.xml`'s SQLite-in-memory lines are commented out). Run this yourself against a real database connection; none of the existing Feature tests are Media-Library-specific yet (they're Breeze's default auth scaffolding tests), so this mainly confirms the rest of the app isn't broken, not the Media Library itself.

---

## Critical Pre-Existing Issues Found During Testing (Not Introduced by This Feature)

These were discovered while actually running the full migration chain end-to-end in Phase 10 — both are real, both predate this work, and **both currently break a fresh installation**:

1. **`tour_destinations` table is created by two separate migrations.** `2026_02_04_093124_create_tour_destinations_table.php` creates a near-empty stub (`id` + `timestamps` only); `2026_02_07_221053_create_tour_destinations_table.php` creates the real, fully-formed pivot table. Running `php artisan migrate:fresh` fails outright at the second one with "table already exists." The production database (per the SQL dump) has the full structure, suggesting its migration history diverged from what's in the repo today. **A fresh clone of this repository cannot currently complete initial setup.**
2. **A migration uses `Schema::table(...)->change()` without `doctrine/dbal` installed.** `2026_02_10_093021_change_meta_fields_to_string_in_tour_packages.php` modifies existing columns, which Laravel requires Doctrine DBAL for, regardless of database driver. It's not in `composer.json` and not in `vendor/`. **This fails on every driver, not just the SQLite used for testing here** — confirmed by checking the actual error, which references DBAL directly rather than anything SQLite-specific.

Both were intentionally *not* fixed as part of this work — they're outside the Media Library's scope, and the safest fix for #1 (removing the stub migration) is a meaningful, potentially environment-specific change that needs your sign-off rather than a unilateral decision made mid-feature-build. Recommended next step: decide whether to delete the stub migration (if no other environment's migration history depends on it existing) and either add `doctrine/dbal` to `composer.json` or rewrite the `change()` migration to use raw SQL instead.

## Other Findings, Informational Only

- `composer.json` declares `filament/filament: "*"` as a dependency, and it's genuinely installed (`v2.17.59`, confirmed via `composer show`) with its service providers actively auto-registering on every request (confirmed in `bootstrap/cache/packages.php`) — but there is no Filament usage anywhere in the application code. This is dead weight costing boot time and disk space for zero benefit. Recommend removing it via `composer remove filament/filament` once you've confirmed nothing was ever built on it that isn't visible in the current codebase.
- `composer.json` requires `laravel/framework: ^10.10`, and the actually-installed version is `10.50.0` — confirmed via the framework's own `Application::VERSION` constant, and corroborated structurally by the presence of `app/Console/Kernel.php` and `app/Http/Kernel.php` (both removed in Laravel 11+). **This is genuinely a Laravel 10 application, not Laravel 12** as stated in the original brief. This didn't cause any incompatibility with the work in this build (Spatie Media Library v10's own requirements — `illuminate/*: ^9.18|^10.0` — match what's installed), but it's worth knowing precisely for any future upgrade planning.
- `intervention/image` (v2.7.2) is installed as a transitive dependency (`spatie/laravel-medialibrary` → `spatie/image` → `league/glide` → `intervention/image`), confirmed via `composer depends`. This is why the brief's call for "Intervention Image" processing didn't need a separate, explicit integration in this build (see Phase 1 analysis) — it's already doing the real work underneath Spatie's conversion pipeline.
