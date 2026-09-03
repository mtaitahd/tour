<?php

namespace App\Pricing;

/**
 * Final step of the calculation: rounding of the per-person selling price.
 * NONE keeps the cent-accurate pre-rounding value; the others round the
 * per-person price to the nearest whole-dollar multiple. The final group
 * total is always rounded_per_person x clients.
 */
enum RoundingRule: string
{
    case NONE = 'none';
    case NEAREST_1 = '1';
    case NEAREST_5 = '5';
    case NEAREST_10 = '10';
    case NEAREST_50 = '50';
    case NEAREST_100 = '100';

    /** The cent multiple this rule rounds to. */
    public function centMultiple(): int
    {
        return match ($this) {
            self::NONE => 1,
            self::NEAREST_1 => 100,
            self::NEAREST_5 => 500,
            self::NEAREST_10 => 1000,
            self::NEAREST_50 => 5000,
            self::NEAREST_100 => 10000,
        };
    }
}