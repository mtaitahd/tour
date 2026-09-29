<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

    /**
     * Regression: a rejected create used to be completely invisible. The create form
     * had no error block (the edit form did), so the browser simply re-rendered a
     * blank form and the click looked like it did nothing.
     */
    public function test_failed_create_shows_errors_instead_of_failing_silently(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
             ->from(route('admin.destinations.create'))
             ->post(route('admin.destinations.store'), [
                 'name'         => '',              // required
                 'country_code' => 'XX',            // not in:TZ,KE,UG,RW
             ])
             ->assertRedirect(route('admin.destinations.create'))
             ->assertSessionHasErrors(['name', 'country_code']);

        $this->assertSame(0, Destination::count());

        // Following the redirect must actually render the messages.
        $html = $this->actingAs($admin)->get(route('admin.destinations.create'))->assertOk()->getContent();

        $this->assertStringContainsString('Destination could not be saved', $html);
        $this->assertStringContainsString('alert-danger', $html);
    }

    /**
     * Regression: submitting inside the modal iframe redirects back to the admin
     * index, so the browser shows nothing while the POST is in flight. The form
     * must therefore mark the button as saving.
     */
    public function test_create_form_has_a_saving_state(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)->get(route('admin.destinations.create'))->assertOk()->getContent();

        $this->assertStringContainsString('destination-create-form', $html);
        $this->assertStringContainsString('destination-create-submit', $html);
        $this->assertStringContainsString('Saving', $html);
    }

    /**
     * Regression: when the live database predates the faqs/reviews_embed migration,
     * the insert threw a QueryException that Laravel rendered as a bare 500 inside
     * the modal iframe — the spinner stopped and the admin was told nothing. It must
     * now redirect back with an actionable message.
     */
    public function test_store_reports_missing_database_columns_instead_of_failing_silently(): void
    {
        $admin = $this->admin();

        // Pretend the destinations table is stale: none of the newer columns exist.
        Schema::shouldReceive('hasColumn')->andReturn(false);

        $this->actingAs($admin)
             ->from(route('admin.destinations.create'))
             ->post(route('admin.destinations.store'), [
                 'name'         => 'Ngorongoro',
                 'country_code' => 'TZ',
             ])
             ->assertRedirect(route('admin.destinations.create'))
             ->assertSessionHas('error');

        $this->assertStringContainsString('migrate', session('error'));
        $this->assertSame(0, Destination::count());
    }

    public function test_create_form_renders_a_flash_error(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
             ->withSession(['error' => 'Destination could not be saved: database out of date'])
             ->get(route('admin.destinations.create'))
             ->assertOk()
             ->assertSee('database out of date');
    }
}
