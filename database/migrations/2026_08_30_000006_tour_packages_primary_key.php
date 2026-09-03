<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Phase 0.5/Phase 1 precondition: the live tour_packages table was created
     * out-of-band and is missing its Laravel $table->id() primary key (id is a
     * plain unsigned bigint, no index), which blocks foreign keys referencing
     * tour_packages.id. Verified before running: 4 rows, 0 NULL ids, 0
     * duplicates, no existing PRIMARY KEY, no dependent FKs. Restores the
     * primary key and AUTO_INCREMENT to match the original schema
     * (2026_02_04_091322_create_tour_packages_table) without touching rows.
     */
    public function up(): void
    {
        if (! $this->primaryKeyExists()) {
            DB::statement('ALTER TABLE `tour_packages` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)');
        }
    }

    /**
     * Reverse only if it is safe: newer foreign keys may depend on the
     * primary key, so rollback aborts rather than leaving the database broken.
     */
    public function down(): void
    {
        $dependents = DB::table('information_schema.key_column_usage')
            ->where('referenced_table_schema', DB::connection()->getDatabaseName())
            ->where('referenced_table_name', 'tour_packages')
            ->where('referenced_column_name', 'id')
            ->select('table_name', 'constraint_name')
            ->get();

        if ($dependents->isNotEmpty()) {
            $list = $dependents
                ->map(fn ($d) => $d->table_name.'.'.$d->constraint_name)
                ->implode(', ');

            throw new RuntimeException(
                'Cannot drop tour_packages primary key: dependent foreign keys exist ('.$list.').'
            );
        }

        if ($this->primaryKeyExists()) {
            DB::statement('ALTER TABLE `tour_packages` DROP PRIMARY KEY, MODIFY `id` BIGINT UNSIGNED NOT NULL');
        }
    }

    private function primaryKeyExists(): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', 'tour_packages')
            ->where('index_name', 'PRIMARY')
            ->exists();
    }
};