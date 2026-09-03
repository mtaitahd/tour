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
        Schema::table('pages', function (Blueprint $table) {
            // Page has two single-image Spatie collections today: 'hero' and 'story'.
            // Both get a dedicated FK so the Media Picker can target either one.
            $table->unsignedBigInteger('hero_image_id')->nullable();
            $table->unsignedBigInteger('story_image_id')->nullable();

            $table->foreign('hero_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');

            $table->foreign('story_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['hero_image_id']);
            $table->dropForeign(['story_image_id']);
            $table->dropColumn(['hero_image_id', 'story_image_id']);
        });
    }
};
