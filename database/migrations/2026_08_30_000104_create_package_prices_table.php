<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — final per-level-per-season price results for a tour. At most
     * one row per (tour_package_id, level_key, season_code) — enforced by the
     * composite unique key, so duplicate identity rows are rejected at the
     * database level. Deleting the source tour cascades; deleting a
     * calculation nulls the reference instead of losing results; created_by /
     * updated_by null on user deletion. Legacy tour_packages.season_pricing is
     * never touched by this table.
     */
    public function up(): void
    {
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->string('package_category', 30)->nullable();
            $table->string('level_key', 30);
            $table->string('level_name', 80);
            $table->string('season_code', 10);
            $table->decimal('price_2p', 12, 2);
            $table->decimal('price_4p', 12, 2);
            $table->decimal('price_6p', 12, 2);
            $table->decimal('group_total_2p', 12, 2);
            $table->decimal('group_total_4p', 12, 2);
            $table->decimal('group_total_6p', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->foreignId('price_calculation_id')->nullable()->constrained('price_calculations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tour_package_id', 'level_key', 'season_code'], 'package_prices_tour_level_season_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};