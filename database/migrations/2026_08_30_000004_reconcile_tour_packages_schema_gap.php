<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconciles the schema so fresh databases match the verified production schema.
 *
 * The live tour_packages table already contains season_pricing, trip_details,
 * faqs and extra_sections, but the original migrations never created all of
 * them. This migration only adds a column when it is missing, and its down()
 * is intentionally a NO-OP: those columns may predate this migration and
 * contain production data, and must never be dropped during rollback.
 */
return new class extends Migration
{
    private const COLUMNS = ['season_pricing', 'trip_details', 'faqs', 'extra_sections'];

    public function up(): void
    {
        foreach (self::COLUMNS as $column) {
            if (! Schema::hasColumn('tour_packages', $column)) {
                Schema::table('tour_packages', function (Blueprint $table) use ($column) {
                    $table->longText($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // No-op: these columns predate this migration on production and may
        // contain data. Never drop existing content columns during rollback.
    }
};