<?php

namespace App\Pricing;

/**
 * A single cost-line item as submitted for a calculation. Validated on
 * construction: negative quantities, malformed currencies and invalid bases
 * are rejected here. Included items must carry an explicit HIGH and LOW_WET
 * rate — the engine never copies or substitutes one season's rate for another.
 */
final class PricingItemValue
{
    private const CURRENCY_PATTERN = '/^[A-Z]{3}$/';

    public function __construct(
        public readonly string $name,
        public readonly string $currency,
        public readonly ChargingBasis $basis,
        public readonly int $quantityThousandths,
        public readonly bool $taxable,
        public readonly bool $included,
        public readonly bool $sharedAcrossLevels,
        public readonly ?string $levelKey,
        public readonly ?int $nights,
        public readonly ?int $roomOccupancy,
        public readonly ?int $vehicleCapacity,
        public readonly array $rates,
        public readonly string $notes = '',
        public readonly int $position = 0,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->name === '' || strlen($this->name) > 160) {
            throw PricingValidationException::for('Item name must be a non-empty string of at most 160 characters.');
        }

        if (! preg_match(self::CURRENCY_PATTERN, $this->currency)) {
            throw PricingValidationException::for("Invalid currency '{$this->currency}' (expected a 3-letter ISO code).");
        }

        if ($this->quantityThousandths < 0) {
            throw PricingValidationException::for("Item '{$this->name}' has a negative quantity.");
        }

        if ($this->sharedAcrossLevels && $this->levelKey !== null) {
            throw PricingValidationException::for(
                "Shared item '{$this->name}' must not declare a level key."
            );
        }

        if (! $this->sharedAcrossLevels && $this->levelKey === null) {
            throw PricingValidationException::for(
                "Level-specific item '{$this->name}' must declare a level key."
            );
        }

        if ($this->nights !== null && $this->nights < 0) {
            throw PricingValidationException::for("Item '{$this->name}' has a negative nights value.");
        }

        if ($this->roomOccupancy !== null && $this->roomOccupancy < 0) {
            throw PricingValidationException::for("Item '{$this->name}' has a negative room occupancy.");
        }

        if ($this->vehicleCapacity !== null && $this->vehicleCapacity < 0) {
            throw PricingValidationException::for("Item '{$this->name}' has a negative vehicle capacity.");
        }

        foreach ($this->rates as $seasonCode => $amount) {
            if (! in_array($seasonCode, ['HIGH', 'LOW_WET'], true)) {
                throw PricingValidationException::for("Item '{$this->name}' has an unsupported season rate key.");
            }

            if (! is_string($amount) && ! ($amount instanceof Money)) {
                throw PricingValidationException::for("Item '{$this->name}' has a malformed rate value.");
            }
        }
    }

    public function isLevelSpecific(): bool
    {
        return ! $this->sharedAcrossLevels;
    }

    public function rateFor(Season $season): Money
    {
        $key = $season->value;

        if (! array_key_exists($key, $this->rates)) {
            throw PricingValidationException::for(
                "Missing {$season->value} rate for item '{$this->name}'."
            );
        }

        return Money::fromDecimalString($this->rates[$key]);
    }
}