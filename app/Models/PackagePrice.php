<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePrice extends Model
{
    use HasFactory;

    protected $table = 'package_prices';

    protected $fillable = [
        'tour_package_id',
        'package_category',
        'level_key',
        'level_name',
        'season_code',
        'price_2p',
        'price_4p',
        'price_6p',
        'group_total_2p',
        'group_total_4p',
        'group_total_6p',
        'currency',
        'price_calculation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price_2p' => 'decimal:2',
        'price_4p' => 'decimal:2',
        'price_6p' => 'decimal:2',
        'group_total_2p' => 'decimal:2',
        'group_total_4p' => 'decimal:2',
        'group_total_6p' => 'decimal:2',
    ];

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function priceCalculation(): BelongsTo
    {
        return $this->belongsTo(PriceCalculation::class, 'price_calculation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}