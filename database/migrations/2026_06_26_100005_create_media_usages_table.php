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
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id');             // references Spatie's media.id

            // What the image is attached to.
            $table->string('model_type');                       // e.g. App\Models\Destination
            $table->unsignedBigInteger('model_id');

            // Where on that model it's used. For standard Spatie collection attachments
            // (hero, gallery, etc.) this mirrors media.collection_name. For images referenced
            // only inside JSON columns (e.g. tour_packages.itinerary[].image_id,
            // extra_sections[].image_id) this stores a descriptive path instead, since those
            // references have no real Spatie collection of their own.
            $table->string('context');                          // e.g. 'hero', 'gallery', 'itinerary.day_2', 'extra_sections.0'

            $table->timestamps();

            $table->foreign('media_id')
                  ->references('id')->on('media')
                  ->onDelete('cascade');

            $table->index(['model_type', 'model_id']);
            $table->unique(['media_id', 'model_type', 'model_id', 'context'], 'media_usages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
