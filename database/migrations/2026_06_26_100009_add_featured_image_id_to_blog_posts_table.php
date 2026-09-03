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
        Schema::table('blog_posts', function (Blueprint $table) {
            // NOTE: blog_posts already has a legacy 'featured_image' string column from the
            // original schema. It is not actually used by App\Models\BlogPost (the model
            // stores its featured image via the Spatie 'featured_image' media collection
            // instead — confirmed against production data: every BlogPost media row has
            // collection_name = 'featured_image'). That legacy column is left untouched here
            // to avoid any risk to existing data; it's a candidate for removal in a later,
            // separate cleanup migration once confirmed safe.
            $table->unsignedBigInteger('featured_image_id')->nullable();

            $table->foreign('featured_image_id')
                  ->references('id')->on('media')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['featured_image_id']);
            $table->dropColumn('featured_image_id');
        });
    }
};
