<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — a stored price calculation run. Holds the validated inputs and
     * the serialized engine output. Deleting a tour cascades to its
     * calculations; the engine never writes here directly (Phase 2 layer).
     */
    public function up(): void
    {
        Schema::create('price_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->string('package_duration_type', 20);
            $table->string('tour_type', 60);
            $table->string('package_category', 30)->nullable();
            $table->boolean('tax_enabled')->default(true);
            $table->decimal('tax_percentage', 5, 2)->default(18);
            $table->string('markup_type', 10)->default('percent');
            $table->decimal('markup_value', 12, 2)->default(0);
            $table->string('rounding_rule', 10)->default('none');
            $table->json('breakdown')->nullable();
            $table->json('results')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_calculations');
    }
};