<?php

namespace App\Services;

use App\Models\PackagePrice;
use App\Models\TourPackage;
use App\Pricing\LevelCatalog;
use Illuminate\Support\Collection;

/**
 * Centralised server-side price resolver for the public site (Phase 3).
 *
 * Single source of truth for every "From $X" card price and the customer-facing
 * price lookup. Never trusts a browser-supplied number: the inquiry snapshot and
 * the /tours/{tour}/price-lookup endpoint both derive amounts here from the
 * configured `package_prices` rows (manual/calculator) or the preserved legacy
 * `season_pricing` tiers.
 *
 * Lookup outcome kinds:
 *   - 'automatic'       the exact combination (tour + package level + season +
 *                       group size 2/4/6) has a configured `package_prices`
 *                       row — per-person and exact group total.
 *   - 'custom'          any group size outside 2/4/6, a missing season/level
 *                       combination — the visitor asks for a custom price.
 *   - 'package_request' the tour has no configured price at all.
 *
 * Legacy `season_pricing` tiers (SILVER/GOLD/PLATINUM...) are product tiers,
 * NOT date-based season rates. They are display-only: the minimum positive
 * tier price feeds the "From" label, but an exact dated quote is never
 * automatic for a legacy tour — automation requires the exact new-system
 * tour + level + season + group-size row.
 */
class TourPriceResolver
{
    public const AUTO_SIZES = [2, 4, 6];

    public const SEASON_LABELS = [
        TourSeason::HIGH    => 'High Season',
        TourSeason::LOW_WET => 'Low Wet Season',
    ];

    /** @var \App\Pricing\LevelCatalog|null */
    private static ?LevelCatalog $catalog = null;

    private function catalog(): LevelCatalog
    {
        return self::$catalog ??= LevelCatalog::fromConfig(config('tour.level_catalog'));
    }

    /**
     * Centralised "From" price for a tour, used by every public card.
     *
     * Precedence: configured package_prices rows first (new-system), then the
     * preserved legacy season_pricing tiers, then the plain base_price.
     *
     * @return array{amount: float, currency: string, source: string}|null null when the tour has no price anywhere
     */
    public static function fromPrice(TourPackage $tour, ?string $newMin = null): ?array
    {
        $currency = strtoupper((string) ($tour->currency ?: 'USD'));

        if ($newMin === null) {
            $rows = $tour->packagePrices ?? null;
            if ($rows instanceof Collection && $rows->isNotEmpty()) {
                $newMin = $rows->where('price_2p', '>', 0)->min('price_2p');
            } elseif (in_array($tour->pricing_source, ['manual', 'calculator'], true)) {
                $newMin = PackagePrice::where('tour_package_id', $tour->id)
                    ->where('price_2p', '>', 0)
                    ->min('price_2p');
            }
        }

        if ($newMin !== null && (float) $newMin > 0) {
            return ['amount' => (float) $newMin, 'currency' => $currency, 'source' => 'new'];
        }

        $legacyMin = collect($tour->season_pricing ?? [])
            ->pluck('price_2p')
            ->filter(fn ($p) => (float) $p > 0)
            ->map(fn ($p) => (float) $p)
            ->min();

        if ($legacyMin !== null) {
            return ['amount' => (float) $legacyMin, 'currency' => $currency, 'source' => 'legacy'];
        }

        if ((float) ($tour->base_price ?? 0) > 0) {
            return ['amount' => (float) $tour->base_price, 'currency' => $currency, 'source' => 'base'];
        }

        return null;
    }

    /**
     * "From" prices for a whole collection with a single aggregate query.
     *
     * @param iterable<TourPackage> $tours
     * @return array<int, array{amount: float, currency: string, source: string}|null> keyed by tour id
     */
    public static function fromPriceMap(iterable $tours): array
    {
        $tours = collect($tours);
        $ids = $tours->pluck('id')->filter()->all();
        $result = [];

        $mins = [];
        if ($ids !== []) {
            $mins = PackagePrice::query()
                ->whereIn('tour_package_id', $ids)
                ->where('price_2p', '>', 0)
                ->selectRaw('tour_package_id, MIN(price_2p) AS min_price')
                ->groupBy('tour_package_id')
                ->pluck('min_price', 'tour_package_id')
                ->map(fn ($v) => (string) $v)
                ->all();
        }

        foreach ($tours as $tour) {
            $result[$tour->id] = static::fromPrice($tour, $mins[$tour->id] ?? null);
        }

        return $result;
    }

    /**
     * Server-side price lookup for a travel date, group size and optional level.
     *
     * @param array{date?: string|null, group_size?: int, level_key?: string|null} $input
     * @return array<string, mixed>
     */
    public function lookup(TourPackage $tour, array $input = []): array
    {
        $season = TourSeason::resolve($input['date'] ?? null);
        $size = max(1, (int) ($input['group_size'] ?? 1));
        $requestedLevel = isset($input['level_key']) && $input['level_key'] !== '' ? (string) $input['level_key'] : null;

        $rows = $tour->packagePrices ?? collect();
        $newRows = $rows->filter(fn (PackagePrice $r) => (float) $r->price_2p > 0);
        $legacyRows = collect($tour->season_pricing ?? [])->filter(fn ($tier) => (float) ($tier['price_2p'] ?? 0) > 0);

        $currency = strtoupper((string) ($tour->currency ?: 'USD'));

        // ── 1. Nothing configured → request a package price ──────────────────
        if ($newRows->isEmpty() && $legacyRows->isEmpty()) {
            return [
                'tour_id'          => $tour->id,
                'tour_title'       => $tour->cardTitle(),
                'request_type'     => 'package_request',
                'source'           => 'none',
                'date'             => $input['date'] ?? null,
                'season'           => $season,
                'season_label'     => self::SEASON_LABELS[$season],
                'level_key'        => null,
                'level_name'       => null,
                'package_price_id' => null,
                'package_category' => $tour->package_category,
                'group_size'       => $size,
                'price_pp'         => null,
                'group_total'      => null,
                'currency'         => $currency,
                'note'             => "This tour doesn't list a package price yet. Request a Package Price and we'll prepare a tailored quote.",
            ];
        }

        // ── 2. New-system tiers (manual / calculator rows) ────────────────────
        if ($newRows->isNotEmpty()) {
            $seasonRows = $newRows
                ->where('season_code', $season)
                ->values();

            if (! in_array($size, self::AUTO_SIZES, true)) {
                return $this->customResult($tour, $input, 'new', $season, $size, $currency,
                    "Automatic prices are available for groups of 2, 4 or 6 travelers. Request a Custom Price for your group of {$size}.");
            }

            if ($seasonRows->isEmpty()) {
                return $this->customResult($tour, $input, 'new', $season, $size, $currency,
                    "No {$this->seasonName($season)} prices are configured for this tour yet. Request a Custom Price.");
            }

            $row = null;
            if ($requestedLevel !== null) {
                $row = $seasonRows->firstWhere('level_key', $requestedLevel);
                if ($row === null) {
                    $catalogName = $this->catalog()->levelName($requestedLevel);
                    return $this->customResult($tour, $input, 'new', $season, $size, $currency,
                        $catalogName
                            ? "The {$catalogName} price for {$this->seasonName($season)} isn't listed. Request a Custom Price for that combination."
                            : "That price combination isn't listed. Request a Custom Price.");
                }
            } else {
                $row = $seasonRows->sortBy('price_2p')->first();
            }

            $priceKey = 'price_' . $size . 'p';
            $totalKey = 'group_total_' . $size . 'p';

            return [
                'tour_id'          => $tour->id,
                'tour_title'       => $tour->cardTitle(),
                'request_type'     => 'automatic',
                'source'           => 'new',
                'date'             => $input['date'] ?? null,
                'season'           => $season,
                'season_label'     => self::SEASON_LABELS[$season],
                'level_key'        => $row->level_key,
                'level_name'       => (string) $row->level_name,
                'package_price_id' => $row->id,
                'package_category' => $row->package_category ?: $tour->package_category,
                'group_size'       => $size,
                'price_pp'         => static::money($row->{$priceKey}),
                'group_total'      => static::money($row->{$totalKey}),
                'currency'         => strtoupper((string) ($row->currency ?: $currency)),
                'note'             => null,
            ];
        }

        // ── 3. Legacy tiers (SILVER/GOLD/PLATINUM) — display-only. Their values
        //         carry no date/season relationship, so a dated quote can never be
        //         automatic here. The visitor must confirm the price for the date.
        return $this->customResult($tour, $input, 'legacy', $season, $size, $currency,
            "This tour's legacy rates don't change by travel date. Request a Custom Price to confirm the exact price for your travel date.");
    }

    /**
     * Server-authored snapshot for an inquiry. The exact resolver outcome is
     * frozen at submission time together with the traveler split, the package
     * row (when automatic) and the quote timestamp. The inquiry's total_amount
     * must be copied from here — never from the browser.
     *
     * @param array{date?: string|null, group_size?: int, level_key?: string|null} $input
     * @return array<string, mixed>
     */
    public function snapshot(TourPackage $tour, array $input = [], ?int $adults = null, ?int $children = null): array
    {
        $snapshot = $this->lookup($tour, $input);

        if ($adults !== null && $children !== null) {
            $snapshot['adults'] = (int) $adults;
            $snapshot['children'] = (int) $children;
            $snapshot['group_size'] = (int) $adults + (int) $children;
        } else {
            $snapshot['adults'] = $snapshot['group_size'];
            $snapshot['children'] = 0;
        }

        $snapshot['total_travelers'] = $snapshot['adults'] + $snapshot['children'];
        $snapshot['quoted_at'] = now()->toIso8601String();

        if ($snapshot['request_type'] !== 'automatic') {
            $snapshot['reason'] = $snapshot['note'];
        }

        return $snapshot;
    }

    /**
     * Server-rendered price matrix for the tour detail page.
     *
     * Only meaningful for tours with new-system package_prices rows; legacy and
     * no-price tours return null (they keep their own existing display).
     *
     * @return array<string, mixed>|null
     */
    public function matrixFor(TourPackage $tour): ?array
    {
        $rows = ($tour->packagePrices ?? collect())
            ->filter(fn (PackagePrice $r) => (float) $r->price_2p > 0)
            ->values();

        if ($rows->isEmpty()) {
            return null;
        }

        if ($tour->package_duration_type === 'single_day') {
            $levels = array_map(
                static fn ($key, $name) => ['key' => $key, 'name' => $name],
                array_keys($this->catalog()->singleDayLevel()),
                $this->catalog()->singleDayLevel()
            );
        } else {
            $catalogLevels = $this->catalog()->levelsFor((string) $tour->package_category);
            $levels = array_map(
                static fn ($key, $name) => ['key' => $key, 'name' => $name],
                array_keys($catalogLevels),
                $catalogLevels
            );
            if ($levels === []) {
                $present = $rows->pluck('level_key')->unique()->values();
                $levels = $present->map(function ($key) {
                    return ['key' => $key, 'name' => $this->catalog()->levelName($key) ?? (string) $key];
                })->all();
            }
        }

        $currency = $rows->first()->currency ?? $tour->currency ?: 'USD';
        $sizes = collect(self::AUTO_SIZES);
        $cells = [];

        foreach ([TourSeason::HIGH, TourSeason::LOW_WET] as $season) {
            $cells[$season] = [];
            foreach ($rows->where('season_code', $season) as $row) {
                $pp = [];
                $total = [];
                foreach ($sizes as $size) {
                    $pp[(string) $size] = static::money($row->{'price_' . $size . 'p'});
                    $total[(string) $size] = static::money($row->{'group_total_' . $size . 'p'});
                }
                $cells[$season][$row->level_key] = ['pp' => $pp, 'total' => $total];
            }
        }

        return [
            'duration' => (string) $tour->package_duration_type,
            'currency' => strtoupper((string) $currency),
            'levels'   => $levels,
            'seasons'  => self::SEASON_LABELS,
            'sizes'    => self::AUTO_SIZES,
            'cells'    => $cells,
        ];
    }

    /**
     * Default (cheapest) level key across a tour's configured prices.
     */
    public function defaultLevelKey(TourPackage $tour): ?string
    {
        $rows = ($tour->packagePrices ?? collect())
            ->filter(fn (PackagePrice $r) => (float) $r->price_2p > 0);

        if ($rows->isEmpty()) {
            return null;
        }

        return $rows->sortBy('price_2p')->first()->level_key;
    }

    private function customResult(TourPackage $tour, array $input, string $source, string $season, int $size, string $currency, string $note): array
    {
        return [
            'tour_id'          => $tour->id,
            'tour_title'       => $tour->cardTitle(),
            'request_type'     => 'custom',
            'source'           => $source,
            'date'             => $input['date'] ?? null,
            'season'           => $season,
            'season_label'     => self::SEASON_LABELS[$season],
            'level_key'        => null,
            'level_name'       => null,
            'package_price_id' => null,
            'package_category' => $tour->package_category,
            'group_size'       => $size,
            'price_pp'         => null,
            'group_total'      => null,
            'currency'         => $currency,
            'note'             => $note,
        ];
    }

    private function seasonName(string $season): string
    {
        return strtolower(self::SEASON_LABELS[$season] ?? $season);
    }

    private static function money(mixed $value): ?string
    {
        return $value !== null && $value !== '' && (float) $value >= 0
            ? number_format((float) $value, 2, '.', '')
            : null;
    }
}