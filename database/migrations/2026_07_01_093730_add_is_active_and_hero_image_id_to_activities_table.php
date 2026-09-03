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
        Schema::table('activities', function (Blueprint $table) {
            // Already present in Activity's $fillable/$casts, but never actually
            // added to the schema — this model had no admin UI (and therefore no
            // real writes) until now, so the mismatch was never triggered.
            $table->boolean('is_active')->default(true)->after('is_featured');

            // Replaces the never-implemented plain 'image' string column (also in
            // the old $fillable but with no matching column and no upload path
            // anywhere) with the same Media Library picker pattern used everywhere
            // else — picker-only, no legacy fallback needed for the same reason as
            // Accommodation/Testimonial.
            $table->unsignedBigInteger('hero_image_id')->nullable()->after('icon');
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
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['hero_image_id']);
            $table->dropColumn(['hero_image_id', 'is_active']);
        });
    }
};
