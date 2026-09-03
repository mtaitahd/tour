<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — cost-line items belonging to a price calculation. Rates are
     * stored separately in pricing_item_rates (one per season). Columns such
     * as nights / room_occupancy / vehicle_capacity are only meaningful for the
     * charging bases that use them; enforcement happens in the engine.
     */
    public function up(): void
    {
        Schema::create('pricing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_calculation_id')->constrained('price_calculations')->cascadeOnDelete();
            $table->string('predefined_key', 60)->nullable();
            $table->string('name', 160);
            $table->string('currency', 3)->default('USD');
            $table->string('charging_basis', 40);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->boolean('taxable')->default(false);
            $table->boolean('included')->default(true);
            $table->boolean('shared_across_levels')->default(true);
            $table->string('level_key', 30)->nullable();
            $table->unsignedInteger('nights')->nullable();
            $table->unsignedInteger('room_occupancy')->nullable();
            $table->unsignedInteger('vehicle_capacity')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_items');
    }
};