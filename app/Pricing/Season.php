<?php

namespace App\Pricing;

/**
 * Supported pricing seasons. Only High Season and Low Wet Season exist in the
 * product model. Shoulder Season was intentionally removed and is rejected.
 */
enum Season: string
{
    case HIGH = 'HIGH';
    case LOW_WET = 'LOW_WET';

    public static function tryFromKey(string $key): ?self
    {
        return match (strtoupper($key)) {
            'HIGH' => self::HIGH,
            'LOW_WET' => self::LOW_WET,
            default => null,
        };
    }
}