<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Converts tour_packages.status from a MariaDB ENUM to a validated VARCHAR(30).
 *
 * This deliberately avoids relying on Laravel's change() for enums (doctrine is
 * unreliable for MariaDB enum alters). The Phase 0 statuses remain exactly
 * draft / published / archived; the new review statuses are added in the
 * Package Editor workflow phase.
 *
 * Rollback is independently reversible WITHOUT depending on audit_logs (which
 * is dropped before this migration during a full rollback). It maps new status
 * values explicitly, preserves published/archived, and aborts on any unknown
 * value instead of silently truncating it.
 */
return new class extends Migration
{
    private const ORIGINAL = ['draft', 'published', 'archived'];
    private const FORWARD  = ['submitted', 'changes_requested', 'rejected'];

    public function up(): void
    {
        DB::statement("ALTER TABLE `tour_packages` CHANGE `status` `status` VARCHAR(30) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        $allowed = array_merge(self::ORIGINAL, self::FORWARD);

        $unknown = DB::table('tour_packages')
            ->whereNotIn('status', $allowed)
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        if ($unknown->count() > 0) {
            throw new \RuntimeException(
                'Cannot roll back status conversion: unsupported status value(s) present: '.$unknown->implode(', ').'. '
                .'Resolve them before rolling back.'
            );
        }

        DB::table('tour_packages')
            ->whereIn('status', ['submitted', 'changes_requested'])
            ->update(['status' => 'draft']);

        DB::table('tour_packages')
            ->where('status', 'rejected')
            ->update(['status' => 'archived']);

        DB::statement(
            "ALTER TABLE `tour_packages` CHANGE `status` `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'"
        );
    }
};