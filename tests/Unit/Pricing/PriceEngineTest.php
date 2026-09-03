<?php

namespace Tests\Unit\Pricing;

use App\Pricing\ChargingBasis;
use App\Pricing\LevelCatalog;
use App\Pricing\MarkupType;
use App\Pricing\PackageDurationType;
use App\Pricing\PriceEngine;
use App\Pricing\PricingInput;
use App\Pricing\PricingItemValue;
use App\Pricing\PricingResult;
use App\Pricing\PricingValidationException;
use App\Pricing\RoundingRule;
use App\Pricing\Season;
use PHPUnit\Framework\TestCase;

class PriceEngineTest extends TestCase
{
    private function levels(): LevelCatalog
    {
        $config = require __DIR__.'/../../../config/tour.php';

        return LevelCatalog::fromConfig($config['level_catalog']);
    }

    private function engine(): PriceEngine
    {
        return new PriceEngine($this->levels());
    }

    private function input(array $overrides = [], array $items = []): PricingInput
    {
        $data = array_merge([
            'duration_type' => 'multi_day',
            'tour_type' => 'safari',
            'package_category' => 'BUDGET',
            'tax_enabled' => false,
            'tax_percentage' => '18',
            'markup_type' => 'percent',
            'markup_value' => '0',
            'rounding_rule' => 'none',
            'days' => 3,
            'nights' => 2,
            'items' => $items ?: [$this->item()],
        ], $overrides);

        return PricingInput::fromArray($data, $this->levels());
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cost line',
            'currency' => 'USD',
            'charging_basis' => 'FIXED_PER_PACKAGE',
            'quantity' => '1',
            'taxable' => false,
            'included' => true,
            'shared_across_levels' => true,
            'rates' => ['HIGH' => '100.00', 'LOW_WET' => '90.00'],
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Charging basis formulas
    // ------------------------------------------------------------------

    public function test_every_charging_basis_formula(): void
    {
        $items = [
            $this->item(['name' => 'pp_day', 'charging_basis' => 'PER_PERSON_PER_DAY']),
            $this->item(['name' => 'pp_night', 'charging_basis' => 'PER_PERSON_PER_NIGHT']),
            $this->item(['name' => 'pp_trip', 'charging_basis' => 'PER_PERSON_PER_TRIP']),
            $this->item(['name' => 'grp_day', 'charging_basis' => 'PER_GROUP_PER_DAY']),
            $this->item(['name' => 'grp_trip', 'charging_basis' => 'PER_GROUP_PER_TRIP']),
            $this->item(['name' => 'veh_day', 'charging_basis' => 'PER_VEHICLE_PER_DAY', 'vehicle_capacity' => 2]),
            $this->item(['name' => 'veh_trip', 'charging_basis' => 'PER_VEHICLE_PER_TRIP', 'vehicle_capacity' => 2]),
            $this->item(['name' => 'room_night', 'charging_basis' => 'PER_ROOM_PER_NIGHT', 'room_occupancy' => 2]),
            $this->item(['name' => 'fixed', 'charging_basis' => 'FIXED_PER_PACKAGE', 'quantity' => '2']),
        ];

        $result = $this->engine()->calculate($this->input([], $items));
        $group = $this->group($result, 'HIGH', 'VALUE', 2);

        $expected = [
            'pp_day' => '600.00',  // 100 x 2 x 3 days
            'pp_night' => '400.00', // 100 x 2 x 2 nights
            'pp_trip' => '200.00',  // 100 x 2 x qty 1
            'grp_day' => '300.00',  // 100 x 3 days
            'grp_trip' => '100.00', // 100 x qty 1
            'veh_day' => '300.00',  // 100 x ceil(2/2) x 3
            'veh_trip' => '100.00', // 100 x ceil(2/2) x 1
            'room_night' => '200.00', // 100 x ceil(2/2) x 2 nights
            'fixed' => '200.00',    // 100 x qty 2
        ];

        foreach ($expected as $name => $total) {
            $this->assertSame($total, $this->lineTotal($group, $name), "basis line {$name}");
        }

        // LOW_WET rate 90 applies to the same formulas.
        $lowGroup = $this->group($result, 'LOW_WET', 'VALUE', 2);
        $this->assertSame('540.00', $this->lineTotal($lowGroup, 'pp_day'));
        $this->assertSame('180.00', $this->lineTotal($lowGroup, 'room_night'));
    }

    public function test_vehicle_capacity_uses_ceiling(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'veh', 'charging_basis' => 'PER_VEHICLE_PER_TRIP', 'vehicle_capacity' => 4]),
        ]));

        $this->assertSame('100.00', $this->lineTotal($this->group($result, 'HIGH', 'VALUE', 2), 'veh')); // ceil(2/4)=1
        $this->assertSame('200.00', $this->lineTotal($this->group($result, 'HIGH', 'VALUE', 6), 'veh')); // ceil(6/4)=2
    }

    public function test_room_occupancy_uses_ceiling(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'room', 'charging_basis' => 'PER_ROOM_PER_NIGHT', 'room_occupancy' => 3]),
        ]));

        $this->assertSame('400.00', $this->lineTotal($this->group($result, 'HIGH', 'VALUE', 6), 'room')); // ceil(6/3)=2 x 2 nights x 100
    }

    // ------------------------------------------------------------------
    // Included / excluded / shared / level-specific
    // ------------------------------------------------------------------

    public function test_included_versus_excluded_items(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'in', 'quantity' => '2']),
            $this->item(['name' => 'ex', 'included' => false, 'quantity' => '2']),
        ]));

        $group = $this->group($result, 'HIGH', 'VALUE', 2);
        $this->assertSame('200.00', $this->lineTotal($group, 'in'));
        $this->assertSame('0.00', $this->lineTotal($group, 'ex'));
        $this->assertSame('excluded', $this->lineKind($group, 'ex'));
        $this->assertSame('200.00', $group->costTotal->toString()); // excluded contributes zero
    }

    public function test_shared_items_apply_to_every_level(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'shared', 'shared_across_levels' => true]),
        ]));

        foreach (['ESSENTIAL', 'VALUE', 'PLUS'] as $level) {
            $this->assertSame('100.00', $this->lineTotal($this->group($result, 'HIGH', $level, 2), 'shared'));
        }
    }

    public function test_level_specific_items_apply_only_to_their_exact_level(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'plus_only', 'shared_across_levels' => false, 'level_key' => 'PLUS']),
        ]));

        $this->assertSame('100.00', $this->lineTotal($this->group($result, 'HIGH', 'PLUS', 2), 'plus_only'));
        $plusGroup = $this->group($result, 'HIGH', 'PLUS', 2);
        $optional = $this->group($result, 'HIGH', 'VALUE', 2);
        $this->assertCount(1, $plusGroup->lines);
        $this->assertSame([], $optional->lines);
        $this->assertSame(0, $optional->costTotal->cents());
    }

    // ------------------------------------------------------------------
    // Seasons
    // ------------------------------------------------------------------

    public function test_both_seasons_are_calculated(): void
    {
        $result = $this->engine()->calculate($this->input());

        $this->assertTrue($result->hasGroup('HIGH', 'VALUE', 2));
        $this->assertTrue($result->hasGroup('LOW_WET', 'VALUE', 2));
        $this->assertSame('100.00', $this->group($result, 'HIGH', 'VALUE', 2)->costTotal->toString());
        $this->assertSame('90.00', $this->group($result, 'LOW_WET', 'VALUE', 2)->costTotal->toString());
    }

    public function test_missing_seasonal_rate_is_a_validation_error_in_input(): void
    {
        $this->expectException(PricingValidationException::class);
        $this->expectExceptionMessage('Missing LOW_WET rate');

        $this->input([], [$this->item(['rates' => ['HIGH' => '100.00']])]);
    }

    public function test_missing_seasonal_rate_is_rejected_by_engine_without_silent_copy(): void
    {
        $item = new PricingItemValue(
            name: 'nightly',
            currency: 'USD',
            basis: ChargingBasis::PER_PERSON_PER_NIGHT,
            quantityThousandths: 1000,
            taxable: false,
            included: true,
            sharedAcrossLevels: true,
            levelKey: null,
            nights: 1,
            roomOccupancy: null,
            vehicleCapacity: null,
            rates: ['HIGH' => '100.00'], // LOW_WET deliberately missing
        );

        $input = PricingInput::fromParts(
            durationType: PackageDurationType::MULTI_DAY,
            tourType: 'safari',
            packageCategory: 'BUDGET',
            taxEnabled: false,
            taxPercentage: '18',
            markupType: MarkupType::PERCENT,
            markupValue: '0',
            roundingRule: RoundingRule::NONE,
            days: 2,
            nights: 1,
            items: [$item],
        );

        $this->expectException(PricingValidationException::class);
        $this->expectExceptionMessage('Missing LOW_WET rate');

        $this->engine()->calculate($input);
    }

    public function test_shoulder_season_does_not_exist(): void
    {
        $this->assertNull(Season::tryFromKey('SHOULDER'));

        $this->expectException(PricingValidationException::class);
        $this->input([], [$this->item(['rates' => ['HIGH' => '100.00', 'SHOULDER' => '80.00']])]);
    }

    // ------------------------------------------------------------------
    // Tax
    // ------------------------------------------------------------------

    public function test_tax_applies_only_to_taxable_included_items(): void
    {
        $result = $this->engine()->calculate($this->input([
            'tax_enabled' => true,
            'tax_percentage' => '18',
        ], [
            $this->item(['name' => 'taxable', 'taxable' => true, 'quantity' => '2']),
            $this->item(['name' => 'not_taxable', 'taxable' => false, 'quantity' => '2']),
            $this->item(['name' => 'excluded_taxable', 'taxable' => true, 'included' => false, 'quantity' => '2']),
        ]));

        $group = $this->group($result, 'HIGH', 'VALUE', 2);
        $this->assertSame('200.00', $group->taxableSubtotal->toString());
        $this->assertSame('200.00', $group->nonTaxableSubtotal->toString());
        $this->assertSame('36.00', $group->taxAmount->toString()); // 18% of 200 only
        $this->assertSame('436.00', $group->costTotal->toString());
    }

    public function test_tax_disabled_costs_zero_tax(): void
    {
        $group = $this->group(
            $this->engine()->calculate($this->input(['tax_enabled' => false], [
                $this->item(['name' => 'x', 'taxable' => true, 'quantity' => '2']),
            ])),
            'HIGH',
            'VALUE',
            2
        );
        $this->assertSame('0.00', $group->taxAmount->toString());
        $this->assertSame('200.00', $group->costTotal->toString());
        $this->assertSame('200.00', $group->taxableSubtotal->toString());
    }

    // ------------------------------------------------------------------
    // Markup
    // ------------------------------------------------------------------

    public function test_percentage_markup(): void
    {
        $group = $this->group(
            $this->engine()->calculate($this->input(['markup_type' => 'percent', 'markup_value' => '10'], [
                $this->item(['name' => 'base', 'quantity' => '2']),
            ])),
            'HIGH',
            'VALUE',
            2
        );
        $this->assertSame('200.00', $group->costTotal->toString());
        $this->assertSame('20.00', $group->markupAmount->toString());
        $this->assertSame('220.00', $group->preRoundingGroupTotal->toString());
    }

    public function test_fixed_markup_is_per_complete_group_calculation(): void
    {
        $result = $this->engine()->calculate($this->input(['markup_type' => 'fixed', 'markup_value' => '75'], [
            $this->item(['name' => 'base', 'quantity' => '2']),
        ]));

        foreach ([2, 4, 6] as $clients) {
            $group = $this->group($result, 'HIGH', 'VALUE', $clients);
            $this->assertSame('75.00', $group->markupAmount->toString(), "same fixed markup for {$clients} clients");
            $this->assertSame(20000 + 7500, $group->costTotal->add($group->markupAmount)->cents());
        }
    }

    // ------------------------------------------------------------------
    // Rounding
    // ------------------------------------------------------------------

    public function test_all_rounding_rules_round_final_per_person_only(): void
    {
        // one FIXED item rate 246.90 x qty 1 -> group cost 246.90; pp = 123.45
        $cases = [
            'none' => ['123.45', '246.90'],
            '1' => ['123.00', '246.00'],
            '5' => ['125.00', '250.00'],
            '10' => ['120.00', '240.00'],
            '50' => ['100.00', '200.00'],
            '100' => ['100.00', '200.00'],
        ];

        foreach ($cases as $rule => [$pp, $groupTotal]) {
            $result = $this->engine()->calculate($this->input(['rounding_rule' => $rule], [
                $this->item(['name' => 'x', 'rates' => ['HIGH' => '246.90', 'LOW_WET' => '246.90']]),
            ]));
            $group = $this->group($result, 'HIGH', 'VALUE', 2);
            $this->assertSame($pp, $group->finalPerPerson->toString(), "rule {$rule} per person");
            $this->assertSame($groupTotal, $group->finalGroupTotal->toString(), "rule {$rule} group total");
            $this->assertSame('246.90', $group->preRoundingGroupTotal->toString(), "pre-rounding group retained");
        }
    }

    // ------------------------------------------------------------------
    // Group sizes
    // ------------------------------------------------------------------

    public function test_two_four_six_client_per_person_results(): void
    {
        $result = $this->engine()->calculate($this->input([], [
            $this->item(['name' => 'base', 'quantity' => '2']),
        ]));

        $this->assertSame('100.00', $this->group($result, 'HIGH', 'VALUE', 2)->finalPerPerson->toString());
        $this->assertSame('50.00', $this->group($result, 'HIGH', 'VALUE', 4)->finalPerPerson->toString());
        $this->assertSame('33.33', $this->group($result, 'HIGH', 'VALUE', 6)->finalPerPerson->toString());
        $this->assertSame('199.98', $this->group($result, 'HIGH', 'VALUE', 6)->finalGroupTotal->toString());
    }

    // ------------------------------------------------------------------
    // Single-day
    // ------------------------------------------------------------------

    public function test_single_day_produces_standard_level_only_without_category(): void
    {
        $result = $this->engine()->calculate($this->input([
            'duration_type' => 'single_day',
            'package_category' => 'LUXURY', // ignored for single-day
            'days' => 3,
            'nights' => 2,
        ], [
            $this->item(['name' => 'base', 'rates' => ['HIGH' => '50.00', 'LOW_WET' => '40.00']]),
        ]));

        $this->assertTrue($result->hasGroup('HIGH', 'STANDARD', 2));
        $this->assertTrue($result->hasGroup('LOW_WET', 'STANDARD', 6));
        $this->assertFalse($result->hasGroup('HIGH', 'LUXURY', 2));
        $this->assertCount(2 * 3, $result->allGroups()); // 2 seasons x 1 level x 3 sizes
        $this->assertSame('50.00', $this->group($result, 'HIGH', 'STANDARD', 2)->costTotal->toString());
    }

    public function test_single_day_forces_nights_to_zero(): void
    {
        $result = $this->engine()->calculate($this->input([
            'duration_type' => 'single_day',
            'days' => 3,
            'nights' => 2,
        ], [
            // PER_PERSON_PER_NIGHT item without an explicit item-level night count
            $this->item(['name' => 'nightly', 'charging_basis' => 'PER_PERSON_PER_NIGHT']),
            $this->item(['name' => 'daily', 'charging_basis' => 'PER_PERSON_PER_DAY', 'rates' => ['HIGH' => '20.00', 'LOW_WET' => '10.00']]),
        ]));

        $group = $this->group($result, 'HIGH', 'STANDARD', 2);
        $this->assertSame('0.00', $this->lineTotal($group, 'nightly')); // nights forced to zero
        $this->assertSame('40.00', $this->lineTotal($group, 'daily'));   // 20 x 2 clients x 1 day
    }

    // ------------------------------------------------------------------
    // Levels per category
    // ------------------------------------------------------------------

    public function test_luxury_levels(): void
    {
        $this->assertLevelSet('LUXURY', ['LUXURY', 'EXCLUSIVE', 'ELITE']);
    }

    public function test_mid_range_levels(): void
    {
        $this->assertLevelSet('MID_RANGE', ['CLASSIC', 'COMFORT', 'PREMIUM']);
    }

    public function test_budget_levels(): void
    {
        $this->assertLevelSet('BUDGET', ['ESSENTIAL', 'VALUE', 'PLUS']);
    }

    private function assertLevelSet(string $category, array $expected): void
    {
        $result = $this->engine()->calculate($this->input(['package_category' => $category]));

        $this->assertSame($expected, $this->levelKeys($result));
    }

    // ------------------------------------------------------------------
    // Rejections
    // ------------------------------------------------------------------

    public function test_cross_category_level_rejected(): void
    {
        $this->expectException(PricingValidationException::class);
        $this->expectExceptionMessage('cross-category');

        $this->input(['package_category' => 'BUDGET'], [
            $this->item(['shared_across_levels' => false, 'level_key' => 'ELITE']),
        ]);
    }

    public function test_negative_values_rejected(): void
    {
        $cases = [
            ['basis' => 'PER_VEHICLE_PER_DAY', 'overrides' => ['vehicle_capacity' => 2, 'quantity' => '-1']],
            ['basis' => 'PER_VEHICLE_PER_DAY', 'overrides' => ['vehicle_capacity' => 2, 'rates' => ['HIGH' => '-1.00', 'LOW_WET' => '1.00']]],
            ['basis' => 'PER_ROOM_PER_NIGHT', 'overrides' => ['room_occupancy' => -2]],
            ['basis' => 'PER_VEHICLE_PER_DAY', 'overrides' => ['vehicle_capacity' => -2]],
        ];

        foreach ($cases as $index => $case) {
            try {
                $this->input([], [
                    $this->item(array_merge(['name' => "neg_case_{$index}", 'charging_basis' => $case['basis']], $case['overrides'])),
                ]);
                $this->fail('Expected rejection for: '.json_encode($case));
            } catch (PricingValidationException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_multi_day_requires_positive_days(): void
    {
        $this->expectException(PricingValidationException::class);
        $this->input(['days' => 0]);
    }

    // ------------------------------------------------------------------
    // Money safety on full pipeline
    // ------------------------------------------------------------------

    public function test_full_pipeline_outputs_exact_decimal_strings(): void
    {
        $result = $this->engine()->calculate($this->input([
            'tax_enabled' => true,
            'tax_percentage' => '18',
            'markup_type' => 'percent',
            'markup_value' => '10',
            'rounding_rule' => 'none',
        ], [
            $this->item(['name' => 'pp_day', 'charging_basis' => 'PER_PERSON_PER_DAY', 'taxable' => true, 'rates' => ['HIGH' => '342.05', 'LOW_WET' => '300.00']]),
        ]));

        $json = json_encode($result->toArray());
        $this->assertStringNotContainsString('e-', $json, 'no scientific notation');
        $this->assertMatchesRegularExpression('/"final_per_person":"\d+\.\d{2}"/', $json, 'money serialized as 2dp strings');
        $group = $this->group($result, 'HIGH', 'VALUE', 2);
        // 342.05 x 2 x 3 = 2052.30 ; tax 18% = 369.41 ; markup 10% of 2421.71 = 242.17 ; total 2663.88
        $this->assertSame('2052.30', $group->taxableSubtotal->toString());
        $this->assertSame('369.41', $group->taxAmount->toString());
        $this->assertSame('2421.71', $group->costTotal->toString());
        $this->assertSame('242.17', $group->markupAmount->toString());
        $this->assertSame('2663.88', $group->preRoundingGroupTotal->toString());
        $this->assertSame('1331.94', $group->finalPerPerson->toString());
    }

    // ------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------

    private function group(PricingResult $result, string $season, string $level, int $clients)
    {
        return $result->group($season, $level, $clients);
    }

    private function lineTotal($group, string $name): string
    {
        foreach ($group->lines as $line) {
            if ($line->name === $name) {
                return $line->total;
            }
        }

        $this->fail("Line '{$name}' not found.");
    }

    private function lineKind($group, string $name): string
    {
        foreach ($group->lines as $line) {
            if ($line->name === $name) {
                return $line->kind;
            }
        }

        $this->fail("Line '{$name}' not found.");
    }

    private function levelKeys(PricingResult $result): array
    {
        $keys = [];
        foreach ($result->allGroups() as $group) {
            $keys[$group->levelKey] = true;
        }
        $keys = array_keys($keys);

        return $keys;
    }
}