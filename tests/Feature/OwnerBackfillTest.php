<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OwnerBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Phase 0 guarded promotion of the two verified owner accounts.
 *
 * Promotes ONLY the exact owners (by ID + email + name), aborts on any
 * identity mismatch or partial presence, never promotes unverified accounts,
 * and never overwrites an already-assigned role.
 */
class OwnerBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function seedMatchedOwners(): void
    {
        foreach ((new OwnerBackfill)->owners() as $owner) {
            User::factory()->noRole()->create([
                'id' => $owner['id'],
                'name' => $owner['name'],
                'email' => $owner['email'],
            ]);
        }
    }

    public function test_fresh_database_is_a_safe_no_op(): void
    {
        $result = (new OwnerBackfill)->run();

        $this->assertSame([], $result['promoted']);
        $this->assertSame(0, User::whereNotNull('role')->count());
    }

    public function test_promotes_only_the_verified_owners(): void
    {
        $this->seedMatchedOwners();

        User::factory()->noRole()->create(['name' => 'Stranger', 'email' => 'stranger@example.com']);

        $result = (new OwnerBackfill)->run();

        $this->assertSame([1, 2], $result['promoted']);

        $this->assertSame(User::ROLE_SUPER_ADMIN, User::find(1)->role);
        $this->assertSame(User::ROLE_SUPER_ADMIN, User::find(2)->role);
        $this->assertNull(User::where('email', 'stranger@example.com')->first()->role);
    }

    public function test_never_overwrites_an_existing_role(): void
    {
        $this->seedMatchedOwners();

        User::where('id', 1)->update(['role' => 'package_editor']);

        (new OwnerBackfill)->run();

        $this->assertSame('package_editor', User::find(1)->role);
        $this->assertSame(User::ROLE_SUPER_ADMIN, User::find(2)->role);
    }

    public function test_aborts_when_identity_does_not_match(): void
    {
        User::factory()->noRole()->create(['id' => 1, 'name' => 'Frank Michael', 'email' => 'pwned@example.com']);
        User::factory()->noRole()->create(['id' => 2, 'name' => 'Admin', 'email' => 'admin@admin.com']);

        $this->expectException(RuntimeException::class);

        (new OwnerBackfill)->run();
    }

    public function test_aborts_when_only_part_of_the_owner_set_is_present(): void
    {
        $owners = (new OwnerBackfill)->owners();
        User::factory()->noRole()->create([
            'id' => $owners[0]['id'],
            'name' => $owners[0]['name'],
            'email' => $owners[0]['email'],
        ]);

        $this->expectException(RuntimeException::class);

        (new OwnerBackfill)->run();
    }
}