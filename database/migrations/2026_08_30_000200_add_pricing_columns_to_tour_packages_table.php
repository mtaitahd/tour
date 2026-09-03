<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 additive pricing columns. These four fields describe how a tour
     * is priced in the NEW system; they are additive (nullable / defaulted) so
     * that every existing tour is preserved as-is. `pricing_source` defaults
     * to 'none' for brand-new rows; existing tours that carry legacy SILVER /
     * GOLD / PLATINUM season_pricing (stored in the JSON column) are labelled
     * 'legacy' so the admin form keeps showing them read-only until a Super
     * Admin explicitly sets up new pricing. Their season_pricing byte-content
     * is never touched here.
     */
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('package_duration_type', 20)->nullable()->after('tour_level');
            $table->string('tour_type', 40)->nullable()->after('package_duration_type');
            $table->string('package_category', 20)->nullable()->after('tour_type');
            $table->string('pricing_source', 16)->nullable(false)->default('none')->after('package_category');
        });

        DB::table('tour_packages')
            ->whereRaw("TRIM(COALESCE(season_pricing, '')) <> ''")
            ->update(['pricing_source' => 'legacy']);
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn(['package_duration_type', 'tour_type', 'package_category', 'pricing_source']);
        });
    }
};