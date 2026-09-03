<?php

use App\Support\OwnerBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Promotes ONLY the two verified owner accounts (by explicit ID + email + name)
 * to super_admin. Aborts on any identity mismatch. Never promotes unverified
 * accounts. Never overwrites an already-assigned role.
 *
 * Rollback restores the original NULL role for these exact accounts only, and
 * only while their email still matches the expected identity.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(OwnerBackfill::class)->run();
    }

    public function down(): void
    {
        foreach ((new OwnerBackfill)->owners() as $owner) {
            DB::table('users')
                ->where('id', $owner['id'])
                ->where('email', $owner['email'])
                ->where('role', 'super_admin')
                ->update(['role' => null]);
        }
    }
};