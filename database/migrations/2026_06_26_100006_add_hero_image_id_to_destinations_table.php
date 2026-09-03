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
        Schema::table('destinations', function (Blueprint $table) {
            // Additive: existing hero/gallery images stay attached via Spatie's media table
            // exactly as before. This column lets the new Media Picker select an existing
            // library image directly, without re-uploading.
            $table->unsignedBigInteger('hero_image_id')->nullable();

            $table->foreign('hero_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropForeign(['hero_image_id']);
            $table->dropColumn('hero_image_id');
        });
    }
};
