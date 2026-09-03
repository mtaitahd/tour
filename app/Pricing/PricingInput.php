<?php

namespace App\Pricing;

/**
 * Validated, framework-independent calculation input. Everything the engine
 * needs is passed here explicitly — it never reads Request objects, sessions,
 * Blade templates or JavaScript state.
 *
 * Rejection rules enforced on construction:
 * - negative values (quantities, money, percentages, markups, counts)
 * - unsupported / Shoulder seasons
 * - zero room occupancy / vehicle capacity where the basis requires it
 * - negative nights
 * - cross-category levels (level not in the selected category)
 * - single-day input declaring a category
 * - missing HIGH or LOW_WET rate on any included item
 */
final class PricingInput
{
    public const DAYS_SINGLE_DAY = 1;

    private function __construct(
        public readonly PackageDurationType $durationType,
        public readonly string $tourType,
        public readonly ?string $packageCategory,
        public readonly bool $taxEnabled,
        public readonly string $taxPercentage,
        public readonly MarkupType $markupType,
        public readonly string $markupValue,
        public readonly RoundingRule $roundingRule,
        public readonly int $days,
        public readonly int $nights,
        /** @var PricingItemValue[] */
        public readonly array $items,
    ) {
    }

    /**
     * @param array<string, mixed> $data validated by {@see self::fromArray}
     */
    public static function fromArray(array $data, LevelCatalog $levels): self
    {
        $durationType = PackageDurationType::tryFrom((string) ($data['duration_type'] ?? ''));

        if ($durationType === null) {
            throw PricingValidationException::for('Invalid package duration type.');
        }

        if ($durationType === PackageDurationType::SINGLE_DAY) {
            $packageCategory = null;
        } else {
            $packageCategory = $data['package_category'] ?? null;

            if (! is_string($packageCategory) || $packageCategory === '') {
                throw PricingValidationException::for('Multi-day tours require a package category.');
            }

            if (! in_array($packageCategory, $levels->multiDayCategories(), true)) {
                throw PricingValidationException::for("Unknown package category '{$packageCategory}'.");
            }
        }

        $roundingRule = RoundingRule::tryFrom((string) ($data['rounding_rule'] ?? ''));
        if ($roundingRule === null) {
            throw PricingValidationException::for('Invalid rounding rule.');
        }

        $markupType = MarkupType::tryFrom((string) ($data['markup_type'] ?? ''));
        if ($markupType === null) {
            throw PricingValidationException::for('Invalid markup type.');
        }

        $tourType = trim((string) ($data['tour_type'] ?? ''));
        if ($tourType === '') {
            throw PricingValidationException::for('tour_type must be a non-empty string.');
        }

        $daysRaw = (int) ($data['days'] ?? 0);
        $nightsRaw = (int) ($data['nights'] ?? 0);

        if ($durationType === PackageDurationType::MULTI_DAY && $daysRaw < 1) {
            throw PricingValidationException::for('Multi-day tours require a positive days value.');
        }

        if ($durationType === PackageDurationType::MULTI_DAY && $nightsRaw < 0) {
            throw PricingValidationException::for('nights must be non-negative.');
        }

        return new self(
            $durationType,
            $tourType,
            $packageCategory,
            (bool) ($data['tax_enabled'] ?? true),
            self::validatePercentage($data['tax_percentage'] ?? '18', 'tax_percentage', 0, 100),
            $markupType,
            self::validateNonNegativeDecimal($data['markup_value'] ?? '0', 'markup_value'),
            $roundingRule,
            $durationType === PackageDurationType::SINGLE_DAY
                ? self::DAYS_SINGLE_DAY
                : $daysRaw,
            $durationType === PackageDurationType::SINGLE_DAY
                ? 0
                : $nightsRaw,
            self::validateItems($data['items'] ?? [], $durationType, $packageCategory, $levels),
        );
    }

    public function isSingleDay(): bool
    {
        return $this->durationType === PackageDurationType::SINGLE_DAY;
    }

    /**
     * Rebuilds input from already-constructed item values (e.g. when the
     * persistence layer reconstructs a calculation from stored records in
     * Phase 2, or when an item cannot pass `fromArray`'s rate completeness
     * requirement by design). Item structural validation happened at the
     * PricingItemValue constructor; the engine performs the final rate and
     * level checks.
     *
     * @param PricingItemValue[] $items
     */
    public static function fromParts(
        PackageDurationType $durationType,
        string $tourType,
        ?string $packageCategory,
        bool $taxEnabled,
        string $taxPercentage,
        MarkupType $markupType,
        string $markupValue,
        RoundingRule $roundingRule,
        int $days,
        int $nights,
        array $items,
    ): self {
        return new self(
            $durationType,
            $tourType,
            $packageCategory,
            $taxEnabled,
            $taxPercentage,
            $markupType,
            $markupValue,
            $roundingRule,
            $days,
            $nights,
            $items,
        );
    }

    /** @return PricingItemValue[] */
    public function items(): array
    {
        return $this->items;
    }

    /** Percentage as hundredths of a percent (18.00 % -> 1800). */
    public function taxPercentageHundredths(): int
    {
        return self::decimalToHundredths($this->taxPercentage);
    }

    /** Markup as hundredths of a percent when markup_type is percent. */
    public function markupValueHundredths(): int
    {
        return self::decimalToHundredths($this->markupValue);
    }

    // ------------------------------------------------------------------
    // Validation helpers
    // ------------------------------------------------------------------

    private static function validatePercentage(mixed $value, string $field, float $min, float $max): string
    {
        $decimal = (string) $value;

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $decimal)) {
            throw PricingValidationException::for("{$field} must be a non-negative decimal with up to 2 places.");
        }

        $numeric = (float) $decimal;
        if ($numeric < $min || $numeric > $max) {
            throw PricingValidationException::for("{$field} must be between {$min} and {$max}.");
        }

        return $decimal;
    }

    private static function validateNonNegativeDecimal(mixed $value, string $field): string
    {
        $decimal = (string) $value;

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $decimal)) {
            throw PricingValidationException::for("{$field} must be a non-negative decimal with up to 2 places.");
        }

        return $decimal;
    }

    /**
     * @param array<int, mixed> $items
     * @return PricingItemValue[]
     */
    private static function validateItems(array $items, PackageDurationType $durationType, ?string $category, LevelCatalog $levels): array
    {
        $result = [];

        foreach ($items as $index => $data) {
            $position = (int) ($data['position'] ?? $index);

            $basis = ChargingBasis::tryFrom((string) ($data['charging_basis'] ?? ''));
            if ($basis === null) {
                throw PricingValidationException::for("Item #{$index} has an invalid charging basis.");
            }

            $quantityThousandths = self::decimalToThousandths(
                self::validateNonNegativeDecimal($data['quantity'] ?? '1', "items.{$index}.quantity")
            );

            $shared = (bool) ($data['shared_across_levels'] ?? false);
            $levelKey = isset($data['level_key']) && $data['level_key'] !== '' ? (string) $data['level_key'] : null;

            if ($levelKey !== null && ! $levels->hasLevel($levelKey)) {
                throw PricingValidationException::for("Item #{$index} references unknown level '{$levelKey}'.");
            }

            if (! $shared && $durationType === PackageDurationType::MULTI_DAY && $category !== null) {
                if (! $levels->levelBelongsTo($category, $levelKey ?? '')) {
                    throw PricingValidationException::for(
                        "Item #{$index} is cross-category: level '{$levelKey}' does not belong to category '{$category}'."
                    );
                }
            }

            if (! $shared && $durationType === PackageDurationType::SINGLE_DAY) {
                [$singleKey] = $levels->singleDayLevel();
                if ($levelKey !== $singleKey) {
                    throw PricingValidationException::for(
                        "Item #{$index} targets '{$levelKey}' but single-day tours only have the {$singleKey} level."
                    );
                }
            }

            $roomOccupancy = self::nullableNonNegativeInt($data['room_occupancy'] ?? null, "items.{$index}.room_occupancy");
            $vehicleCapacity = self::nullableNonNegativeInt($data['vehicle_capacity'] ?? null, "items.{$index}.vehicle_capacity");
            $nights = self::nullableNonNegativeInt($data['nights'] ?? null, "items.{$index}.nights");

            if ($basis->usesRoomOccupancy() && ($roomOccupancy === null || $roomOccupancy < 1)) {
                throw PricingValidationException::for(
                    "Item #{$index} uses {$basis->value} and requires a positive room occupancy."
                );
            }

            if ($basis->usesVehicleCapacity() && ($vehicleCapacity === null || $vehicleCapacity < 1)) {
                throw PricingValidationException::for(
                    "Item #{$index} uses {$basis->value} and requires a positive vehicle capacity."
                );
            }

            $rates = [];
            foreach (['HIGH', 'LOW_WET'] as $seasonKey) {
                $amount = $data['rates'][$seasonKey] ?? $data["rate_{$seasonKey}"] ?? null;
                if ($amount !== null) {
                    $rates[$seasonKey] = self::validateNonNegativeDecimal(
                        $amount,
                        "items.{$index}.rates.{$seasonKey}"
                    );
                }
            }

            $included = (bool) ($data['included'] ?? true);
            $itemName = (string) ($data['name'] ?? '');

            if ($included) {
                foreach ([Season::HIGH, Season::LOW_WET] as $season) {
                    if (! array_key_exists($season->value, $rates)) {
                        throw PricingValidationException::for(
                            "Missing {$season->value} rate for item #{$index} ('{$itemName}')."
                        );
                    }
                }
            }

            $result[] = new PricingItemValue(
                name: (string) ($data['name'] ?? ''),
                currency: strtoupper((string) ($data['currency'] ?? 'USD')),
                basis: $basis,
                quantityThousandths: $quantityThousandths,
                taxable: (bool) ($data['taxable'] ?? false),
                included: $included,
                sharedAcrossLevels: $shared,
                levelKey: $levelKey,
                nights: $nights,
                roomOccupancy: $roomOccupancy,
                vehicleCapacity: $vehicleCapacity,
                rates: $rates,
                notes: (string) ($data['notes'] ?? ''),
                position: $position,
            );
        }

        return $result;
    }

    private static function nullableNonNegativeInt(mixed $value, string $field): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;
        if ($int < 0) {
            throw PricingValidationException::for("{$field} must be non-negative.");
        }

        return $int;
    }

    private static function decimalToThousandths(string $decimal): int
    {
        if (str_contains($decimal, '.')) {
            [$whole, $fraction] = explode('.', $decimal, 2);
            $fraction = str_pad(substr($fraction, 0, 3), 3, '0');

            return ((int) $whole * 1000) + (int) $fraction;
        }

        return (int) $decimal * 1000;
    }

    private static function decimalToHundredths(string $decimal): int
    {
        if (str_contains($decimal, '.')) {
            [$whole, $fraction] = explode('.', $decimal, 2);
            $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

            return ((int) $whole * 100) + (int) $fraction;
        }

        return (int) $decimal * 100;
    }
}