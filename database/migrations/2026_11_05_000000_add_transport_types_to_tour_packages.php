<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tour_packages', 'transport_types')) {
            Schema::table('tour_packages', fn (Blueprint $table) => $table->json('transport_types')->nullable());
        }
    }

    public function down(): void
    {
        Schema::table('tour_packages', fn (Blueprint $table) => $table->dropColumn('transport_types'));
    }
};
