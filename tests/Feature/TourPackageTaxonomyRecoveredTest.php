<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Destination;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tour package <-> taxonomy wiring.
 *
 * Three related problems this covers:
 *
 *  1. The create form had no Categories or Activities pickers at all, so a new tour
 *     could only be categorised after a second, separate edit.
 *  2. store() never synced activities, so the field could not have worked on create.
 *  3. Both store() and update() synced behind ->has(), which is always false when
 *     the admin unticks every box (an empty multi-select is absent from the POST).
 *     "Clear all" therefore silently kept the old rows.
 *
 * Since the grouped Tour Categories dropdowns landed, the legacy "Listing
 * Categories" checkbox column no longer renders on either form — categorising
 * happens through the Country / Region / Tour Type / Duration selects. The
 * controller still accepts a categories[] payload (older clients, imports,
 * these tests), it is just not offered by the UI anymore.
 */
class TourPackageTaxonomyRecoveredTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'          => 'Migration Safari',
            'slug'           => 'migration-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
        ], $overrides);
    }

    private function category(string $name): TourCategory
    {
        return TourCategory::create(['name' => $name, 'slug' => Str::slug($name) . '-' . Str::random(4)]);
    }

    private function activity(string $name): Activity
    {
        return Activity::create(['name' => $name, 'slug' => Str::slug($name) . '-' . Str::random(4)]);
    }

    /* ── The create form ───────────────────────────────────────────────── */

    public function test_create_form_offers_an_activity_picker_but_no_listing_categories(): void
    {
        $category = $this->category('Tanzania Tours');
        $activity = $this->activity('Big Five');

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="activities[]"', $html);
        $this->assertStringContainsString($activity->name, $html);

        // The Listing Categories column is gone: no checkboxes for it, and
        // legacy category rows are not rendered anywhere on the form.
        $this->assertStringNotContainsString('name="categories[]"', $html);
        $this->assertStringNotContainsString('Listing Categories', $html);
        $this->assertStringNotContainsString($category->name, $html);
    }

    public function test_create_form_explains_when_there_is_nothing_to_pick(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('add some from the admin menu first', $html);
    }

    public function test_the_grouped_column_is_labelled_tour_categories(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>Tour Categories</label>', $html);
        $this->assertStringNotContainsString('>Classification</label>', $html);
    }

    /* ── Both forms offer the same pickers ─────────────────────────────── */

    /**
     * Asserts the remaining taxonomy fields are checkbox groups and that the
     * given ids come back pre-ticked. Returns the parsed document for further
     * checks. The legacy categories[] picker must not render at all anymore.
     */
    private function assertCheckboxPickers(string $html, array $ticked = []): DOMXPath
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);

        $xpath = new DOMXPath($document);

        $this->assertSame(0, $xpath->query("//input[@name='categories[]']")->length, 'Listing Categories checkboxes should be gone');

        foreach (['destinations', 'activities'] as $field) {
            $boxes = $xpath->query("//input[@name='{$field}[]']");

            $this->assertGreaterThan(0, $boxes->length, "no {$field}[] checkboxes were rendered");

            foreach ($boxes as $box) {
                $this->assertSame('checkbox', $box->getAttribute('type'), "{$field}[] is not a checkbox");
            }

            // A <select multiple> is the shape this used to have, and it is the
            // shape that cannot express "nothing ticked".
            $this->assertSame(0, $xpath->query("//select[@name='{$field}[]']")->length);
        }

        foreach ($ticked as [$field, $id]) {
            $box = $xpath->query("//input[@name='{$field}[]' and @value='{$id}']")->item(0);

            $this->assertNotNull($box, "{$field}[] has no option {$id}");
            $this->assertTrue($box->hasAttribute('checked'), "{$field}[] option {$id} is not pre-ticked");
        }

        return $xpath;
    }

    public function test_create_and_edit_forms_offer_the_same_pickers(): void
    {
        $category = $this->category('Tanzania Tours');
        $activity = $this->activity('Big Five');
        $spot     = Destination::create(['name' => 'Ngorongoro', 'slug' => 'ngorongoro-' . Str::random(4), 'country_code' => 'TZ']);

        $createHtml = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertCheckboxPickers($createHtml);

        $tour = TourPackage::create([
            'title'  => 'Ruaha',
            'slug'   => 'ruaha-' . Str::random(5),
            'status' => 'published',
        ]);
        $tour->categories()->attach($category->id);
        $tour->activities()->attach($activity->id);
        $tour->destinations()->attach($spot->id);

        $editHtml = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $tour))
            ->assertOk()
            ->getContent();

        $this->assertCheckboxPickers($editHtml, [
            ['destinations', $spot->id],
            ['activities', $activity->id],
        ]);
    }

    public function test_an_attached_inactive_activity_is_still_offered_on_edit(): void
    {
        $activity = $this->activity('Snorkelling');
        $activity->update(['is_active' => false]);

        $tour = TourPackage::create([
            'title'  => 'Ruaha',
            'slug'   => 'ruaha-' . Str::random(5),
            'status' => 'published',
        ]);
        $tour->categories()->attach($this->category('Tanzania Tours')->id);
        $tour->activities()->attach($activity->id);
        $tour->destinations()->attach(
            Destination::create(['name' => 'Ruaha NP', 'slug' => 'ruaha-np-' . Str::random(4), 'country_code' => 'TZ'])->id
        );

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $tour))
            ->assertOk()
            ->getContent();

        $xpath = $this->assertCheckboxPickers($html);

        $box = $xpath->query("//input[@name='activities[]' and @value='{$activity->id}']")->item(0);

        $this->assertTrue($box->hasAttribute('checked'));
        $this->assertStringContainsString('(inactive)', $html);
    }

    /* ── Storing ───────────────────────────────────────────────────────── */

    public function test_store_persists_categories_and_activities(): void
    {
        $category = $this->category('Tanzania Tours');
        $other    = $this->category('Zanzibar');
        $activity = $this->activity('Big Five');
        $hike     = $this->activity('Hiking');

        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'categories' => [$category->id, $other->id],
                'activities' => [$activity->id, $hike->id],
            ]))
            ->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();

        $this->assertEqualsCanonicalizing(
            [$category->id, $other->id],
            $tour->categories->pluck('id')->all()
        );
        $this->assertEqualsCanonicalizing(
            [$activity->id, $hike->id],
            $tour->activities->pluck('id')->all()
        );
    }

    public function test_store_saves_destinations_supplied_with_categories(): void
    {
        $category = $this->category('Tanzania Tours');
        $spot     = Destination::create(['name' => 'Ngorongoro', 'slug' => 'ngorongoro-' . Str::random(4), 'country_code' => 'TZ']);

        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'destinations' => [$spot->id],
                'categories'   => [$category->id],
            ]))
            ->assertRedirect();

        $this->assertEqualsCanonicalizing([$spot->id], TourPackage::orderByDesc('id')->first()->destinations->pluck('id')->all());
    }

    /* ── The "clear all" bug ───────────────────────────────────────────── */

    public function test_update_clears_categories_when_every_box_is_unticked(): void
    {
        $tour = TourPackage::create([
            'title'  => 'Ruaha',
            'slug'   => 'ruaha-' . Str::random(5),
            'status' => 'published',
        ]);
        $tour->categories()->attach($this->category('Tanzania Tours')->id);

        $this->assertCount(1, $tour->fresh()->categories);

        // No 'categories' key at all — exactly what an unticked multi-select sends.
        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), $this->payload(['slug' => $tour->slug]))
            ->assertRedirect();

        $this->assertCount(0, $tour->fresh()->categories);
    }

    public function test_update_clears_activities_and_destinations_when_unticked(): void
    {
        $tour = TourPackage::create([
            'title'  => 'Ruaha',
            'slug'   => 'ruaha-' . Str::random(5),
            'status' => 'published',
        ]);
        $tour->activities()->attach($this->activity('Big Five')->id);
        $tour->destinations()->attach(
            Destination::create(['name' => 'Ruaha NP', 'slug' => 'ruaha-np-' . Str::random(4), 'country_code' => 'TZ'])->id
        );

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), $this->payload(['slug' => $tour->slug]))
            ->assertRedirect();

        $tour = $tour->fresh();
        $this->assertCount(0, $tour->activities);
        $this->assertCount(0, $tour->destinations);
    }

    /* ── Validation ────────────────────────────────────────────────────── */

    public function test_a_nonexistent_category_id_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.create'))
            ->post(route('admin.tour-packages.store'), $this->payload([
                'categories' => [999999],
            ]))
            ->assertSessionHasErrors('categories.0');

        $this->assertSame(0, TourPackage::count());
    }

    public function test_a_nonexistent_activity_id_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.create'))
            ->post(route('admin.tour-packages.store'), $this->payload([
                'activities' => [999999],
            ]))
            ->assertSessionHasErrors('activities.0');
    }

    /* ── Activities admin screen ───────────────────────────────────────── */

    public function test_the_activities_admin_screen_is_reachable(): void
    {
        $this->activity('Big Five');

        $this->actingAs($this->admin)
            ->get(route('admin.activities.index'))
            ->assertOk();
    }

    public function test_activities_can_be_created_and_deleted_from_the_admin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.activities.store'), [
                'name'        => 'Snorkelling',
                'description' => 'Reef snorkelling trip.',
            ])
            ->assertRedirect();

        $activity = Activity::where('name', 'Snorkelling')->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.activities.destroy', $activity))
            ->assertRedirect();

        $this->assertNull(Activity::find($activity->id));
    }
}
