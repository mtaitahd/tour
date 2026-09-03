<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceCalculation extends Model
{
    use HasFactory;

    protected $table = 'price_calculations';

    protected $fillable = [
        'tour_package_id',
        'package_duration_type',
        'tour_type',
        'package_category',
        'tax_enabled',
        'tax_percentage',
        'markup_type',
        'markup_value',
        'rounding_rule',
        'breakdown',
        'results',
        'created_by',
    ];

    protected $casts = [
        'tax_enabled' => 'boolean',
        'breakdown' => 'array',
        'results' => 'array',
    ];

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function pricingItems(): HasMany
    {
        return $this->hasMany(PricingItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}