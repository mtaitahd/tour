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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();           // example: about-us, terms-conditions
            $table->string('title');                    // Page title
            $table->text('meta_description')->nullable();
            $table->longText('content')->nullable();    // Main rich text content
            $table->string('hero_image')->nullable();   // Path to image (we'll improve later)
            $table->boolean('is_published')->default(true);
            $table->integer('order')->default(999);     // For sorting in menus if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
