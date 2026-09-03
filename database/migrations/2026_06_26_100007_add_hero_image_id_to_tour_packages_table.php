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
        Schema::table('tour_packages', function (Blueprint $table) {
            // Additive: gallery, safari_car_images, itinerary_images, and extra_sections
            // continue to work exactly as before via Spatie collections and the existing
            // JSON image_id references. Only the single hero image gets a dedicated FK,
            // since it's the one place a clean "pick from library" replacement is needed first.
            $table->unsignedBigInteger('hero_image_id')->nullable();

            $table->foreign('hero_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropForeign(['hero_image_id']);
            $table->dropColumn('hero_image_id');
        });
    }
};
