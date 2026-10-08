<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grouped taxonomy support for tour categories.
     *
     * Adds a `type` discriminator so one table can hold several independent
     * classification groups (country, region, tour_type, duration, …) plus the
     * legacy flat "category" rows that power the public /{slug} landing pages.
     *
     * Existing rows are NOT modified: the column default ('category') keeps the
     * two current SEO categories exactly as they are, so /tanzania-tours and
     * /kilimanjaro-climbing, their sections, sitemap entries, footer/header
     * links and the tour_category_tour_package pivot all keep working.
     *
     * `status` lets a group option be hidden from pickers without deleting it
     * (which would detach it from tours).
     */
    public function up(): void
    {
        Schema::table('tour_categories', function (Blueprint $table) {
            $table->string('type', 32)->default('category')->after('name');
            $table->string('status', 16)->default('active')->after('type');

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('tour_categories', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
            $table->dropColumn(['type', 'status']);
        });
    }
};
