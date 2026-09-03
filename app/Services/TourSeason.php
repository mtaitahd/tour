<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Resolves a travel date into one of the two tour pricing seasons.
 *
 * Month ranges live in config/tour.php as the single source of truth.
 */
class TourSeason
{
    public const HIGH = 'HIGH';

    public const LOW_WET = 'LOW_WET';

    public static function resolve(string|CarbonInterface|null $date): string
    {
        if ($date === null || $date === '') {
            return self::HIGH;
        }

        $carbon = $date instanceof CarbonInterface ? Carbon::instance($date) : Carbon::parse($date);

        foreach (config('tour.seasons.high') as $month) {
            if ($carbon->month === (int) $month) {
                return self::HIGH;
            }
        }

        return self::LOW_WET;
    }
}