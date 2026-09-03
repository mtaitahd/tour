<?php

namespace App\Pricing;

/**
 * How a package is structured for pricing. Single-day tours are priced as a
 * single STANDARD level with nights forced to zero; multi-day tours calculate
 * every level of their package category.
 */
enum PackageDurationType: string
{
    case SINGLE_DAY = 'single_day';
    case MULTI_DAY = 'multi_day';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_DAY => 'Single Day',
            self::MULTI_DAY => 'Multi Day',
        };
    }
}