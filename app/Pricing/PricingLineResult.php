<?php

namespace App\Pricing;

/**
 * One itemized line in a group result. All money is serialized as exactly-2dp
 * decimal strings (never floats). Excluded items appear with a zero total;
 * level-specific items targeting other levels are simply absent.
 */
final class PricingLineResult
{
    private function __construct(
        public readonly string $name,
        public readonly string $basis,
        public readonly Season $season,
        public readonly string $rate,
        public readonly string $quantity,
        public readonly bool $taxable,
        public readonly bool $sharedAcrossLevels,
        public readonly ?string $levelKey,
        public readonly string $kind,
        public readonly string $total,
    ) {
    }

    public static function included(PricingItemValue $item, Season $season, Money $total): self
    {
        return new self(
            name: $item->name,
            basis: $item->basis->value,
            season: $season,
            rate: $item->rateFor($season)->toString(),
            quantity: self::thousandthsToString($item->quantityThousandths),
            taxable: $item->taxable,
            sharedAcrossLevels: $item->sharedAcrossLevels,
            levelKey: $item->levelKey,
            kind: 'included',
            total: $total->toString(),
        );
    }

    public static function excluded(PricingItemValue $item, Season $season): self
    {
        return new self(
            name: $item->name,
            basis: $item->basis->value,
            season: $season,
            rate: array_key_exists($season->value, $item->rates)
                ? $item->rateFor($season)->toString()
                : Money::zero()->toString(),
            quantity: self::thousandthsToString($item->quantityThousandths),
            taxable: $item->taxable,
            sharedAcrossLevels: $item->sharedAcrossLevels,
            levelKey: $item->levelKey,
            kind: 'excluded',
            total: Money::zero()->toString(),
        );
    }

    private static function thousandthsToString(int $thousandths): string
    {
        return sprintf('%d.%03d', intdiv($thousandths, 1000), $thousandths % 1000);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'basis' => $this->basis,
            'season' => $this->season->value,
            'rate' => $this->rate,
            'quantity' => $this->quantity,
            'taxable' => $this->taxable,
            'shared_across_levels' => $this->sharedAcrossLevels,
            'level_key' => $this->levelKey,
            'kind' => $this->kind,
            'total' => $this->total,
        ];
    }
}