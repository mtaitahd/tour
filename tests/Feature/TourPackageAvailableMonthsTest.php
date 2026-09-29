<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cover for the "Available Months" picker on the tour package form.
 *
 * The admin form offers a "Select all months" checkbox plus twelve month
 * checkboxes, and stores the ticked months as a JSON array of month numbers
 * (1-12). Unchecked-everything must persist as null ("runs all year") rather
 * than an empty array, and a bogus month number must be rejected.
 */
class TourPackageAvailableMonthsTest extends TestCase
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
            'title'          => 'Serengeti Migration',
            'slug'           => 'serengeti-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
        ], $overrides);
    }

    /* ── The form UI ──────────────────────────────────────────────────── */

    public function test_create_form_offers_twelve_month_checkboxes_and_a_select_all(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Select all months', $html);

        foreach (TourPackage::MONTHS as $name) {
            $this->assertStringContainsString($name, $html);
        }

        // Twelve month checkboxes in total.
        $this->assertSame(12, substr_count($html, 'name="available_months[]"'));
    }

    public function test_edit_form_pre_ticks_the_existing_months(): void
    {
        $tour = TourPackage::create([
            'title'            => 'Kilimanjaro',
            'slug'             => 'kilimanjaro-' . Str::random(5),
            'status'           => 'published',
            'available_months' => [1, 6, 7],
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $tour))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/name="available_months\[\]"\s+value="6"[^>]*checked/s',
            $html,
            'June (month 6) should be pre-ticked on edit.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/name="available_months\[\]"\s+value="3"[^>]*checked/s',
            $html,
            'March (month 3) was not selected and must not be pre-ticked.'
        );
    }

    /* ── Saving ───────────────────────────────────────────────────────── */

    public function test_store_saves_the_selected_months(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'available_months' => [6, 7, 8],
            ]))
            ->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();

        $this->assertSame([6, 7, 8], $tour->available_months);
        $this->assertSame(['June', 'July', 'August'], $tour->available_month_names);
    }

    public function test_store_sorts_and_deduplicates_submitted_months(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'available_months' => [8, 6, 7, 6],
            ]))
            ->assertRedirect();

        $this->assertSame([6, 7, 8], TourPackage::orderByDesc('id')->first()->available_months);
    }

    public function test_no_months_selected_stores_null_so_the_tour_runs_all_year(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload())
            ->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();

        // An unchecked box is simply absent from the POST — this must not become
        // an empty array, which is a different state.
        $this->assertNull($tour->available_months);
        $this->assertTrue($tour->runsAllYear());
    }

    public function test_update_replaces_the_months(): void
    {
        $tour = TourPackage::create([
            'title'            => 'Ruaha',
            'slug'             => 'ruaha-' . Str::random(5),
            'status'           => 'published',
            'available_months' => [1, 2, 3],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), $this->payload([
                'slug'             => $tour->slug,
                'available_months' => [9, 10],
            ]))
            ->assertRedirect();

        $this->assertSame([9, 10], $tour->fresh()->available_months);
    }

    public function test_update_clears_the_months_when_every_box_is_unticked(): void
    {
        $tour = TourPackage::create([
            'title'            => 'Ruaha',
            'slug'             => 'ruaha-' . Str::random(5),
            'status'           => 'published',
            'available_months' => [1, 2, 3],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), $this->payload([
                'slug' => $tour->slug,
            ]))
            ->assertRedirect();

        $this->assertNull($tour->fresh()->available_months);
    }

    /* ── Validation ───────────────────────────────────────────────────── */

    public function test_a_month_number_outside_one_to_twelve_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.create'))
            ->post(route('admin.tour-packages.store'), $this->payload([
                'available_months' => [13],
            ]))
            ->assertRedirect(route('admin.tour-packages.create'))
            ->assertSessionHasErrors('available_months.0');

        $this->assertSame(0, TourPackage::count());
    }
}
