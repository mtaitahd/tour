<?php

namespace App\Services;

use App\Models\PackagePrice;
use App\Models\PriceCalculation;
use App\Models\PricingItem;
use App\Models\PricingItemRate;
use App\Models\TourPackage;
use App\Pricing\LevelCatalog;
use App\Pricing\PackageDurationType;
use App\Pricing\PricingValidationException;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persists (and removes) the Phase 1 pricing records behind a tour. All public
 * entry points are called from inside a DB transaction by the controlling
 * controller; they never commit on their own. Purchaser/actor ownership ids
 * are passed in explicitly and never extracted from the Request (no
 * mass-assignment of protected fields).
 */
final class PricingPersistenceService
{
    // Raw payload is carried by the form; manual rows are rebuilt to the same
    // key shape so both persistence paths share one persister.
    public const SEASONS = ['HIGH', 'LOW_WET'];

    public function __construct(
        private readonly PricingService $pricing,
        private readonly LevelCatalog $levels,
    ) {
    }

    /**
     * Replace all current new-system package prices for a tour with those
     * derived from one PriceCalculation snapshot (calculator path). If the
     * snapshot has no package prices (single-day with zero rates, or any
     * zero-rate model), every current package price is removed.
     */
    public function replacePackagePricesFromCalculation(
        TourPackage $tour,
        PriceCalculation $calculation,
        array $serialized,
        int $actorId,
        array $meta,
    ): void {
        $this->removeAllCurrentPackagePrices($tour);

        foreach ($this->manualRowsFromSerialized($serialized, $meta) as $row) {
            PackagePrice::create([
                ...$row,
                'tour_package_id' => $tour->getKey(),
                'package_category' => $meta['package_category'],
                'currency' => $meta['currency'],
                'price_calculation_id' => $calculation->getKey(),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        }
    }

    /**
     * Replace current package prices for a manual tour. Prices arrive as rows
     * of per-person values; group totals are computed server-side by the pure
     * Money type (per-person x clients), never trusted from the browser.
     */
    public function replaceManualPackagePrices(
        TourPackage $tour,
        array $rows,
        string $packageCategory,
        string $currency,
        PackageDurationType $durationType,
        int $actorId,
        ?int $calculationId = null,
    ): void {
        $this->removeAllCurrentPackagePrices($tour);

        $singleDay = $durationType === PackageDurationType::SINGLE_DAY;

        foreach ($rows as $row) {
            $levelKey = (string) $row['level_key'];
            $season = (string) $row['season_code'];
            $price2 = \App\Pricing\Money::fromDecimalString($row['price_2p']);
            $price4 = \App\Pricing\Money::fromDecimalString($row['price_4p']);
            $price6 = \App\Pricing\Money::fromDecimalString($row['price_6p']);

            PackagePrice::create([
                'tour_package_id' => $tour->getKey(),
                'package_category' => $singleDay ? null : $packageCategory,
                'level_key' => $levelKey,
                'level_name' => $this->levels->levelName($levelKey) ?? $levelKey,
                'season_code' => $season,
                'price_2p' => $price2->toString(),
                'price_4p' => $price4->toString(),
                'price_6p' => $price6->toString(),
                'group_total_2p' => $price2->multiply(2)->toString(),
                'group_total_4p' => $price4->multiply(4)->toString(),
                'group_total_6p' => $price6->multiply(6)->toString(),
                'currency' => $currency,
                'price_calculation_id' => $calculationId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        }
    }

    /**
     * Remove every current package price for the tour (price_calculation_id
     * is set null first / set null by FK rule, preserving history). Called only
     * inside a successful save transaction.
     */
    public function removeAllCurrentPackagePrices(TourPackage $tour): void
    {
        PackagePrice::where('tour_package_id', $tour->getKey())->delete();
    }

    /**
     * Create the immutable calculation snapshot with its item rows and both
     * seasonal rates. `$input` items with an empty level_key are persisted as
     * shared; otherwise the row is level-specific. Predefined costs map back to
     * their template key when provided.
     *
     * @param array<int, array<string, mixed>> $itemPayloads raw calculator items (for predefined_key)
     *
     * @return PriceCalculation
     */
    public function createCalculation(
        TourPackage $tour,
        array $raw,
        array $serialized,
        int $actorId,
        array $itemPayloads,
    ): PriceCalculation {
        $calculation = PriceCalculation::create([
            'tour_package_id' => $tour->getKey(),
            'package_duration_type' => (string) ($raw['package_duration_type'] ?? $serialized['meta']['duration_type'] ?? 'multi_day'),
            'tour_type' => (string) ($raw['tour_type'] ?? $serialized['meta']['tour_type'] ?? ''),
            'package_category' => $serialized['meta']['package_category'] ?? null,
            'tax_enabled' => (bool) ($serialized['meta']['tax_enabled'] ?? true),
            'tax_percentage' => (string) ($serialized['meta']['tax_percentage'] ?? '18'),
            'markup_type' => (string) ($serialized['meta']['markup_type'] ?? 'percent'),
            'markup_value' => (string) ($serialized['meta']['markup_value'] ?? '0'),
            'rounding_rule' => (string) ($serialized['meta']['rounding_rule'] ?? 'none'),
            'breakdown' => [
                'payload' => $raw,
                'result' => $serialized,
            ],
            'results' => $this->compactResults($serialized),
            'created_by' => $actorId,
        ]);

        $input = $this->pricing->fromRaw($raw);

        $byPosition = [];
        foreach ($input->items() as $itemValue) {
            $byPosition[$itemValue->position] ??= $itemValue;
        }

        foreach ($raw['items'] ?? [] as $index => $itemRaw) {
            $position = (int) ($itemRaw['position'] ?? $index);

            $item = $byPosition[$position] ?? null;
            if ($item === null) {
                throw PricingValidationException::for("Missing engine item for raw item at position {$position}.");
            }

            $record = PricingItem::create([
                'price_calculation_id' => $calculation->getKey(),
                'predefined_key' => $itemRaw['predefined_key'] ?? null,
                'name' => $item->name,
                'currency' => $item->currency,
                'charging_basis' => $item->basis->value,
                'quantity' => sprintf('%d.%03d', intdiv($item->quantityThousandths, 1000), $item->quantityThousandths % 1000),
                'taxable' => $item->taxable,
                'included' => $item->included,
                'shared_across_levels' => $item->sharedAcrossLevels,
                'level_key' => $item->levelKey,
                'nights' => $item->nights,
                'room_occupancy' => $item->roomOccupancy,
                'vehicle_capacity' => $item->vehicleCapacity,
                'notes' => $item->notes,
                'position' => $position,
            ]);

            foreach ($item->rates as $seasonCode => $amount) {
                PricingItemRate::create([
                    'pricing_item_id' => $record->getKey(),
                    'season_code' => $seasonCode,
                    'amount' => \App\Pricing\Money::fromDecimalString($amount)->toString(),
                ]);
            }
        }

        return $calculation;
    }

    /**
     * Fresh-form manual rows have no grouping season/level ordering enforced by
     * the engine, so the parser walks `serialized` (authoritative) and returns
     * a single season/level row set (one row per season/level comb – all three
     * group sizes) — exactly the package_prices combos.
     */
    private function manualRowsFromSerialized(array $serialized, array $meta): array
    {
        $rows = [];

        foreach ($serialized['groups'] ?? [] as $seasonCode => $levelGroups) {
            foreach ($levelGroups as $levelKey => $sizes) {
                foreach ($sizes as $clients => $group) {
                    $group['clients'] = (int) $clients;
                    $rows["{$seasonCode}|{$levelKey}"]['season'] ??= $seasonCode;
                    $rows["{$seasonCode}|{$levelKey}"]['level'] ??= $levelKey;
                    $rows["{$seasonCode}|{$levelKey}"]['level_name'] ??= $group['level_name'] ?? $levelKey;
                    $rows["{$seasonCode}|{$levelKey}"]["price_{$clients}p"] = $group['final_per_person'] ?? '0.00';
                    $rows["{$seasonCode}|{$levelKey}"]["group_total_{$clients}p"] = $group['final_group_total'] ?? '0.00';
                }
            }
        }

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'season_code' => $r['season'],
                'level_key' => $r['level'],
                'level_name' => $r['level_name'],
                'price_2p' => $r['price_2p'],
                'price_4p' => $r['price_4p'],
                'price_6p' => $r['price_6p'],
                'group_total_2p' => $r['group_total_2p'],
                'group_total_4p' => $r['group_total_4p'],
                'group_total_6p' => $r['group_total_6p'],
            ];
        }

        return $result;
    }

    private function compactResults(array $serialized): array
    {
        $compact = [];
        foreach ($serialized['groups'] ?? [] as $seasonCode => $levelGroups) {
            foreach ($levelGroups as $levelKey => $sizes) {
                foreach ($sizes as $clients => $group) {
                    $compact[$seasonCode][$levelKey][(string) $clients] = [
                        'final_per_person' => $group['final_per_person'] ?? '0.00',
                        'final_group_total' => $group['final_group_total'] ?? '0.00',
                        'pre_group' => $group['pre_rounding_group_total'] ?? '0.00',
                        'cost' => $group['cost_total'] ?? '0.00',
                        'tax' => $group['tax_amount'] ?? '0.00',
                        'markup' => $group['markup_amount'] ?? '0.00',
                    ];
                }
            }
        }

        return $compact;
    }
}