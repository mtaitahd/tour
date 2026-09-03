<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotent reconciliation: video_url / embed_map existed in the original
     * production schema (and in the restored live database) but were never part
     * of any committed migration, so fresh/testing schemas lacked them. This
     * adds them only when missing, matching the live column types and placement.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tour_packages')) {
            return;
        }

        if (!Schema::hasColumn('tour_packages', 'video_url')) {
            Schema::table('tour_packages', function (Blueprint $table) {
                $table->string('video_url', 500)->nullable()->after('duration_nights');
            });
        }

        if (!Schema::hasColumn('tour_packages', 'embed_map')) {
            Schema::table('tour_packages', function (Blueprint $table) {
                $table->text('embed_map')->nullable()->after('video_url');
            });
        }
    }

    /**
     * No-op: these columns predate this migration and carry content in
     * production; dropping here would be destructive and is never intended.
     */
    public function down(): void
    {
    }
};