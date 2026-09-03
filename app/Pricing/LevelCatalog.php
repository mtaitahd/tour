<?php

namespace App\Pricing;

/**
 * Canonical, non-monetary package-level hierarchy. It never contains rates —
 * monetary values live only in pricing data. Direct multi-day categories map
 * to exactly three levels; the SINGLE_DAY entry defines the one level used for
 * single-day tours.
 *
 * Build from config/tour.php via LevelCatalog::fromConfig(
 *     config('tour.level_catalog')
 * ) or directly from the raw array so the engine itself stays framework-free.
 */
final class LevelCatalog
{
    /** @var array<string, array{label: string, levels: array<string, string>}> */
    private array $categories;

    /**
     * @param array<string, array{label?: string, levels: array<string, string>}> $categories
     */
    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    public static function fromConfig(array $config): self
    {
        return new self($config);
    }

    /** @return string[] the category keys */
    public function categories(): array
    {
        return array_keys($this->categories);
    }

    /**
     * Category keys that a multi-day package can actually belong to (i.e. the
     * catalog excluding SINGLE_DAY, which is not a selectable package category).
     *
     * @return string[]
     */
    public function multiDayCategories(): array
    {
        return array_values(array_filter(
            $this->categories(),
            fn (string $key) => $key !== 'SINGLE_DAY'
        ));
    }

    /** @return array<string, string> level key => display name */
    public function levelsFor(string $categoryKey): array
    {
        return $this->categories[$categoryKey]['levels'] ?? [];
    }

    public function hasLevel(string $levelKey): bool
    {
        foreach ($this->categories as $category) {
            if (array_key_exists($levelKey, $category['levels'])) {
                return true;
            }
        }

        return false;
    }

    public function levelName(string $levelKey): ?string
    {
        foreach ($this->categories as $category) {
            if (array_key_exists($levelKey, $category['levels'])) {
                return $category['levels'][$levelKey];
            }
        }

        return null;
    }

    public function levelBelongsTo(string $categoryKey, string $levelKey): bool
    {
        return array_key_exists($levelKey, $this->levelsFor($categoryKey));
    }

    /** @return array<string, string> single-day level key => display name */
    public function singleDayLevel(): array
    {
        $levels = $this->categories['SINGLE_DAY']['levels'] ?? [];

        if (count($levels) !== 1) {
            throw new PricingValidationException('Level catalog must define exactly one SINGLE_DAY level.');
        }

        return [$key = array_key_first($levels) => $levels[$key]];
    }
}