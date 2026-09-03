<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3: server-generated quote snapshot on every tour inquiry.
 *
 * The store() action resolves the tour's price server-side (date/season, level,
 * exact group size) and freezes the result here in JSON. It is an audit-snap of
 * what the visitor was quoted at submission time — never recalculated from
 * browser-supplied numbers, and never trusted for payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->json('quote_snapshot')
                ->nullable()
                ->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('quote_snapshot');
        });
    }
};