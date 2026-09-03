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
        Schema::table('users', function (Blueprint $table) {
            // Additive, same pattern as hero_image_id on destinations/tour_packages:
            // the existing 'avatar' string column (legacy direct-upload path) stays
            // untouched and keeps working as a fallback. This column lets the Media
            // Picker select an existing library image as the profile picture instead.
            $table->unsignedBigInteger('avatar_image_id')->nullable()->after('avatar');

            $table->foreign('avatar_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_image_id']);
            $table->dropColumn('avatar_image_id');
        });
    }
};
