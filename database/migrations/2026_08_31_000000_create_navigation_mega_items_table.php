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
        Schema::create('navigation_mega_items', function (Blueprint $table) {
            $table->id();

            // Top-level menu the item appears under. Values come from
            // config/navigation.php (parent_menus). The public mega menu only renders
            // parents that exist here, so a stale key never renders anything.
            $table->string('parent_menu_key', 60);

            // Polymorphic source: 'tour' (TourPackage) or 'page' (Page). Kept as an
            // explicit short string (not the full class name) so the service can
            // resolve the source model without a morph-map lookup and the admin
            // form can populate its select without guessing class names.
            $table->string('source_type', 20);
            $table->unsignedBigInteger('source_id');

            // Presentation overrides. short_description defaults to a stripped,
            // truncated excerpt of the source content when left empty (public side
            // only — the stored value stays null).
            $table->string('menu_label', 120);
            $table->text('short_description')->nullable();
            $table->string('button_label', 120)->nullable();
            $table->string('button_url_override', 2048)->nullable();

            // Contextual image chosen from the Media Library. Falls back to the
            // source's hero image and then a site-wide fallback on the public side.
            $table->unsignedBigInteger('image_id')->nullable()->index();

            // Optional short pill shown beside the left-column label.
            $table->string('badge_text', 40)->nullable();

            // Manual ordering within the parent. Public rendering sorts by
            // display_order first, then menu_label as a stable tiebreaker.
            $table->unsignedInteger('display_order')->default(0);

            // Show in Mega Menu toggle — default is OFF. Only active items whose
            // source is also published/draft-excluded will ever render publicly.
            $table->boolean('is_active')->default(false);

            // Who configured this item (audit trail; nullable for seeded data).
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // A source may only appear once per parent menu. The admin form edits a
            // single parent per source, and persists by deleting any other rows for
            // the same source first, so this also guards against direct inserts.
            $table->unique(['parent_menu_key', 'source_type', 'source_id'], 'nav_mega_items_parent_source_unique');

            // Fast eager filtering per parent for the public header.
            $table->index(['parent_menu_key', 'is_active', 'display_order'], 'nav_mega_items_parent_active_order_idx');

            $table->foreign('image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');

            $table->foreign('updated_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_mega_items');
    }
};