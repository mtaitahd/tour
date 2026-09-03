<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\TourPackage;
use App\Services\ImageProcessorService;
use App\Services\MediaLibraryService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * One-time backfill for the new Media Library on top of the project's existing media.
 *
 * Run manually, once, after the Phase 2 migrations: php artisan media:backfill-library
 *
 * What it does, per the Phase 1 analysis report:
 *   1. Creates an empty media_meta row for every existing media row that doesn't have
 *      one yet (ready for an admin to fill in title/alt text/caption/description later).
 *   2. Regenerates conversions for every media row missing the current standard set
 *      (thumb-webp/medium-webp/large-webp) — this is the slow part: ~351 images across
 *      251 BlogPost + ~100 others with the older, inconsistent conversion set.
 *   3. Creates media_usages rows for every existing Spatie collection attachment
 *      (Destination/TourPackage/Page/BlogPost hero, gallery, etc.).
 *   4. Walks TourPackage.itinerary and TourPackage.extra_sections JSON columns and
 *      creates media_usages rows for the image_id references found inside them — the
 *      informal "already-reused" pattern flagged in Phase 1. Legacy entries that store
 *      a raw file path instead of an image_id (a small number of older extra_sections
 *      rows — see Phase 4 notes) are skipped and reported, since they have no
 *      corresponding media row to link to.
 *
 * Deliberately synchronous (no queue) and chunked, with progress output, per your Phase
 * 1 decision — you run this yourself, once, whenever convenient.
 */
class BackfillMediaLibrary extends Command
{
    protected $signature = 'media:backfill-library
        {--chunk=25 : How many media rows to process per batch when regenerating conversions}
        {--skip-conversions : Skip the (slow) conversion regeneration step}
        {--dry-run : Report what would happen without writing anything}';

    protected $description = 'Backfill media_meta and media_usages for existing media, and regenerate missing conversions.';

    protected ImageProcessorService $imageProcessor;

    protected MediaLibraryService $mediaLibrary;

    public function __construct(ImageProcessorService $imageProcessor, MediaLibraryService $mediaLibrary)
    {
        parent::__construct();

        $this->imageProcessor = $imageProcessor;
        $this->mediaLibrary = $mediaLibrary;
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in --dry-run mode: no database writes, no conversion regeneration.');
        }

        $this->backfillMediaMeta($dryRun);
        $this->backfillCollectionUsages($dryRun);
        $this->backfillJsonEmbeddedUsages($dryRun);

        if (! $this->option('skip-conversions')) {
            $this->regenerateMissingConversions($dryRun);
        } else {
            $this->comment('Skipping conversion regeneration (--skip-conversions).');
        }

        $this->newLine();
        $this->info('Backfill complete.');

        return self::SUCCESS;
    }

    /**
     * Step 1: ensure every media row has a (possibly empty) media_meta row.
     */
    protected function backfillMediaMeta(bool $dryRun): void
    {
        $this->info('Step 1/4 — Backfilling media_meta...');

        $total = GalleryImage::doesntHave('meta')->count();

        if ($total === 0) {
            $this->line('  Nothing to do — every media row already has metadata.');
            return;
        }

        $this->line("  {$total} media rows need an empty metadata row.");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        GalleryImage::doesntHave('meta')
            ->chunkById((int) $this->option('chunk'), function (Collection $images) use ($bar) {
                foreach ($images as $image) {
                    $image->meta()->create([
                        'title' => null,
                        'alt_text' => null,
                        'caption' => null,
                        'description' => null,
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
    }

    /**
     * Step 2: record a media_usages row for every existing Spatie collection attachment
     * across the four content models. This covers hero/gallery/featured_image/story/
     * safari_car_images/itinerary_images — every collection-based attachment that already
     * exists, regardless of model.
     */
    protected function backfillCollectionUsages(bool $dryRun): void
    {
        $this->info('Step 2/4 — Backfilling usage records for existing collection attachments...');

        $created = 0;

        // Every model that owns media, with the collections it actually defines.
        $modelsToScan = [
            Destination::class => ['hero', 'gallery'],
            TourPackage::class => ['hero', 'gallery', 'safari_car_images', 'itinerary_images', 'extra_sections'],
            Page::class => ['hero', 'story', 'gallery'],
            BlogPost::class => ['featured_image', 'gallery'],
        ];

        foreach ($modelsToScan as $modelClass => $collections) {
            $modelClass::query()->chunkById((int) $this->option('chunk'), function (Collection $records) use ($collections, $dryRun, &$created) {
                foreach ($records as $record) {
                    foreach ($collections as $collection) {
                        foreach ($record->getMedia($collection) as $media) {
                            if (! $dryRun) {
                                $this->mediaLibrary->recordUsage($media->id, $record, $collection);
                            }
                            $created++;
                        }
                    }
                }
            });
        }

        $this->line("  {$created} collection-based usage record(s) " . ($dryRun ? 'would be created' : 'created/confirmed') . '.');
    }

    /**
     * Step 3: walk TourPackage.itinerary and TourPackage.extra_sections JSON columns,
     * which already contain informal image_id references (see Phase 1 analysis), and
     * record a media_usages row for each one that resolves to a real media row.
     *
     * A small number of older extra_sections entries store a raw file path in an
     * 'image' field instead of an image_id (a legacy pattern that predates the image_id
     * convention) — these have no media row to link to and are skipped, with a count
     * reported at the end so you know how many exist.
     */
    protected function backfillJsonEmbeddedUsages(bool $dryRun): void
    {
        $this->info('Step 3/4 — Backfilling usage records for JSON-embedded image references...');

        $created = 0;
        $skippedLegacyPaths = 0;
        $skippedMissingMedia = 0;

        TourPackage::query()->chunkById((int) $this->option('chunk'), function (Collection $tours) use ($dryRun, &$created, &$skippedLegacyPaths, &$skippedMissingMedia) {
            foreach ($tours as $tour) {
                // --- itinerary: each day may have image_ids[] (day-level images)
                //     and accommodations[].image_id (per-tier accommodation images) ---
                foreach ((array) $tour->itinerary as $dayIndex => $day) {
                    $dayLabel = "itinerary.day_{$dayIndex}";

                    foreach ((array) ($day['image_ids'] ?? []) as $mediaId) {
                        $this->recordIfMediaExists(
                            (int) $mediaId,
                            $tour,
                            $dayLabel,
                            $dryRun,
                            $created,
                            $skippedMissingMedia
                        );
                    }

                    foreach ((array) ($day['accommodations'] ?? []) as $tierIndex => $accommodation) {
                        if (empty($accommodation['image_id'])) {
                            continue;
                        }

                        $this->recordIfMediaExists(
                            (int) $accommodation['image_id'],
                            $tour,
                            "{$dayLabel}.accommodation_{$tierIndex}",
                            $dryRun,
                            $created,
                            $skippedMissingMedia
                        );
                    }
                }

                // --- extra_sections: each entry may have image_id (newer) or a raw
                //     'image' file path string (older, legacy — no media row exists) ---
                foreach ((array) $tour->extra_sections as $sectionIndex => $section) {
                    $context = "extra_sections.{$sectionIndex}";

                    if (! empty($section['image_id'])) {
                        $this->recordIfMediaExists(
                            (int) $section['image_id'],
                            $tour,
                            $context,
                            $dryRun,
                            $created,
                            $skippedMissingMedia
                        );
                    } elseif (! empty($section['image'])) {
                        // Legacy raw path, predates the media table reference. Nothing to
                        // link — counted so you can see the scale of this if you want to
                        // address it separately (e.g. importing these as real media rows).
                        $skippedLegacyPaths++;
                    }
                }
            }
        });

        $this->line('  ' . $created . ' JSON-embedded usage record(s) ' . ($dryRun ? 'would be created' : 'created/confirmed') . '.');

        if ($skippedMissingMedia > 0) {
            $this->warn("  {$skippedMissingMedia} image_id reference(s) pointed at a media row that no longer exists — skipped.");
        }

        if ($skippedLegacyPaths > 0) {
            $this->warn("  {$skippedLegacyPaths} extra_sections entr(y/ies) use a legacy raw file path instead of an image_id — skipped (not linkable to a media row).");
        }
    }

    /**
     * Helper: record a usage only if the referenced media row actually exists — itinerary/
     * extra_sections JSON can in principle reference a media id that was since deleted
     * directly (bypassing the usage tracking this very backfill is establishing).
     */
    protected function recordIfMediaExists(
        int $mediaId,
        TourPackage $tour,
        string $context,
        bool $dryRun,
        int &$created,
        int &$skippedMissingMedia
    ): void {
        if ($mediaId <= 0) {
            return;
        }

        if (! GalleryImage::query()->whereKey($mediaId)->exists()) {
            $skippedMissingMedia++;
            return;
        }

        if (! $dryRun) {
            $this->mediaLibrary->recordUsage($mediaId, $tour, $context);
        }

        $created++;
    }

    /**
     * Step 4: regenerate conversions for every media row missing the current standard
     * set. This is the slow part — ~351 images per the Phase 1 estimate (251 BlogPost,
     * which had no conversions defined at all until Phase 3, plus ~100 others uploaded
     * before the WebP conversion set existed). Runs synchronously, chunked, with a
     * progress bar, per your Phase 1 decision to trigger this manually rather than via
     * a queue worker.
     */
    protected function regenerateMissingConversions(bool $dryRun): void
    {
        $this->info('Step 4/4 — Regenerating missing conversions (this is the slow step)...');

        $requiredConversions = ['thumb', 'medium', 'thumb-webp', 'medium-webp', 'large-webp'];

        $needsRegeneration = GalleryImage::query()
            ->get()
            ->filter(function (GalleryImage $image) use ($requiredConversions) {
                $generated = array_keys(array_filter($image->generated_conversions ?? []));
                return count(array_diff($requiredConversions, $generated)) > 0;
            });

        $total = $needsRegeneration->count();

        if ($total === 0) {
            $this->line('  Nothing to do — every media row already has the full conversion set.');
            return;
        }

        $this->line("  {$total} media row(s) are missing at least one conversion.");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($needsRegeneration as $image) {
            $this->imageProcessor->regenerateConversions($image);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
