<?php

namespace App\Console\Commands;

use App\Models\Destination;
use App\Models\TourPackage;
use App\Services\MediaLibraryService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * One-time migration for the switch to picker-only galleries (per your decision: full
 * replace of direct upload with multi-select picker for Destination/TourPackage
 * gallery and TourPackage safari_car_images). Run once after deploying the new
 * picker-based gallery forms.
 *
 * What it does: for every Destination and TourPackage, reads whatever images are
 * already sitting in the relevant Spatie collection (in their existing display order)
 * and records them as an ordered media_usages set via
 * MediaLibraryService::setOrderedUsages(), under the SAME context name the new picker
 * forms read from ('gallery', 'safari_car_images'). This does not touch the Spatie
 * collection itself or delete any files — it's purely additive, so if anything looks
 * wrong after running it, the original images are still exactly where they were.
 *
 * Does NOT cover itinerary day images or extra_sections images — those were never
 * stored as flat Spatie collections in the first place (see Phase 1/4 analysis: they
 * live as image_id references inside tour_packages.itinerary / extra_sections JSON
 * columns), and the Phase 4 backfill command already recorded media_usages rows for
 * them under contexts like 'itinerary.day_2' / 'extra_sections.0'. Those two are
 * handled by the new itinerary/extra-section form sections reading directly from
 * media_usages, not by this command.
 */
class MigrateGalleriesToLibrary extends Command
{
    protected $signature = 'media:migrate-galleries
        {--dry-run : Report what would happen without writing anything}';

    protected $description = 'Migrate existing Destination/TourPackage gallery and safari_car_images collections into the new picker-based ordered usage system.';

    public function __construct(protected MediaLibraryService $mediaLibrary)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in --dry-run mode: no database writes.');
        }

        $this->migrateCollection(Destination::class, 'gallery', $dryRun);
        $this->migrateCollection(TourPackage::class, 'gallery', $dryRun);
        $this->migrateCollection(TourPackage::class, 'safari_car_images', $dryRun);

        $this->newLine();
        $this->info('Gallery migration complete.');

        return self::SUCCESS;
    }

    /**
     * Migrate one Spatie collection, across every record of one model, into the
     * ordered media_usages system under the same context name.
     */
    protected function migrateCollection(string $modelClass, string $collection, bool $dryRun): void
    {
        $this->info("Migrating {$modelClass}::{$collection}...");

        $migrated = 0;
        $skippedEmpty = 0;
        $skippedAlreadyMigrated = 0;

        $modelClass::query()->chunkById(25, function (Collection $records) use ($collection, $dryRun, &$migrated, &$skippedEmpty, &$skippedAlreadyMigrated) {
            foreach ($records as $record) {
                // Skip records that already have an ordered usage set for this context
                // — running this command twice (e.g. after an interrupted first run)
                // should never duplicate or overwrite anything that was already
                // migrated, since a record's gallery may have already been
                // re-ordered/edited via the new picker since the first run.
                //
                // NOTE: this must be `continue`, not `return`, inside this foreach —
                // `return` here would exit the entire chunk callback (chunkById
                // interprets a truthy return as "keep chunking", but it would skip
                // every remaining record in the current chunk of 25, not just this
                // one). Caught during review before this command was ever run for
                // real, since silently skipping 24 records per chunk would have been
                // a serious, hard-to-notice bug in a one-time data migration.
                $existing = $this->mediaLibrary->orderedImagesFor($record, $collection);
                if ($existing->isNotEmpty()) {
                    $skippedAlreadyMigrated++;
                    continue;
                }

                $media = $record->getMedia($collection)->sortBy('order_column');

                if ($media->isEmpty()) {
                    $skippedEmpty++;
                    continue;
                }

                $mediaIds = $media->pluck('id')->all();

                if (! $dryRun) {
                    $this->mediaLibrary->setOrderedUsages($record, $collection, $mediaIds);
                }

                $migrated++;
            }
        });

        $this->line("  {$migrated} record(s) migrated, {$skippedEmpty} had no images, {$skippedAlreadyMigrated} already had a migrated gallery.");
    }
}
