<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Promotes ONLY the two verified owner accounts to super_admin.
 *
 * Identities are resolved by explicit ID + email + name. The operation:
 *  - reports the accounts before changing anything,
 *  - aborts if a matching account is missing or any identity differs,
 *  - never promotes every existing account and never guesses owners
 *    from lowest ID, creation order or registration date,
 *  - never overwrites an already-assigned role.
 *
 * Original role values are preserved for rollback documentation: both owner
 * accounts currently have a NULL role, so down() restores NULL.
 */
class OwnerBackfill
{
    /**
     * The confirmed owner accounts (live production DB, verified pre-flight).
     *
     * @var array<int, array{id:int, name:string, email:string}>
     */
    private const OWNERS = [
        ['id' => 1, 'name' => 'Frank Michael', 'email' => 'frankmicky@gmail.com'],
        ['id' => 2, 'name' => 'Admin', 'email' => 'admin@admin.com'],
    ];

    public function owners(): array
    {
        return self::OWNERS;
    }

    /**
     * Verify that the exact owner identities exist. Throws if any matched
     * account differs or if only part of the set is present.
     *
     * @return array<int, array{id:int,name:string,email:string,current_role:?string,present:bool}>
     */
    public function verify(): array
    {
        $rows = DB::table('users')->whereIn('id', array_column(self::OWNERS, 'id'))->get()->keyBy('id');

        $report = [];
        $present = 0;
        foreach (self::OWNERS as $owner) {
            $row = $rows->get($owner['id']);
            $matched = $row !== null
                && strtolower((string) $row->email) === strtolower($owner['email'])
                && $row->name === $owner['name'];

            if ($row !== null) {
                $present++;
            }
            if ($row !== null && ! $matched) {
                throw new RuntimeException(
                    'Owner role backfill aborted: user #'.$owner['id'].' ('.$owner['email'].') does not match the expected identity.'
                );
            }

            $report[] = [
                'id'           => $owner['id'],
                'name'         => $owner['name'],
                'email'        => $owner['email'],
                'current_role' => $row?->role,
                'present'      => $row !== null,
            ];
        }

        if ($present > 0 && $present !== count(self::OWNERS)) {
            throw new RuntimeException(
                'Owner role backfill aborted: only '.$present.' of '.count(self::OWNERS).' owner accounts are present.'
            );
        }

        return $report;
    }

    /**
     * Promote the verified owner accounts. Safe no-op on fresh databases.
     *
     * @return array{promoted: list<int>, report: array}
     */
    public function run(): array
    {
        $report = $this->verify();

        $promoted = [];
        foreach (self::OWNERS as $owner) {
            $affected = DB::table('users')
                ->where('id', $owner['id'])
                ->whereNull('role')
                ->update(['role' => 'super_admin']);

            if ($affected > 0) {
                $promoted[] = $owner['id'];
            }
        }

        return ['promoted' => $promoted, 'report' => $report];
    }
}