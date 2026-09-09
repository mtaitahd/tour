<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mega menu items are no longer created from the tour/page forms. They are managed
     * standalone in the dedicated "Mega Nav" admin module, so:
     *  - source_type/source_id become nullable (a standalone "custom" item has no
     *    TourPackage/Page source — it carries its own title/heading/description/image
     *    and an explicit link URL);
     *  - a new `heading` column stores the middle-column heading separately from the
     *    left-column title (previously both rendered from menu_label).
     */
    public function up(): void
    {
        Schema::table('navigation_mega_items', function (Blueprint $table) {
            $table->string('source_type', 20)->nullable()->change();
            $table->unsignedBigInteger('source_id')->nullable()->change();
            $table->string('heading', 255)->nullable()->after('menu_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigation_mega_items', function (Blueprint $table) {
            $table->string('source_type', 20)->nullable(false)->change();
            $table->unsignedBigInteger('source_id')->nullable(false)->change();
            $table->dropColumn('heading');
        });
    }
};