<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds price-calculator default settings IF MISSING. Never overwrites an
 * existing value. Rollback only removes rows that still exactly match the
 * defaults this migration would have inserted (user-modified values survive).
 */
return new class extends Migration
{
    private const DEFAULTS = [
        [
            'key'         => 'pricing_default_tax_percentage',
            'value'       => '18',
            'type'        => 'text',
            'group'       => 'pricing',
            'label'       => 'Default Government Tax Percentage (%)',
            'description' => 'Default tax percentage applied by the price calculator to taxable items.',
        ],
        [
            'key'         => 'pricing_default_markup_type',
            'value'       => 'percent',
            'type'        => 'select',
            'group'       => 'pricing',
            'label'       => 'Default Markup Type',
            'description' => 'Default markup type used by the price calculator (percent or fixed).',
        ],
        [
            'key'         => 'pricing_default_markup_value',
            'value'       => '0',
            'type'        => 'text',
            'group'       => 'pricing',
            'label'       => 'Default Markup Value',
            'description' => 'Default markup amount (percentage or fixed) used by the price calculator.',
        ],
        [
            'key'         => 'pricing_default_rounding',
            'value'       => 'none',
            'type'        => 'select',
            'group'       => 'pricing',
            'label'       => 'Default Per-Person Price Rounding',
            'description' => 'Default rounding applied to per-person prices (none, 1, 5, 10, 50 or 100).',
        ],
    ];

    public function up(): void
    {
        $now = now();
        foreach (self::DEFAULTS as $default) {
            if (DB::table('settings')->where('key', $default['key'])->exists()) {
                continue;
            }

            DB::table('settings')->insert($default + ['created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        foreach (self::DEFAULTS as $default) {
            DB::table('settings')
                ->where('key', $default['key'])
                ->where('value', $default['value'])
                ->where('group', 'pricing')
                ->delete();
        }
    }
};