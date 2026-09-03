<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Add new columns if they don't exist yet
            if (!Schema::hasColumn('pages', 'status')) {
                $table->string('status')->default('draft')->after('content');
            }

            if (!Schema::hasColumn('pages', 'order')) {
                $table->integer('order')->default(999)->after('status');
            }

            if (!Schema::hasColumn('pages', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('order');
            }

            if (!Schema::hasColumn('pages', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('hero_image');
            }

            if (!Schema::hasColumn('pages', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }

            // Optional: If you had 'is_published' boolean from earlier and want to drop it
            // if (Schema::hasColumn('pages', 'is_published')) {
            //     $table->dropColumn('is_published');
            // }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Reverse: drop the columns we added (safe rollback)
            $table->dropColumn([
                'status',
                'order',
                'hero_image',
                'meta_title',
                'meta_keywords',
                // 'is_published' if you dropped it
            ]);
        });
    }
};