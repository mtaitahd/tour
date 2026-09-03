<?php

namespace App\Services;

use App\Pricing\ChargingBasis;
use App\Pricing\LevelCatalog;
use App\Pricing\PackageDurationType;
use App\Pricing\PriceEngine;
use App\Pricing\PricingInput;
use App\Pricing\PricingValidationException;

/**
 * Framework-side façade over the pure Pricing engine. Reads the catalog (and
 * predefined cost templates) from config/tour.php, validates a raw calculator
 * payload into a {@see PricingInput}, runs the engine, and provides the
 * human-readable label maps the admin UI uses. Never writes to the database —
 * persistence is handled by {@see PricingPersistenceService}.
 *
 * Raw calculator payload shape (also carried by the tour form):
 *   package_duration_type : 'multi_day' | 'single_day'
 *   tour_type             : 'KILIMANJARO' | 'SAFARI'  (required for calculator)
 *   package_category      : 'LUXURY' | 'MID_RANGE' | 'BUDGET' | null
 *   currency              : 'USD' (optional, default USD)
 *   tax_enabled           : bool
 *   tax_percentage        : decimal string 0..100 (2dp)
 *   markup_type           : 'percent' | 'fixed'
 *   markup_value          : decimal string
 *   rounding_rule         : 'none' | '1' | '5' | '10' | '50' | '100'
 *   days, nights          : int
 *   items                 : item[] (see PricingInput::fromArray + predefined_key)
 */
final class PricingService
{
    public const SEASONS = ['HIGH', 'LOW_WET'];

    private const MONEY_PATTERN = '/^\d{1,10}(\.\d{1,2})?$/';
    private const QUANTITY_PATTERN = '/^\d{1,10}(\.\d{1,3})?$/';
    private const CURRENCY_PATTERN = '/^[A-Za-z]{3}$/';

    private static ?LevelCatalog $catalog = null;

    public function levels(): LevelCatalog
    {
        return self::$catalog ??= LevelCatalog::fromConfig(config('tour.level_catalog'));
    }

    /** @return string[] LUXURY, MID_RANGE, BUDGET */
    public function multiDayCategories(): array
    {
        return $this->levels()->multiDayCategories();
    }

    /** @return array<string, string> level key => display name for a category */
    public function levelsFor(string $category): array
    {
        return $this->levels()->levelsFor($category);
    }

    public function singleDayLevel(): array
    {
        return $this->levels()->singleDayLevel();
    }

    /** @return array<int, array<string, mixed>> config templates for a tour type */
    public function predefinedCosts(string $tourType): array
    {
        return config('tour.predefined_costs.' . strtoupper($tourType), []);
    }

    /** @return array<string, string> enum value => human label */
    public function chargingBasisLabels(): array
    {
        return [
            ChargingBasis::PER_PERSON_PER_DAY->value => 'Each client per day',
            ChargingBasis::PER_PERSON_PER_NIGHT->value => 'Each client per night',
            ChargingBasis::PER_PERSON_PER_TRIP->value => 'Each client per trip',
            ChargingBasis::PER_GROUP_PER_DAY->value => 'Whole group per day',
            ChargingBasis::PER_GROUP_PER_TRIP->value => 'Whole group per trip',
            ChargingBasis::PER_VEHICLE_PER_DAY->value => 'Each vehicle per day',
            ChargingBasis::PER_VEHICLE_PER_TRIP->value => 'Each vehicle per trip',
            ChargingBasis::PER_ROOM_PER_NIGHT->value => 'Each room per night',
            ChargingBasis::FIXED_PER_PACKAGE->value => 'Fixed package cost',
        ];
    }

    /** @return array<string, string> basis => inline help text */
    public function chargingBasisHelp(): array
    {
        return [
            ChargingBasis::PER_PERSON_PER_DAY->value => 'Rate x number of clients x number of days',
            ChargingBasis::PER_PERSON_PER_NIGHT->value => 'Rate x number of clients x number of nights',
            ChargingBasis::PER_PERSON_PER_TRIP->value => 'Rate x number of clients x quantity',
            ChargingBasis::PER_GROUP_PER_DAY->value => 'Rate x number of days (once for the whole group)',
            ChargingBasis::PER_GROUP_PER_TRIP->value => 'Rate x quantity (once for the whole group)',
            ChargingBasis::PER_VEHICLE_PER_DAY->value => 'Rate x ceiling(clients / vehicle capacity) x days',
            ChargingBasis::PER_VEHICLE_PER_TRIP->value => 'Rate x ceiling(clients / vehicle capacity) x quantity',
            ChargingBasis::PER_ROOM_PER_NIGHT->value => 'Rate x ceiling(clients / room occupancy) x nights',
            ChargingBasis::FIXED_PER_PACKAGE->value => 'Rate x quantity (fixed cost once for the package)',
        ];
    }

    /**
     * Validate a raw calculator payload into a full PricingInput. Beyond the
     * engine's own validations (unlisted basis, negative money, cross-category
     * levels, missing HIGH/LOW_WET rates on included items, zero occupancy /
     * capacity, invalid markup/rounding, unknown duration) this rejects
     * anything that would not survive Phase 1 storage constraints:
     *   - Shoulder / any non-HIGH/LOW_WET season rate key
     *   - single-day payload declaring a package category
     *   - money larger than decimal(12,2) or more than 2 decimal places
     *   - quantity larger than decimal(12,3)
     *   - malformed 3-letter currencies
     *
     * @throws PricingValidationException
     */
    public function fromRaw(array $raw): PricingInput
    {
        // The admin payload carries the top-level key package_duration_type (so the
        // tour form and persistence layer agree), while the pure engine reads the
        // canonical duration_type. Normalize at the boundary so both remain consistent.
        if (! array_key_exists('duration_type', $raw) && array_key_exists('package_duration_type', $raw)) {
            $raw['duration_type'] = $raw['package_duration_type'];
        }

        $this->assertPayloadAccepted($raw);

        return PricingInput::fromArray($raw, $this->levels());
    }

    /**
     * Round-trip a raw payload through validation and the engine, returning the
     * serialized result (meta + all season/level/group cells).
     *
     * @return array<string, mixed>
     */
    public function calculate(array $raw): array
    {
        $input = $this->fromRaw($raw);
        $result = (new PriceEngine($this->levels()))->calculate($input);

        return $result->toArray();
    }

    /** @return array{0: PricingInput, 1: array<string, mixed>} */
    public function calculateWithInput(array $raw): array
    {
        $input = $this->fromRaw($raw);
        $result = (new PriceEngine($this->levels()))->calculate($input);

        return [$input, $result->toArray()];
    }

    private function assertPayloadAccepted(array $raw): void
    {
        $duration = (string) ($raw['duration_type'] ?? '');
        $category = $raw['package_category'] ?? null;

        if ($duration === PackageDurationType::SINGLE_DAY->value) {
            if (is_string($category) && $category !== '') {
                throw PricingValidationException::for('Single-day tours cannot declare a package category.');
            }
        }

        if (isset($raw['currency'])) {
            if (! is_string($raw['currency']) || ! preg_match(self::CURRENCY_PATTERN, trim($raw['currency']))) {
                throw PricingValidationException::for('Currency must be a 3-letter code.');
            }
        }

        foreach ((array) ($raw['markup_value'] ?? null) as $unused) {
            break;
        }

        foreach ($raw['items'] ?? [] as $index => $item) {
            $itemCurrency = $item['currency'] ?? null;
            if ($itemCurrency !== null && ! preg_match(self::CURRENCY_PATTERN, trim((string) $itemCurrency))) {
                throw PricingValidationException::for("items.{$index}.currency must be a 3-letter code.");
            }

            foreach (['HIGH', 'LOW_WET'] as $allowed) {
                foreach (array_keys($item['rates'] ?? []) as $seasonKey) {
                    if (! in_array($seasonKey, self::SEASONS, true)) {
                        throw PricingValidationException::for("items.{$index} uses unsupported season rate key '{$seasonKey}'.");
                    }
                }
            }

            foreach (['HIGH', 'LOW_WET'] as $seasonKey) {
                $rate = $item['rates'][$seasonKey] ?? null;
                if ($rate !== null && ! preg_match(self::MONEY_PATTERN, (string) $rate)) {
                    throw PricingValidationException::for("items.{$index}.rates.{$seasonKey} is not a valid money value.");
                }
            }

            if (isset($item['quantity']) && ! preg_match(self::QUANTITY_PATTERN, (string) $item['quantity'])) {
                throw PricingValidationException::for("items.{$index}.quantity is not a valid quantity.");
            }
        }

        if (isset($raw['markup_value']) && ! $this->isMoney($raw['markup_value'])) {
            throw PricingValidationException::for('markup_value is not a valid money value.');
        }

        if (isset($raw['tax_percentage']) && ! preg_match(self::MONEY_PATTERN, (string) $raw['tax_percentage'])) {
            throw PricingValidationException::for('tax_percentage is not a valid percentage.');
        }
    }

    private function isMoney(mixed $value): bool
    {
        return is_string($value) || is_numeric($value)
            ? (bool) preg_match(self::MONEY_PATTERN, (string) $value)
            : false;
    }
}