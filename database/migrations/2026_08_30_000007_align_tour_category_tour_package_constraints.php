<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 0.5 pivot-table alignment. On the live database the pivot was
     * created out-of-band with only the tour_category_id FK (0 orphans, 0
     * duplicates, 3 rows — verified before running), so this adds the missing
     * tour_package_id FK and the composite uniqueness guarantee. On a fresh
     * database the original pivot migration already carries both, so every
     * step is guarded and this is a safe no-op there. No pivot records are
     * deleted or rewritten. Depends on tour_packages having its primary key
     * (added by 2026_08_30_000006_tour_packages_primary_key).
     */
    public function up(): void
    {
        Schema::table('tour_category_tour_package', function (Blueprint $table) {
            if (! $this->hasForeign('tour_category_tour_package_tour_package_id_foreign')) {
                $table->foreign('tour_package_id')
                    ->references('id')
                    ->on('tour_packages')
                    ->onDelete('cascade');
            }

            if (! $this->hasIndex('tour_cat_pkg_unique')) {
                $table->unique(['tour_category_id', 'tour_package_id'], 'tour_cat_pkg_unique');
            }
        });
    }

    /**
     * Reverse only what this migration (and not the original pivot migration)
     * added, so a fresh database rollback stays consistent.
     */
    public function down(): void
    {
        Schema::table('tour_category_tour_package', function (Blueprint $table) {
            if ($this->hasIndex('tour_cat_pkg_unique')) {
                $table->dropUnique('tour_cat_pkg_unique');
            }

            if ($this->hasForeign('tour_category_tour_package_tour_package_id_foreign')) {
                $table->dropForeign(['tour_package_id']);
            }
        });
    }

    private function hasIndex(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', 'tour_category_tour_package')
            ->where('index_name', $indexName)
            ->exists();
    }

    private function hasForeign(string $constraintName): bool
    {
        return DB::table('information_schema.key_column_usage')
            ->where('constraint_schema', DB::connection()->getDatabaseName())
            ->where('table_name', 'tour_category_tour_package')
            ->where('constraint_name', $constraintName)
            ->exists();
    }
};