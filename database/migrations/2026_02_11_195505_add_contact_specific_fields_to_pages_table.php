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
            // Main contact heading (big title)
            $table->string('contact_heading')->nullable()->after('content');
            
            // Subtitle below heading
            $table->text('contact_subheading')->nullable()->after('contact_heading');
            
            // Google Maps / embed code (iframe)
            $table->text('contact_map_embed')->nullable()->after('contact_subheading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'contact_heading',
                'contact_subheading',
                'contact_map_embed',
            ]);
        });
    }
};