<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression cover for the two 500s reported from the admin Destinations screen:
 *
 *  1. "View current destination" (the eye button -> the public destination page)
 *     blew up with "Attempt to read property \"id\" on int".
 *  2. "Add destination" (the modal iframe -> admin/destinations/create) blew up.
 */
class DestinationPageRenderingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_public_destination_page_renders_with_no_related_tours(): void
    {
        $destination = Destination::create([
            'name'         => 'Serengeti',
            'slug'         => 'serengeti',
            'country_code' => 'TZ',
            'type'         => 'national_park',
        ]);

        // The destination has no tours at all, so the related-tour paginator is
        // empty but still carries integer meta (total => 0, per_page => 6).
        $this->get(route('destination.show', $destination->slug))->assertOk();
    }

    public function test_public_destination_page_renders_with_related_tours(): void
    {
        $destination = Destination::create([
            'name'         => 'Ngorongoro',
            'slug'         => 'ngorongoro',
            'country_code' => 'TZ',
            'type'         => 'national_park',
        ]);

        $this->get(route('destination.show', $destination->slug))->assertOk();
    }

    public function test_admin_destination_index_renders(): void
    {
        $admin = $this->admin();

        Destination::create([
            'name'         => 'Zanzibar',
            'slug'         => 'zanzibar',
            'country_code' => 'TZ',
        ]);

        $this->actingAs($admin)
             ->get(route('admin.destinations.index'))
             ->assertOk();
    }

    public function test_admin_destination_create_form_renders_in_modal(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
             ->get(route('admin.destinations.create') . '?modal=1')
             ->assertOk();
    }

    public function test_admin_destination_edit_form_renders_in_modal(): void
    {
        $admin = $this->admin();

        $destination = Destination::create([
            'name'         => 'Kilimanjaro',
            'slug'         => 'kilimanjaro',
            'country_code' => 'TZ',
        ]);

        $this->actingAs($admin)
             ->get(route('admin.destinations.edit', $destination) . '?modal=1')
             ->assertOk();
    }

    public function test_admin_can_store_a_destination(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
             ->post(route('admin.destinations.store'), [
                 'name'         => 'Tarangire',
                 'slug'         => '',
                 'country_code' => 'TZ',
                 'type'         => 'national_park',
                 'faqs'         => [
                     ['question' => 'When is the best time?', 'answer' => 'June to October.'],
                     ['question' => '', 'answer' => ''],
                 ],
             ])
             ->assertRedirect(route('admin.destinations.index'));

        $this->assertDatabaseHas('destinations', [
            'slug'         => 'tarangire',
            'country_code' => 'TZ',
        ]);

        // The blank repeater row must be dropped, not stored.
        $this->assertCount(1, Destination::first()->faqs);
    }
}
