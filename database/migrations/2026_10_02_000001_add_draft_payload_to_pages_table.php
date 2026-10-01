<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'draft_payload')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->json('draft_payload')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'draft_payload')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('draft_payload');
            });
        }
    }
};
