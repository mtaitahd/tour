<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingItem extends Model
{
    use HasFactory;

    protected $table = 'pricing_items';

    protected $fillable = [
        'price_calculation_id',
        'predefined_key',
        'name',
        'currency',
        'charging_basis',
        'quantity',
        'taxable',
        'included',
        'shared_across_levels',
        'level_key',
        'nights',
        'room_occupancy',
        'vehicle_capacity',
        'notes',
        'position',
    ];

    protected $casts = [
        'taxable' => 'boolean',
        'included' => 'boolean',
        'shared_across_levels' => 'boolean',
    ];

    public function priceCalculation(): BelongsTo
    {
        return $this->belongsTo(PriceCalculation::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PricingItemRate::class);
    }
}