<?php

namespace App\Pricing;

/**
 * The pure, framework-independent price calculation engine.
 *
 * It reads no Request objects, sessions, Blade templates or JavaScript state:
 * everything flows in through a validated {@see PricingInput} (which embeds
 * the {@see LevelCatalog}). It never writes to the database — persistence is
 * a separate application-layer concern (Phase 2).
 *
 * A single run calculates both seasons (HIGH and LOW_WET) for every group
 * size (2, 4, 6). Multi-day tours calculate all three levels belonging to the
 * selected package category; single-day tours calculate one STANDARD level
 * with nights forced to zero.
 */
final class PriceEngine
{
    public const GROUP_SIZES = [2, 4, 6];

    /** @var Season[] */
    public const SEASONS = [Season::HIGH, Season::LOW_WET];

    public function __construct(private readonly LevelCatalog $levels)
    {
    }

    public function calculate(PricingInput $input): PricingResult
    {
        $result = new PricingResult($input, $this->levels);

        if ($input->isSingleDay()) {
            $levels = $this->levels->singleDayLevel();
        } else {
            $levels = $this->levels->levelsFor($input->packageCategory);
        }

        foreach (self::SEASONS as $season) {
            foreach ($levels as $levelKey => $levelName) {
                foreach (self::GROUP_SIZES as $clients) {
                    $result->addGroup(
                        $this->computeGroup($input, $season, $levelKey, $levelName, $clients)
                    );
                }
            }
        }

        return $result;
    }

    private function computeGroup(
        PricingInput $input,
        Season $season,
        string $levelKey,
        string $levelName,
        int $clients
    ): PricingGroupResult {
        $lines = [];
        $taxableSubtotal = Money::zero();
        $nonTaxableSubtotal = Money::zero();

        foreach ($input->items() as $item) {
            if (! $item->included) {
                $lines[] = PricingLineResult::excluded($item, $season);
                continue;
            }

            if ($item->isLevelSpecific() && $item->levelKey !== $levelKey) {
                // Level-specific item targeting another level: not applied here.
                continue;
            }

            $nights = $item->nights ?? $input->nights;
            $total = $this->lineTotal($item, $season, $clients, $input->days, $nights);

            if ($item->taxable) {
                $taxableSubtotal = $taxableSubtotal->add($total);
            } else {
                $nonTaxableSubtotal = $nonTaxableSubtotal->add($total);
            }

            $lines[] = PricingLineResult::included($item, $season, $total);
        }

        $tax = $input->taxEnabled
            ? $taxableSubtotal->applyPercentHundredths($input->taxPercentageHundredths())
            : Money::zero();

        $costTotal = $taxableSubtotal->add($nonTaxableSubtotal)->add($tax);

        $markup = match ($input->markupType) {
            MarkupType::PERCENT => $costTotal->applyPercentHundredths($input->markupValueHundredths()),
            MarkupType::FIXED => Money::fromDecimalString($input->markupValue),
        };

        $sellingGroupTotal = $costTotal->add($markup);
        $preRoundingPerPerson = $sellingGroupTotal->divide($clients);

        $finalPerPerson = $input->roundingRule === RoundingRule::NONE
            ? $preRoundingPerPerson
            : $preRoundingPerPerson->roundToCentMultiple($input->roundingRule->centMultiple());

        $finalGroupTotal = $finalPerPerson->multiply($clients);

        return new PricingGroupResult(
            season: $season,
            levelKey: $levelKey,
            levelName: $levelName,
            clients: $clients,
            lines: $lines,
            taxableSubtotal: $taxableSubtotal,
            nonTaxableSubtotal: $nonTaxableSubtotal,
            taxAmount: $tax,
            costTotal: $costTotal,
            markupAmount: $markup,
            preRoundingGroupTotal: $sellingGroupTotal,
            preRoundingPerPerson: $preRoundingPerPerson,
            finalPerPerson: $finalPerPerson,
            finalGroupTotal: $finalGroupTotal,
        );
    }

    private function lineTotal(
        PricingItemValue $item,
        Season $season,
        int $clients,
        int $days,
        int $nights
    ): Money {
        $rate = $item->rateFor($season);
        $quantity = $item->quantityThousandths;

        return match ($item->basis) {
            ChargingBasis::PER_PERSON_PER_DAY => $rate->multiply($clients)->multiply($days),
            ChargingBasis::PER_PERSON_PER_NIGHT => $rate->multiply($clients)->multiply($nights),
            ChargingBasis::PER_PERSON_PER_TRIP => $rate->multiply($clients)->multiplyQuantity($quantity),
            ChargingBasis::PER_GROUP_PER_DAY => $rate->multiply($days),
            ChargingBasis::PER_GROUP_PER_TRIP => $rate->multiplyQuantity($quantity),
            ChargingBasis::PER_VEHICLE_PER_DAY => $rate
                ->multiply($this->ceilDiv($clients, $item->vehicleCapacity ?? 1))
                ->multiply($days),
            ChargingBasis::PER_VEHICLE_PER_TRIP => $rate
                ->multiply($this->ceilDiv($clients, $item->vehicleCapacity ?? 1))
                ->multiplyQuantity($quantity),
            ChargingBasis::PER_ROOM_PER_NIGHT => $rate
                ->multiply($this->ceilDiv($clients, $item->roomOccupancy ?? 1))
                ->multiply($nights),
            ChargingBasis::FIXED_PER_PACKAGE => $rate->multiplyQuantity($quantity),
        };
    }

    private function ceilDiv(int $dividend, int $divisor): int
    {
        return intdiv($dividend + $divisor - 1, $divisor);
    }
}