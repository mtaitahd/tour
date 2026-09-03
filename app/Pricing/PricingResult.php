<?php

namespace App\Pricing;

/**
 * The structured result of one engine run, keyed by season, level and group
 * size. Sensible for the future 12-cell grid (2 seasons x up to 3 levels),
 * with 2/4/6-client group sizes per cell.
 */
final class PricingResult
{
    /** @var array<string, array<string, array<int, PricingGroupResult>>> */
    private array $groups = [];

    /** @param PricingInput $input the validated input that produced this run */
    public function __construct(
        private readonly PricingInput $input,
        private readonly LevelCatalog $levels,
    ) {
    }

    public function addGroup(PricingGroupResult $group): void
    {
        $this->groups[$group->season->value][$group->levelKey][$group->clients] = $group;
    }

    public function group(string $season, string $levelKey, int $clients): PricingGroupResult
    {
        return $this->groups[strtoupper($season)][$levelKey][$clients];
    }

    public function hasGroup(string $season, string $levelKey, int $clients): bool
    {
        return isset($this->groups[strtoupper($season)][$levelKey][$clients]);
    }

    /** @return PricingGroupResult[] */
    public function allGroups(): array
    {
        $flat = [];
        foreach (PriceEngine::SEASONS as $season) {
            foreach ($this->groups[$season->value] ?? [] as $levelGroups) {
                foreach ($levelGroups as $group) {
                    $flat[] = $group;
                }
            }
        }

        return $flat;
    }

    /** @return array{meta: array<string, mixed>, groups: array<string, mixed>} */
    public function toArray(): array
    {
        $meta = [
            'duration_type' => $this->input->durationType->value,
            'tour_type' => $this->input->tourType,
            'package_category' => $this->input->packageCategory,
            'tax_enabled' => $this->input->taxEnabled,
            'tax_percentage' => $this->input->taxPercentage,
            'markup_type' => $this->input->markupType->value,
            'markup_value' => $this->input->markupValue,
            'rounding_rule' => $this->input->roundingRule->value,
            'days' => $this->input->days,
            'nights' => $this->input->nights,
        ];

        $groups = [];
        foreach ($this->groups as $season => $levelGroups) {
            foreach ($levelGroups as $levelKey => $sizes) {
                foreach ($sizes as $clients => $group) {
                    $groups[$season][$levelKey][(string) $clients] = $group->toArray();
                }
            }
        }

        return [
            'meta' => $meta,
            'groups' => $groups,
        ];
    }
}