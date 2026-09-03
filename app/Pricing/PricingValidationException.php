<?php

namespace App\Pricing;

use InvalidArgumentException;

/**
 * Raised by validators and the engine for invalid or incomplete calculation
 * input: negative values, unsupported seasons (including Shoulder Season),
 * zero occupancy/vehicle capacity where required, cross-category levels,
 * and missing seasonal rates.
 */
final class PricingValidationException extends InvalidArgumentException
{
    public static function for(string $message): self
    {
        return new self($message);
    }
}