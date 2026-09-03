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
        Schema::create('media_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();   // self-referencing for nesting, e.g. Destinations > Kilimanjaro
            $table->text('description')->nullable();
            $table->integer('order')->default(999);
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')->on('media_categories')
                  ->onDelete('cascade');                            // deleting a parent removes its children too

            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_categories');
    }
};
