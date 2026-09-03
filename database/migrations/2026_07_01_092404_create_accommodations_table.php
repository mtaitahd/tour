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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // Free-text tier label (e.g. "Luxury Lodge", "Tented Camp", "Boutique
            // Hotel", "Beach Resort") — same convention as destinations.type, kept
            // free-form rather than an enum so new tiers don't need a migration.
            $table->string('tier')->nullable();

            // Optional link to an existing Destination, for cross-referencing (e.g.
            // "Accommodations near Serengeti"). Nullable — a lodge can be added before
            // its destination page exists, or without one at all.
            $table->foreignId('destination_id')->nullable()
                  ->constrained('destinations')->onDelete('set null');

            // Free-text fallback location, used when there's no destination_id, or as
            // a more specific label alongside one (e.g. "Ngorongoro Crater Rim").
            $table->string('location')->nullable();

            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // ["Free WiFi", "Swimming Pool", ...]

            $table->decimal('price_from', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            // Media Library only — this is a brand-new model with no legacy uploads to
            // fall back to, so unlike Destination/TourPackage there's no dual-path
            // heroUrl() fallback needed; hero_image_id is the only hero source.
            $table->unsignedBigInteger('hero_image_id')->nullable();
            $table->foreign('hero_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
            // Gallery images are tracked entirely via media_usages (context =
            // 'gallery'), same as Destination's gallery — no Spatie media collection
            // needed since there's no legacy gallery to support here either.

            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(999);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
