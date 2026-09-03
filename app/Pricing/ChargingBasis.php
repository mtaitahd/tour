<?php

namespace App\Pricing;

/**
 * The nine supported charging bases and their exact formulas:
 *
 *   PER_PERSON_PER_DAY   rate x clients x days
 *   PER_PERSON_PER_NIGHT rate x clients x nights
 *   PER_PERSON_PER_TRIP  rate x clients x quantity
 *   PER_GROUP_PER_DAY    rate x days
 *   PER_GROUP_PER_TRIP   rate x quantity
 *   PER_VEHICLE_PER_DAY  rate x ceil(clients / vehicle_capacity) x days
 *   PER_VEHICLE_PER_TRIP rate x ceil(clients / vehicle_capacity) x quantity
 *   PER_ROOM_PER_NIGHT   rate x ceil(clients / room_occupancy) x nights
 *   FIXED_PER_PACKAGE    rate x quantity
 *
 * Vehicle/room counts are always rounded up to whole units (a half-full car is
 * still billed as one car), which the engine implements with integer ceiling.
 */
enum ChargingBasis: string
{
    case PER_PERSON_PER_DAY = 'PER_PERSON_PER_DAY';
    case PER_PERSON_PER_NIGHT = 'PER_PERSON_PER_NIGHT';
    case PER_PERSON_PER_TRIP = 'PER_PERSON_PER_TRIP';
    case PER_GROUP_PER_DAY = 'PER_GROUP_PER_DAY';
    case PER_GROUP_PER_TRIP = 'PER_GROUP_PER_TRIP';
    case PER_VEHICLE_PER_DAY = 'PER_VEHICLE_PER_DAY';
    case PER_VEHICLE_PER_TRIP = 'PER_VEHICLE_PER_TRIP';
    case PER_ROOM_PER_NIGHT = 'PER_ROOM_PER_NIGHT';
    case FIXED_PER_PACKAGE = 'FIXED_PER_PACKAGE';

    public function usesVehicleCapacity(): bool
    {
        return in_array($this, [self::PER_VEHICLE_PER_DAY, self::PER_VEHICLE_PER_TRIP], true);
    }

    public function usesRoomOccupancy(): bool
    {
        return $this === self::PER_ROOM_PER_NIGHT;
    }

    public function usesNights(): bool
    {
        return in_array($this, [self::PER_PERSON_PER_NIGHT, self::PER_ROOM_PER_NIGHT], true);
    }
}