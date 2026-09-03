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
            // General / reusable extra fields
            if (!Schema::hasColumn('pages', 'extra_heading')) {
                $table->string('extra_heading')->nullable()->after('content');
            }

            if (!Schema::hasColumn('pages', 'extra_subheading')) {
                $table->text('extra_subheading')->nullable()->after('extra_heading');
            }

            if (!Schema::hasColumn('pages', 'extra_hero_image')) {
                $table->string('extra_hero_image')->nullable()->after('extra_subheading');
            }

            if (!Schema::hasColumn('pages', 'cta_text')) {
                $table->string('cta_text')->nullable()->after('extra_hero_image');
            }

            if (!Schema::hasColumn('pages', 'cta_link')) {
                $table->string('cta_link')->nullable()->after('cta_text');
            }

            // Contact page specific
            if (!Schema::hasColumn('pages', 'contact_heading')) {
                $table->string('contact_heading')->nullable()->after('cta_link');
            }

            if (!Schema::hasColumn('pages', 'contact_subheading')) {
                $table->text('contact_subheading')->nullable()->after('contact_heading');
            }

            if (!Schema::hasColumn('pages', 'contact_map_embed')) {
                $table->text('contact_map_embed')->nullable()->after('contact_subheading');
            }

            // About / Story page specific
            if (!Schema::hasColumn('pages', 'story_title')) {
                $table->string('story_title')->nullable()->after('contact_map_embed');
            }

            if (!Schema::hasColumn('pages', 'story_image')) {
                $table->string('story_image')->nullable()->after('story_title');
            }

            if (!Schema::hasColumn('pages', 'why_choose_subtitle')) {
                $table->string('why_choose_subtitle')->nullable()->after('story_image');
            }

            // Stats & custom
            if (!Schema::hasColumn('pages', 'stats_counters')) {
                $table->json('stats_counters')->nullable()->after('why_choose_subtitle');
            }

            if (!Schema::hasColumn('pages', 'custom_data')) {
                $table->json('custom_data')->nullable()->after('stats_counters');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'extra_heading',
                'extra_subheading',
                'extra_hero_image',
                'cta_text',
                'cta_link',
                'contact_heading',
                'contact_subheading',
                'contact_map_embed',
                'story_title',
                'story_image',
                'why_choose_subtitle',
                'stats_counters',
                'custom_data',
            ]);
        });
    }
};