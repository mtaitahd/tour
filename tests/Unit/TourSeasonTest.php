<?php

namespace Tests\Unit;

use App\Services\TourSeason;
use Tests\TestCase;

class TourSeasonTest extends TestCase
{
    public function test_all_high_season_months_resolve_to_high(): void
    {
        foreach ([1, 2, 3, 6, 7, 8, 9, 10, 12] as $month) {
            $date = sprintf('2026-%02d-15', $month);

            $this->assertSame(TourSeason::HIGH, TourSeason::resolve($date), "Month {$month} should be HIGH SEASON");
        }
    }

    public function test_all_low_wet_season_months_resolve_to_low_wet(): void
    {
        foreach ([4, 5, 11] as $month) {
            $date = sprintf('2026-%02d-15', $month);

            $this->assertSame(TourSeason::LOW_WET, TourSeason::resolve($date), "Month {$month} should be LOW WET SEASON");
        }
    }

    public function test_resolve_accepts_carbon_instances(): void
    {
        $this->assertSame(TourSeason::HIGH, TourSeason::resolve(\Illuminate\Support\Carbon::parse('2026-08-01')));
        $this->assertSame(TourSeason::LOW_WET, TourSeason::resolve(\Illuminate\Support\Carbon::parse('2026-11-30')));
    }

    public function test_resolve_accepts_null_and_empty_value(): void
    {
        $this->assertSame(TourSeason::HIGH, TourSeason::resolve(null));
        $this->assertSame(TourSeason::HIGH, TourSeason::resolve(''));
    }
}