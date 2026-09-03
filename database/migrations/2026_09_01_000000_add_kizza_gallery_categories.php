<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seed the kizza-tours style gallery filter categories (Wildlife, Beaches,
 * Mountains, Culture, Lodges) into the Media Library's category table.
 *
 * Idempotent: each category is inserted only if its slug does not already
 * exist, so re-running (e.g. on a fresh DB or an existing one) never
 * duplicates rows. Mirrors kizza-tours' seeded gallery_categories, but reuses
 * the project's existing MediaCategory table instead of creating a new one.
 */
return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'Wildlife',  'order' => 1],
            ['name' => 'Beaches',   'order' => 2],
            ['name' => 'Mountains', 'order' => 3],
            ['name' => 'Culture',   'order' => 4],
            ['name' => 'Lodges',    'order' => 5],
        ];

        foreach ($categories as $cat) {
            if (DB::table('media_categories')->where('slug', Str::slug($cat['name']))->doesntExist()) {
                DB::table('media_categories')->insert([
                    'name'       => $cat['name'],
                    'slug'       => Str::slug($cat['name']),
                    'parent_id'  => null,
                    'description'=> null,
                    'order'      => $cat['order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('media_categories')
            ->whereIn('slug', ['wildlife', 'beaches', 'mountains', 'culture', 'lodges'])
            ->delete();
    }
};
