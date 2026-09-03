<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingItemRate extends Model
{
    use HasFactory;

    protected $table = 'pricing_item_rates';

    protected $fillable = [
        'pricing_item_id',
        'season_code',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function pricingItem(): BelongsTo
    {
        return $this->belongsTo(PricingItem::class);
    }
}