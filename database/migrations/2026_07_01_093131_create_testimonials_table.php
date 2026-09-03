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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable(); // e.g. "United Kingdom"
            $table->unsignedTinyInteger('rating')->default(5); // 1–5
            $table->text('content');

            // Optional links so the same testimonial pool can be filtered per-context:
            // shown generally on the homepage, or scoped to a specific tour's page
            // (replacing the "Reviews (Coming Soon)" placeholder there) or destination.
            // Null on both = general/homepage-only.
            $table->foreignId('tour_package_id')->nullable()
                  ->constrained('tour_packages')->onDelete('set null');
            $table->foreignId('destination_id')->nullable()
                  ->constrained('destinations')->onDelete('set null');

            // Media Library only, same as Accommodation — no legacy uploads to fall
            // back to for a brand-new model.
            $table->unsignedBigInteger('avatar_image_id')->nullable();
            $table->foreign('avatar_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');

            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(999);
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
