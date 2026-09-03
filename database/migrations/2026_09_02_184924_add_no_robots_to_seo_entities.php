<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-entity "noindex" flag. When enabled for a tour / page / blog post it
     * excludes that item from /sitemap.xml and marks the page as noindex,
     * mirroring the `no_robots` column used by the legacy kizza-tours app.
     */
    public function up(): void
    {
        foreach (['tour_packages', 'pages', 'blog_posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'no_robots')) {
                    $table->boolean('no_robots')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['tour_packages', 'pages', 'blog_posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'no_robots')) {
                    $table->dropColumn('no_robots');
                }
            });
        }
    }
};
