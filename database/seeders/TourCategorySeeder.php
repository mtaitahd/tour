<?php

namespace Database\Seeders;

use App\Models\TourCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the grouped tour-classification taxonomy (countries, regions, tour
 * types, durations) into the existing tour_categories table using the `type`
 * discriminator added by 2026_10_08_000001.
 *
 * Idempotent and non-destructive: a group is only filled when it is completely
 * empty, so re-running never touches rows the admin has added, renamed or
 * removed — and the two legacy 'category' rows (tanzania-tours,
 * kilimanjaro-climbing) are never touched because they belong to a different
 * type entirely.
 *
 * Physical rating, tour level, available months and duration_days deliberately
 * get NO rows here: they remain hard columns on tour_packages (Option A) and
 * keep their enum/partial-driven option lists.
 */
class TourCategorySeeder extends Seeder
{
    /** type => [labels...] */
    public const GROUPS = [
        'country'   => ['Tanzania', 'Kenya', 'Uganda', 'Rwanda'],
        'region'    => ['Africa', 'Europe', 'Asia'],
        'tour_type' => ['Private', 'Group'],
        'duration'  => ['1 Day', '2 Days', '3 Days', '4 Days'],
    ];

    public function run(): void
    {
        foreach (self::GROUPS as $type => $labels) {
            if (TourCategory::where('type', $type)->exists()) {
                continue; // group already managed — leave it alone
            }

            foreach ($labels as $i => $label) {
                TourCategory::create([
                    'name'   => $label,
                    'slug'   => TourCategory::uniqueSlug(Str::slug($label) ?: 'category'),
                    'type'   => $type,
                    'status' => 'active',
                    'order'  => $i + 1,
                ]);
            }
        }
    }
}
