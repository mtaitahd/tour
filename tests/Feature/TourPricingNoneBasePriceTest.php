<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * "None" is the quote-only pricing source, but it now collects one value:
 * a single total price for the whole tour (the pricing/none partial — one row
 * with base_price + currency). The row lives in its own pane that only shows
 * when the None radio is selected, yet hidden inputs still submit, so the
 * controller must gate on pricing_source rather than on field presence.
 *
 * Blank stays valid: an empty base_price means "Price on Request" on the
 * public site (TourPriceResolver::fromPrice() returns null for it).
 */
class TourPricingNoneBasePriceTest extends TestCase
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
            'title'          => 'Ngorongoro Day Trip',
            'slug'           => 'ngorongoro-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
        ], $overrides);
    }

    /* ── The form UI ──────────────────────────────────────────────────── */

    public function test_create_form_offers_a_single_total_price_row_for_the_none_source(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="base_price"', $html);
        $this->assertStringContainsString('id="pricing-none-pane"', $html);
    }

    /* ── Creating ─────────────────────────────────────────────────────── */

    public function test_store_persists_the_total_price_for_the_none_source(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'base_price' => '450.00',
                'currency'   => 'EUR',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.index'));

        $package = TourPackage::where('title', 'Ngorongoro Day Trip')->sole();

        $this->assertSame(450.0, (float) $package->base_price);
        $this->assertSame('EUR', $package->currency);
    }

    public function test_store_without_a_total_price_keeps_price_on_request(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $package = TourPackage::where('title', 'Ngorongoro Day Trip')->sole();

        $this->assertNull($package->base_price);
    }

    /**
     * The none pane is only hidden (display:none) when another source is
     * picked — its inputs still ride along on every submit. base_price must
     * be ignored unless the source is actually "none", or switching a tour to
     * manual/calculator would silently stamp a stale total onto it.
     */
    public function test_store_ignores_base_price_when_another_source_is_selected(): void
    {
        // Single-day manual grid: one level (STANDARD) x two seasons.
        $rows = [];
        foreach (['HIGH', 'LOW_WET'] as $season) {
            $rows[] = [
                'level_key'   => 'STANDARD',
                'season_code' => $season,
                'price_2p'    => '100',
                'price_4p'    => '90',
                'price_6p'    => '80',
            ];
        }

        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'pricing_source'        => 'manual',
                'package_duration_type' => 'single_day',
                'base_price'            => '999.00', // stray none-pane input
                'manual_currency'       => 'USD',
                'manual_prices'         => $rows,
            ]))
            ->assertSessionHasNoErrors();

        $package = TourPackage::where('title', 'Ngorongoro Day Trip')->sole();

        $this->assertSame('manual', $package->pricing_source);
        $this->assertNull($package->base_price);
    }

    /* ── Editing ──────────────────────────────────────────────────────── */

    public function test_update_saves_and_clears_the_total_price(): void
    {
        $package = TourPackage::create([
            'title'          => 'Tarangire',
            'slug'           => 'tarangire-' . Str::random(5),
            'pricing_source' => 'none',
            'base_price'     => 300,
        ]);

        // Fill it in.
        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.edit', $package))
            ->put(route('admin.tour-packages.update', $package), $this->payload([
                'title'      => 'Tarangire',
                'slug'       => $package->slug,
                'base_price' => '780.50',
            ]))
            ->assertSessionHasNoErrors();

        $package->refresh();
        $this->assertSame(780.5, (float) $package->base_price);

        // Blank it out — back to "Price on Request".
        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.edit', $package))
            ->put(route('admin.tour-packages.update', $package), $this->payload([
                'title'      => 'Tarangire',
                'slug'       => $package->slug,
                'base_price' => '',
            ]))
            ->assertSessionHasNoErrors();

        $package->refresh();
        $this->assertNull($package->base_price);
    }

    public function test_update_drops_base_price_when_switching_away_from_none(): void
    {
        $package = TourPackage::create([
            'title'          => 'Manyara',
            'slug'           => 'manyara-' . Str::random(5),
            'pricing_source' => 'none',
            'base_price'     => 500,
        ]);

        $rows = [];
        foreach (['HIGH', 'LOW_WET'] as $season) {
            $rows[] = [
                'level_key'   => 'STANDARD',
                'season_code' => $season,
                'price_2p'    => '100',
                'price_4p'    => '90',
                'price_6p'    => '80',
            ];
        }

        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.edit', $package))
            ->put(route('admin.tour-packages.update', $package), $this->payload([
                'title'                 => 'Manyara',
                'slug'                  => $package->slug,
                'pricing_source'        => 'manual',
                'package_duration_type' => 'single_day',
                'base_price'            => '500', // hidden none-pane input, submitted anyway
                'manual_currency'       => 'USD',
                'manual_prices'         => $rows,
            ]))
            ->assertSessionHasNoErrors();

        $package->refresh();
        $this->assertSame('manual', $package->pricing_source);
        $this->assertNull($package->base_price);
    }

    /* ── Public display ───────────────────────────────────────────────── */

    public function test_total_price_shows_as_the_from_price_on_the_public_tour_page(): void
    {
        $package = TourPackage::create([
            'title'          => 'Zanzibar Escape',
            'slug'           => 'zanzibar-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
            'base_price'     => 640,
            'currency'       => 'USD',
        ]);

        $html = $this->get(route('tour.show', $package->slug))->assertOk()->getContent();

        $this->assertStringContainsString('640', $html);
        $this->assertStringNotContainsString('Request a Package Price', $html);
    }

    public function test_blank_total_price_still_renders_price_on_request(): void
    {
        $package = TourPackage::create([
            'title'          => 'Quote Only Trek',
            'slug'           => 'quote-only-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
            'base_price'     => null,
        ]);

        $html = $this->get(route('tour.show', $package->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Price on Request', $html);
    }
}
