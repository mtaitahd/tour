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
        Schema::create('tour_category_tour_package', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_category_id');
            $table->unsignedBigInteger('tour_package_id');
            $table->timestamps();

            $table->foreign('tour_category_id')
                  ->references('id')->on('tour_categories')
                  ->onDelete('cascade');

            $table->foreign('tour_package_id')
                  ->references('id')->on('tour_packages')
                  ->onDelete('cascade');

            // A tour can belong to several categories at once (e.g. a Kilimanjaro climb
            // that's also tagged as a Tanzania Safari) — this just prevents the same
            // pairing being recorded twice.
            $table->unique(['tour_category_id', 'tour_package_id'], 'tour_cat_pkg_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_category_tour_package');
    }
};
