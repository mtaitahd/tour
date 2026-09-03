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
        Schema::create('tour_category_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_category_id');
            $table->string('title');
            $table->longText('content')->nullable();

            // Picker-driven from the start (unlike TourPackage's extra_sections, which
            // began life as a JSON column with a raw upload and only later got
            // converted to the Media Library picker) — this is a brand-new table, so
            // there's no legacy data shape to carry forward. A nullable FK straight to
            // media.id, exactly like hero_image_id/featured_image_id on the four
            // content models.
            $table->unsignedBigInteger('image_id')->nullable();

            // Where this section renders relative to the tour grid on the category
            // listing page — per-section, not a single page-level setting, since a
            // category might want its "Overview" above the grid and its "Why Choose
            // This Region" below it.
            $table->enum('placement', ['above_grid', 'below_grid'])->default('below_grid');

            $table->integer('order')->default(999);
            $table->timestamps();

            $table->foreign('tour_category_id')
                  ->references('id')->on('tour_categories')
                  ->onDelete('cascade');

            $table->foreign('image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_category_sections');
    }
};
