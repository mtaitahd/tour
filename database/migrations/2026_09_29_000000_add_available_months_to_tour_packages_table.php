<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store which months of the year a tour package runs in, as a JSON array of
     * month numbers (1-12). Null means "no restriction / not specified".
     */
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->json('available_months')->nullable()->after('departure_dates');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn('available_months');
        });
    }
};
