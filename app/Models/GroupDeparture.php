<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDeparture extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_package_id', 'departure_date', 'return_date',
        'available_spots', 'total_spots', 'group_price', 'currency',
        'status', 'is_featured', 'notes',
    ];

    protected $casts = [
        'departure_date' => 'datetime',    // or 'date' if you don't need time
        'return_date'   => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    public function tour()
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function isSoldOut()
    {
        return $this->available_spots <= 0 || $this->status === 'sold_out';
    }

    public function spotsLeft()
    {
        return max(0, $this->available_spots);
    }
}