<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\PackagePrice;
use App\Models\TourPackage;
use App\Services\TourPriceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Phase 3: customer-facing price selection + custom-price inquiry flow.
 *
 * Every price the public site shows comes from the server (TourPriceResolver):
 *   - automatic quote only for group sizes 2/4/6 at a configured level + season
 *   - custom-price request for any other group size or missing combination
 *   - package-price request for tours without any configured price
 * The inquiry flow freezes the same lookup server-side in quote_snapshot —
 * browser-supplied amounts are never trusted.
 */
class PublicPricingTest extends TestCase
{
    use RefreshDatabase;

    private const SEASONS = ['HIGH', 'LOW_WET'];

    protected function makeTour(array $overrides = []): TourPackage
    {
        return TourPackage::create(array_merge([
            'slug'                  => 'public-tour-' . Str::random(5),
            'title'                 => 'Public Pricing Tour',
            'status'                => 'published',
            'currency'              => 'USD',
            'pricing_source'        => 'manual',
            'package_duration_type' => 'multi_day',
            'package_category'      => 'LUXURY',
        ], $overrides));
    }

    protected function addPrices(TourPackage $tour, array $rows = []): void
    {
        foreach ($rows ?: $this->standardRows() as $row) {
            PackagePrice::create(array_merge([
                'tour_package_id'   => $tour->id,
                'package_category'  => 'LUXURY',
                'currency'          => 'USD',
            ], $row));
        }
    }

    /** High-Season prices differ from Low Wet Season so season resolution is provable. */
    protected function standardRows(): array
    {
        $delta = ['HIGH' => ['125.50', '122.25', '120.00', '251.00', '489.00', '720.00'],
                  'LOW_WET' => ['110.00', '107.00', '105.00', '220.00', '428.00', '630.00']];

        $rows = [];
        foreach (self::SEASONS as $season) {
            $rows[] = [
                'level_key'         => 'LUXURY',
                'level_name'        => 'Luxury',
                'season_code'       => $season,
                'price_2p'          => $delta[$season][0],
                'price_4p'          => $delta[$season][1],
                'price_6p'          => $delta[$season][2],
                'group_total_2p'    => $delta[$season][3],
                'group_total_4p'    => $delta[$season][4],
                'group_total_6p'    => $delta[$season][5],
            ];
        }

        return $rows;
    }

    protected function inquiryPayload(array $merge = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'jane@example.com',
            'country'    => 'Tanzania',
            'phone'      => '255700000000',
            'g-recaptcha-response' => 'fake-token',
        ], $merge);
    }

    /* ── Resolver: "From" price (centralised across cards) ─────────────── */

    public function test_from_price_prefers_new_system_then_legacy_then_base(): void
    {
        // New-system rows win over legacy + base.
        $tour = $this->makeTour(['base_price' => 999, 'season_pricing' => [['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00']]]);
        $this->addPrices($tour);
        $from = TourPriceResolver::fromPrice($tour);
        $this->assertSame('new', $from['source']);
        $this->assertSame(110.0, $from['amount']); // lowest price_2p across both seasons

        // Legacy-only tour.
        $legacy = $this->makeTour(['pricing_source' => 'legacy', 'base_price' => 750,
            'season_pricing' => [['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00']]]);
        $from = TourPriceResolver::fromPrice($legacy);
        $this->assertSame('legacy', $from['source']);
        $this->assertSame(800.0, $from['amount']);

        // base_price-only tour.
        $base = $this->makeTour(['pricing_source' => 'none', 'base_price' => 500]);
        $from = TourPriceResolver::fromPrice($base);
        $this->assertSame('base', $from['source']);
        $this->assertSame(500.0, $from['amount']);

        // No price anywhere.
        $this->assertNull(TourPriceResolver::fromPrice($this->makeTour(['pricing_source' => 'none', 'base_price' => 0])));
    }

    public function test_from_price_map_resolves_with_single_aggregate_query(): void
    {
        $withPrices = $this->makeTour();
        $this->addPrices($withPrices);
        $none = $this->makeTour(['pricing_source' => 'none']);

        $map = TourPriceResolver::fromPriceMap([$withPrices, $none]);

        $this->assertSame(110.0, $map[$withPrices->id]['amount']);
        $this->assertSame('new', $map[$withPrices->id]['source']);
        $this->assertNull($map[$none->id]);
    }

    /* ── Resolver: lookup outcomes ─────────────────────────────────────── */

    public function test_lookup_automatic_for_group_two_in_high_season(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $result = app(TourPriceResolver::class)->lookup($tour, [
            'date'       => '2026-08-15',
            'group_size' => 2,
            'level_key'  => 'LUXURY',
        ]);

        $this->assertSame('automatic', $result['request_type']);
        $this->assertSame('HIGH', $result['season']);
        $this->assertSame('125.50', $result['price_pp']);
        $this->assertSame('251.00', $result['group_total']);
        $this->assertSame('LUXURY', $result['level_key']);
        $this->assertSame('USD', $result['currency']);
    }

    public function test_lookup_resolves_low_wet_season_from_april_date(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $result = app(TourPriceResolver::class)->lookup($tour, [
            'date'       => '2026-04-10',
            'group_size' => 2,
            'level_key'  => 'LUXURY',
        ]);

        $this->assertSame('automatic', $result['request_type']);
        $this->assertSame('LOW_WET', $result['season']);
        $this->assertSame('Low Wet Season', $result['season_label']);
        $this->assertSame('110.00', $result['price_pp']);
        $this->assertSame('220.00', $result['group_total']);
    }

    public function test_lookup_defaults_to_high_season_when_no_date_given(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $result = app(TourPriceResolver::class)->lookup($tour, ['group_size' => 2]);

        $this->assertSame('HIGH', $result['season']);
        $this->assertSame('125.50', $result['price_pp']);
    }

    public function test_lookup_auto_selects_cheapest_level_when_none_requested(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour, array_merge($this->standardRows(), [
            ['level_key' => 'EXCLUSIVE', 'level_name' => 'Exclusive', 'season_code' => 'HIGH',
             'price_2p' => '90.00', 'price_4p' => '88.00', 'price_6p' => '86.00',
             'group_total_2p' => '180.00', 'group_total_4p' => '352.00', 'group_total_6p' => '516.00'],
        ]));

        $result = app(TourPriceResolver::class)->lookup($tour, ['group_size' => 2, 'level_key' => null]);
        $this->assertSame('automatic', $result['request_type']);
        $this->assertSame('EXCLUSIVE', $result['level_key']);
        $this->assertSame('90.00', $result['price_pp']);
    }

    public function test_lookup_returns_custom_for_unsupported_group_size(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $result = app(TourPriceResolver::class)->lookup($tour, ['date' => '2026-08-15', 'group_size' => 3]);

        $this->assertSame('custom', $result['request_type']);
        $this->assertSame(3, $result['group_size']);
        $this->assertNull($result['price_pp']);
        $this->assertStringContainsString('groups of 2, 4 or 6', $result['note']);
    }

    public function test_lookup_returns_custom_for_missing_level_combination(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $result = app(TourPriceResolver::class)->lookup($tour, [
            'date' => '2026-08-15', 'group_size' => 2, 'level_key' => 'EXCLUSIVE',
        ]);

        $this->assertSame('custom', $result['request_type']);
        $this->assertStringContainsString('Exclusive', $result['note']);
    }

    public function test_lookup_returns_package_request_when_tour_has_no_price(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0]);

        $result = app(TourPriceResolver::class)->lookup($tour, ['group_size' => 2]);

        $this->assertSame('package_request', $result['request_type']);
        $this->assertNull($result['price_pp']);
        $this->assertNull($result['group_total']);
    }

    public function test_lookup_never_returns_automatic_for_legacy_tiers(): void
    {
        // Real legacy structure: SILVER/GOLD/PLATINUM product tiers, no seasons.
        $tour = $this->makeTour(['pricing_source' => 'legacy',
            'season_pricing' => [
                ['season' => 'SILVER', 'price_2p' => '1000', 'price_4p' => '2000', 'price_6p' => '3000'],
                ['season' => 'GOLD', 'price_2p' => '2000', 'price_4p' => '4000', 'price_6p' => '6000'],
                ['season' => 'PLATINUM', 'price_2p' => '3000', 'price_4p' => '6000', 'price_6p' => '9000'],
            ]]);

        foreach (self::SEASONS as $season) {
            $date = $season === 'HIGH' ? '2026-08-15' : '2026-04-10';
            foreach ([2, 4, 6, 3, 5] as $size) {
                $result = app(TourPriceResolver::class)->lookup($tour, [
                    'date' => $date, 'group_size' => $size, 'level_key' => null,
                ]);

                $this->assertNotSame('automatic', $result['request_type'], "legacy auto on {$season}/{$size}");
                $this->assertSame('legacy', $result['source']);
                $this->assertNull($result['price_pp']);
                $this->assertNull($result['group_total']);
                $this->assertNotNull($result['note']);
            }
        }
    }

    public function test_lookup_never_automatic_even_when_legacy_tier_bears_a_season_name(): void
    {
        // A legacy tier labelled "High"/"Low Wet" is still a product tier — it is
        // not a date-specific season rate, so it must never produce automatic.
        $tour = $this->makeTour(['pricing_source' => 'legacy',
            'season_pricing' => [
                ['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00'],
                ['season' => 'Low Wet', 'price_2p' => '700.00', 'price_4p' => '680.00', 'price_6p' => '660.00'],
            ]]);

        foreach (['2026-08-15', '2026-04-10'] as $date) {
            $result = app(TourPriceResolver::class)->lookup($tour, ['date' => $date, 'group_size' => 2]);
            $this->assertSame('custom', $result['request_type'], "false auto for date {$date}");
            $this->assertNull($result['price_pp']);
            $this->assertNull($result['group_total']);
        }
    }

    public function test_lookup_legacy_always_custom_with_date_confirmation_note(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'legacy',
            'season_pricing' => [['season' => 'SILVER', 'price_2p' => '1000', 'price_4p' => '2000', 'price_6p' => '3000']]]);

        $result = app(TourPriceResolver::class)->lookup($tour, ['date' => '2026-04-10', 'group_size' => 2]);

        $this->assertSame('custom', $result['request_type']);
        $this->assertSame('LOW_WET', $result['season']);
        $this->assertNull($result['price_pp']);
        $this->assertStringContainsString('confirm the exact price', $result['note']);
    }

    public function test_from_price_still_uses_min_positive_legacy_tier_for_display(): void
    {
        // Display-only "From": the minimum positive legacy tier price, never a
        // seasonal claim, never tagged automatic.
        $tour = $this->makeTour(['pricing_source' => 'legacy', 'base_price' => 900,
            'season_pricing' => [
                ['season' => 'SILVER', 'price_2p' => '1000', 'price_4p' => '2000', 'price_6p' => '3000'],
                ['season' => 'PLATINUM', 'price_2p' => '1400', 'price_4p' => '2800', 'price_6p' => '4200'],
            ]]);

        $from = TourPriceResolver::fromPrice($tour);
        $this->assertSame('legacy', $from['source']);
        $this->assertSame(1000.0, $from['amount']);
    }

    /* ── Public lookup endpoint ────────────────────────────────────────── */

    public function test_price_lookup_endpoint_returns_automatic_quote(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->get(route('tours.priceLookup', $tour) . '?date=2026-08-15&group_size=2&level_key=LUXURY')
            ->assertOk()
            ->assertJsonPath('request_type', 'automatic')
            ->assertJsonPath('season', 'HIGH')
            ->assertJsonPath('price_pp', '125.50')
            ->assertJsonPath('group_total', '251.00')
            ->assertJsonPath('level_key', 'LUXURY');
    }

    public function test_price_lookup_endpoint_returns_custom_for_unsupported_group(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->get(route('tours.priceLookup', $tour) . '?date=2026-08-15&group_size=3&level_key=LUXURY')
            ->assertOk()
            ->assertJsonPath('request_type', 'custom')
            ->assertJsonPath('group_size', 3);
    }

    public function test_price_lookup_endpoint_returns_package_request_for_priceless_tour(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0]);

        $this->get(route('tours.priceLookup', $tour) . '?group_size=2')
            ->assertOk()
            ->assertJsonPath('request_type', 'package_request');
    }

    public function test_price_lookup_endpoint_never_automatic_for_legacy_tour(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'legacy',
            'season_pricing' => [
                ['season' => 'SILVER', 'price_2p' => '1000', 'price_4p' => '2000', 'price_6p' => '3000'],
                ['season' => 'GOLD', 'price_2p' => '2000', 'price_4p' => '4000', 'price_6p' => '6000'],
                ['season' => 'PLATINUM', 'price_2p' => '3000', 'price_4p' => '6000', 'price_6p' => '9000'],
            ]]);

        $this->get(route('tours.priceLookup', $tour) . '?date=2026-08-15&group_size=2')
            ->assertOk()
            ->assertJsonPath('request_type', 'custom')
            ->assertJsonPath('price_pp', null)
            ->assertJsonPath('group_total', null);

        $this->get(route('tours.priceLookup', $tour) . '?date=2026-04-10&group_size=2')
            ->assertOk()
            ->assertJsonPath('request_type', 'custom');
    }

    public function test_price_lookup_endpoint_hides_unpublished_tours(): void
    {
        $tour = $this->makeTour(['status' => 'draft']);
        $this->addPrices($tour);

        $this->get(route('tours.priceLookup', $tour) . '?group_size=2')->assertNotFound();
    }

    public function test_price_lookup_endpoint_validates_group_size(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->get(route('tours.priceLookup', $tour))->assertSessionHasErrors('group_size');
        $this->get(route('tours.priceLookup', $tour) . '?group_size=0')->assertSessionHasErrors('group_size');
    }

    /* ── Inquiry quote snapshot ────────────────────────────────────────── */

    public function test_tour_inquiry_stores_server_generated_quote_snapshot(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->from(route('tour.show', $tour->slug))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'tour_package_id' => $tour->id,
                'package_level'   => 'LUXURY',
                'travel_date'     => '2026-08-15',
                'adults'          => 2,
                'children'        => 0,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect()
            ->assertSessionHas('success');

        $inquiry = Inquiry::firstOrFail();
        $this->assertSame('tour_booking', $inquiry->type);
        $this->assertArrayHasKey('quote_snapshot', $inquiry->getAttributes());

        // total_amount is copied from the server result — never a browser number.
        $this->assertSame('251.00', $inquiry->total_amount);

        $snapshot = $inquiry->quote_snapshot;
        $this->assertSame('automatic', $snapshot['request_type']);
        $this->assertSame('HIGH', $snapshot['season']);
        $this->assertSame('125.50', $snapshot['price_pp']);
        $this->assertSame('251.00', $snapshot['group_total']);
        $this->assertSame(2, $snapshot['group_size']);
        $this->assertSame('LUXURY', $snapshot['level_key']);

        // Required snapshot structure.
        $packageRow = $tour->packagePrices()->where('season_code', 'HIGH')->where('level_key', 'LUXURY')->firstOrFail();
        $this->assertSame($packageRow->id, $snapshot['package_price_id']);
        $this->assertSame('USD', $snapshot['currency']);
        $this->assertSame('2026-08-15', $snapshot['date']);
        $this->assertSame('High Season', $snapshot['season_label']);
        $this->assertSame('LUXURY', $snapshot['package_category']);
        $this->assertSame('Luxury', $snapshot['level_name']);
        $this->assertSame(2, $snapshot['adults']);
        $this->assertSame(0, $snapshot['children']);
        $this->assertSame(2, $snapshot['total_travelers']);
        $this->assertNotNull($snapshot['quoted_at']);

        foreach ([
            'request_type', 'package_price_id', 'currency', 'date', 'season', 'season_label',
            'package_category', 'level_key', 'level_name', 'adults', 'children',
            'total_travelers', 'price_pp', 'group_total', 'quoted_at',
        ] as $key) {
            $this->assertArrayHasKey($key, $snapshot, "missing snapshot key {$key}");
        }
    }

    public function test_tour_inquiry_group_size_is_derived_server_side(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->from(route('tour.show', $tour->slug))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'tour_package_id' => $tour->id,
                'travel_date'     => '2026-08-15',
                'adults'          => 4,
                'children'        => 1,
            ]))
            ->assertSessionHasNoErrors();

        $inquiry = Inquiry::firstOrFail();
        $this->assertNull($inquiry->total_amount); // custom → never a fake/min price

        $snapshot = $inquiry->quote_snapshot;
        $this->assertSame('custom', $snapshot['request_type']); // 5 travelers → outside 2/4/6
        $this->assertSame(5, $snapshot['group_size']);
        $this->assertSame(5, $snapshot['total_travelers']);
        $this->assertSame(4, $snapshot['adults']);
        $this->assertSame(1, $snapshot['children']);
        $this->assertNull($snapshot['price_pp']);
        $this->assertNull($snapshot['package_price_id']);
        $this->assertNotNull($snapshot['reason']);             // why customization is needed
        $this->assertSame($snapshot['reason'], $snapshot['note']);
    }

    public function test_inquiry_package_request_stores_no_total_and_reason(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0]);

        $this->from(route('tour.show', $tour->slug))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'tour_package_id' => $tour->id,
                'travel_date'     => '2026-08-15',
                'adults'          => 2,
                'children'        => 0,
            ]))
            ->assertSessionHasNoErrors();

        $inquiry = Inquiry::firstOrFail();
        $this->assertNull($inquiry->total_amount);

        $snapshot = $inquiry->quote_snapshot;
        $this->assertSame('package_request', $snapshot['request_type']);
        $this->assertNull($snapshot['group_total']);
        $this->assertNotNull($snapshot['reason']);
    }

    /* ── Snapshot immutability ─────────────────────────────────────────── */

    public function test_inquiry_snapshot_and_total_are_immutable_after_price_edit(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->from(route('tour.show', $tour->slug))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'tour_package_id' => $tour->id,
                'package_level'   => 'LUXURY',
                'travel_date'     => '2026-08-15',
                'adults'          => 2,
                'children'        => 0,
            ]))
            ->assertSessionHasNoErrors();

        $inquiry = Inquiry::firstOrFail();
        $this->assertSame('251.00', $inquiry->total_amount);
        $this->assertSame('125.50', $inquiry->quote_snapshot['price_pp']);

        // The package is later repriced (Phase 2 admin editor writes back to
        // this same table)…
        $row = $tour->packagePrices()->where('season_code', 'HIGH')->where('level_key', 'LUXURY')->firstOrFail();
        $row->update([
            'price_2p'       => '999.00',
            'group_total_2p' => '1998.00',
        ]);
        $row->refresh();

        // …but the stored inquiry snapshot and total_amount never change.
        $inquiry->refresh();
        $this->assertSame('251.00', $inquiry->total_amount);
        $this->assertSame('125.50', $inquiry->quote_snapshot['price_pp']);
        $this->assertSame('251.00', $inquiry->quote_snapshot['group_total']);
        $this->assertSame($row->id, $inquiry->quote_snapshot['package_price_id']);
    }

    /* ── Contextual package-level validation ───────────────────────────── */

    public function test_inquiry_rejects_arbitrary_package_level_string(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->post(route('inquiries.store'), $this->inquiryPayload([
            'tour_package_id' => $tour->id,
            'package_level'   => 'hacker-level-1337',
            'adults'          => 2,
            'children'        => 0,
        ]))->assertSessionHasErrors('package_level');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_inquiry_rejects_standard_level_on_multi_day_tour(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour(['package_duration_type' => 'multi_day']);
        $this->addPrices($tour);

        $this->post(route('inquiries.store'), $this->inquiryPayload([
            'tour_package_id' => $tour->id,
            'package_level'   => 'STANDARD',
            'adults'          => 2,
            'children'        => 0,
        ]))->assertSessionHasErrors('package_level');
    }

    public function test_inquiry_rejects_multiday_level_on_single_day_tour(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour(['package_duration_type' => 'single_day']);
        $this->addPrices($tour, [
            ['level_key' => 'STANDARD', 'level_name' => 'Standard Day Trip', 'season_code' => 'HIGH',
             'price_2p' => '100.00', 'price_4p' => '95.00', 'price_6p' => '90.00',
             'group_total_2p' => '200.00', 'group_total_4p' => '380.00', 'group_total_6p' => '540.00'],
            ['level_key' => 'STANDARD', 'level_name' => 'Standard Day Trip', 'season_code' => 'LOW_WET',
             'price_2p' => '90.00', 'price_4p' => '85.00', 'price_6p' => '80.00',
             'group_total_2p' => '180.00', 'group_total_4p' => '340.00', 'group_total_6p' => '480.00'],
        ]);

        $this->post(route('inquiries.store'), $this->inquiryPayload([
            'tour_package_id' => $tour->id,
            'package_level'   => 'LUXURY',
            'adults'          => 2,
            'children'        => 0,
        ]))->assertSessionHasErrors('package_level');
    }

    public function test_inquiry_rejects_level_belonging_to_another_category(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour(['package_category' => 'LUXURY']);
        $this->addPrices($tour);

        // CLASSIC is a MID_RANGE level — wrong category for this tour.
        $this->post(route('inquiries.store'), $this->inquiryPayload([
            'tour_package_id' => $tour->id,
            'package_level'   => 'CLASSIC',
            'adults'          => 2,
            'children'        => 0,
        ]))->assertSessionHasErrors('package_level');
    }

    public function test_inquiry_requires_explicit_level_on_multilevel_tour(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour(['package_category' => 'LUXURY']);
        $this->addPrices($tour, array_merge($this->standardRows(), [
            ['level_key' => 'EXCLUSIVE', 'level_name' => 'Exclusive', 'season_code' => 'HIGH',
             'price_2p' => '150.00', 'price_4p' => '145.00', 'price_6p' => '140.00',
             'group_total_2p' => '300.00', 'group_total_4p' => '580.00', 'group_total_6p' => '840.00'],
        ]));

        // Two configured levels, none chosen → must not silently fall back to the
        // cheapest (EXCLUSIVE); the submission is rejected.
        $this->post(route('inquiries.store'), $this->inquiryPayload([
            'tour_package_id' => $tour->id,
            'adults'          => 2,
            'children'        => 0,
        ]))->assertSessionHasErrors('package_level');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_inquiry_single_level_tour_does_not_require_explicit_level(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        // Exactly one configured level — no selection needed, still automatic.
        $this->from(route('tour.show', $tour->slug))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'tour_package_id' => $tour->id,
                'travel_date'     => '2026-08-15',
                'adults'          => 2,
                'children'        => 0,
            ]))
            ->assertSessionHasNoErrors();

        $snapshot = Inquiry::firstOrFail()->quote_snapshot;
        $this->assertSame('automatic', $snapshot['request_type']);
        $this->assertSame('LUXURY', $snapshot['level_key']);
    }

    public function test_inquiry_without_budget_fields_still_succeeds(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);
        $tour = $this->makeTour();
        $this->addPrices($tour);

        // Destination-style submission: no tour_package_id, no budget range.
        $this->from(route('home'))
            ->post(route('inquiries.store'), $this->inquiryPayload([
                'travel_date' => '2026-08-15',
                'adults'      => 2,
                'children'    => 0,
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $inquiry = Inquiry::firstOrFail();
        $this->assertSame('general', $inquiry->type);
        $this->assertNull($inquiry->budget_min);
        $this->assertNull($inquiry->budget_max);
        $this->assertNull($inquiry->quote_snapshot);
    }

    public function test_contact_submission_is_not_rejected_by_recaptcha_rule(): void
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);

        $this->from(route('home'))
            ->post(route('contact.submit'), [
                'first_name' => 'John',
                'last_name'  => 'Smith',
                'email'      => 'john@example.com',
                'phone'      => '255700000001',
                'message'    => 'Please tell me more about your tours.',
                'g-recaptcha-response' => 'fake-token',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $inquiry = Inquiry::firstOrFail();
        $this->assertSame('contact', $inquiry->type);
    }

    /* ── Detail page rendering ─────────────────────────────────────────── */

    public function test_show_page_renders_phase_two_price_matrix(): void
    {
        $tour = $this->makeTour();
        $this->addPrices($tour);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('Price per Person')
            ->assertSee('High Season')
            ->assertSee('Low Wet Season')
            ->assertSee('Luxury')
            ->assertDontSee('Package Base Rates')
            ->assertSee('tdFormLevel')
            ->assertSee('price-lookup');
    }

    public function test_show_page_keeps_legacy_display_untouched(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'legacy',
            'base_price' => 1200,
            'season_pricing' => [['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00']]]);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee('Package Base Rates')
            ->assertDontSee('Price per Person'); // no matrix for legacy tours
    }

    public function test_show_page_for_priceless_tour_asks_for_package_price(): void
    {
        $tour = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0]);

        $this->get(route('tour.show', $tour->slug))
            ->assertOk()
            ->assertSee("This tour's package price is not listed", false) // literal apostrophe in the blade
            ->assertSee('Price on Request');
    }

    public function test_home_cards_use_centralised_from_price(): void
    {
        $new = $this->makeTour(['title' => 'Priced Safari', 'is_featured' => true]);
        $this->addPrices($new);
        $legacy = $this->makeTour(['pricing_source' => 'legacy', 'title' => 'Legacy Safari', 'is_featured' => true,
            'base_price' => 1200, 'season_pricing' => [['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00']]]);
        $none = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0, 'title' => 'Quote Only', 'is_featured' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('$110')                          // new-system min 2p price
            ->assertSee('$800')                          // legacy tier price_2p
            ->assertSee('Request a Quote');
    }

    public function test_tours_index_cards_use_centralised_from_price(): void
    {
        $new = $this->makeTour(['title' => 'Priced Safari']);
        $this->addPrices($new);
        $legacy = $this->makeTour(['pricing_source' => 'legacy', 'title' => 'Legacy Safari',
            'base_price' => 1200, 'season_pricing' => [['season' => 'High', 'price_2p' => '800.00', 'price_4p' => '780.00', 'price_6p' => '760.00']]]);
        $none = $this->makeTour(['pricing_source' => 'none', 'base_price' => 0, 'title' => 'Quote Only']);

        $this->get(route('tours.index'))
            ->assertOk()
            ->assertSee('Priced Safari')
            ->assertSee('$110')                          // new-system min 2p price
            ->assertSee('Legacy Safari')
            ->assertSee('$800')                          // legacy tier price_2p
            ->assertSee('Quote Only')
            ->assertSee('Request a Quote');
    }
}