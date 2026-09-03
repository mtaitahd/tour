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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g. "Wildlife Safari", "Kilimanjaro Climb"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();   // e.g. "isax isax-airplane" or font-awesome class
            $table->integer('order')->default(999);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('activity_tour_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
