<?php

namespace App\Pricing;

/**
 * The full result for one season + level + group size: itemized line totals,
 * taxable / non-taxable subtotals, tax, cost total, markup, pre-rounding
 * group total and per-person price, and the final rounded values. The
 * pre-rounding breakdown is retained for audit transparency.
 */
final class PricingGroupResult
{
    /** @param PricingLineResult[] $lines */
    public function __construct(
        public readonly Season $season,
        public readonly string $levelKey,
        public readonly string $levelName,
        public readonly int $clients,
        public readonly array $lines,
        public readonly Money $taxableSubtotal,
        public readonly Money $nonTaxableSubtotal,
        public readonly Money $taxAmount,
        public readonly Money $costTotal,
        public readonly Money $markupAmount,
        public readonly Money $preRoundingGroupTotal,
        public readonly Money $preRoundingPerPerson,
        public readonly Money $finalPerPerson,
        public readonly Money $finalGroupTotal,
    ) {
    }

    /**
     * @return array<string, mixed> serialized for storage/JSON; all money is
     *                               exactly-2dp decimal strings.
     */
    public function toArray(): array
    {
        return [
            'season' => $this->season->value,
            'level_key' => $this->levelKey,
            'level_name' => $this->levelName,
            'clients' => $this->clients,
            'lines' => array_map(fn (PricingLineResult $line) => $line->toArray(), $this->lines),
            'taxable_subtotal' => $this->taxableSubtotal->toString(),
            'non_taxable_subtotal' => $this->nonTaxableSubtotal->toString(),
            'tax_amount' => $this->taxAmount->toString(),
            'cost_total' => $this->costTotal->toString(),
            'markup_amount' => $this->markupAmount->toString(),
            'pre_rounding_group_total' => $this->preRoundingGroupTotal->toString(),
            'pre_rounding_per_person' => $this->preRoundingPerPerson->toString(),
            'final_per_person' => $this->finalPerPerson->toString(),
            'final_group_total' => $this->finalGroupTotal->toString(),
        ];
    }
}