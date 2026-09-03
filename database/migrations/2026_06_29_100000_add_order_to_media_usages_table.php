<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_usages', function (Blueprint $table) {
            // Needed for the multi-select picker integration (galleries, safari car
            // images, itinerary day images, extra sections) — a single context like
            // 'gallery' can now have several media_usages rows (one per image), and
            // this column lets them display in a chosen sequence rather than just
            // insertion/id order. Defaults to 0 so every pre-existing row (hero,
            // featured_image, story — all single-image contexts where order never
            // mattered) is unaffected.
            $table->unsignedInteger('order')->default(0)->after('context');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_usages', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
