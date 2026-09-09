<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Our Story images on the About page are multiple images, each with a caption.
     * `custom_data` already stores the Team Members grid and `stats_counters` stores
     * the stat rows, so this gets its own JSON column: [{image_id, caption}, ...].
     * The image ids mirror into media_usages (context = story_gallery) so the Media
     * Library can track/refuse deletion while an image is in use.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('story_gallery')->nullable()->after('custom_data');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('story_gallery');
        });
    }
};