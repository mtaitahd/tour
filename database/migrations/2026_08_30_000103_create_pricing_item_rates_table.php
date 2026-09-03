<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — a single per-season rate for one pricing item. Exactly one
     * rate per (item, season) is enforced. Only HIGH and LOW_WET are used;
     * Shoulder Season is not part of the pricing model.
     */
    public function up(): void
    {
        Schema::create('pricing_item_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_item_id')->constrained('pricing_items')->cascadeOnDelete();
            $table->string('season_code', 10);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['pricing_item_id', 'season_code'], 'pricing_item_rates_item_season_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_item_rates');
    }
};