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
            // JSON: [{question, answer}, ...] — same shape/convention as
            // tour_packages.faqs, rendered with the same accordion partial.
            $table->json('faqs')->nullable()->after('description');

            // Admin pastes either a plain URL (rendered as a "Read Reviews"
            // link/button) or a third-party embed snippet (e.g. Elfsight) here.
            // Free-form so either works without a schema change; the frontend
            // decides how to render it based on whether it looks like a bare URL.
            $table->text('reviews_embed')->nullable()->after('faqs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['faqs', 'reviews_embed']);
        });
    }
};
