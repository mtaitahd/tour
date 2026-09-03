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
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->integer('duration_days')->nullable();
            $table->integer('duration_nights')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('physical_rating', ['relaxing', 'easy', 'moderate', 'complex', 'super_complex'])->default('moderate');
            $table->enum('tour_level', ['budget_camping', 'budget_lodge', 'mid_range', 'luxury'])->default('mid_range');
            $table->boolean('is_group_departure')->default(false);
            $table->json('departure_dates')->nullable();           // array of dates ['2026-06-15', ...]
            $table->string('starting_point')->nullable();
            $table->string('ending_point')->nullable();
            $table->json('map_data')->nullable();                  // {center: {lat,lng}, zoom: 8, markers: [...]}
            $table->text('overview')->nullable();
            $table->json('highlights')->nullable();                // ["See Big Five", "Sunrise at summit"]
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('itinerary')->nullable();                 // [{day:1, title:"", description:"", accommodation:"", meals:""}]
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('order')->default(999);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
