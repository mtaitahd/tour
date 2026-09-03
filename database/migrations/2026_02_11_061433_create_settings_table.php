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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // e.g. site_name, logo_path, whatsapp_number
            $table->text('value')->nullable();      // string, JSON, or text
            $table->string('type')->default('text'); // text, textarea, image, checkbox, number, email, url, json
            $table->string('group')->nullable();    // e.g. general, seo, contact
            $table->string('label')->nullable();    // human-readable name
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
