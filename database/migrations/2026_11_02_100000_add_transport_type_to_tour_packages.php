<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', fn (Blueprint $table) => $table->string('transport_type', 40)->nullable());
    }

    public function down(): void
    {
        Schema::table('tour_packages', fn (Blueprint $table) => $table->dropColumn('transport_type'));
    }
};
