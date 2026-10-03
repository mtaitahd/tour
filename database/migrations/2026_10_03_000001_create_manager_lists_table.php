<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('content_type', 16)->index();
            $table->longText('caption')->nullable();
            $table->longText('introduction')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('page_ids')->nullable();
            $table->json('faqs')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->boolean('no_robots')->default(false);
            $table->string('status', 16)->default('draft')->index();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_lists');
    }
};
