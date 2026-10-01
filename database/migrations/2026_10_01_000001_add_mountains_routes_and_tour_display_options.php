<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mountains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mountain_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mountain_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(999);
            $table->timestamps();
            $table->unique(['mountain_id', 'name']);
        });

        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('tour_format', 20)->nullable()->after('tour_type');
            $table->foreignId('mountain_id')->nullable()->after('tour_format')->constrained('mountains')->nullOnDelete();
            $table->json('mountain_route_ids')->nullable()->after('mountain_id');
            $table->json('related_tour_ids')->nullable()->after('mountain_route_ids');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropForeign(['mountain_id']);
            $table->dropColumn(['tour_format', 'mountain_id', 'mountain_route_ids', 'related_tour_ids']);
        });
        Schema::dropIfExists('mountain_routes');
        Schema::dropIfExists('mountains');
    }
};
