<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_package_id',
        'type',
        'name',
        'email',
        'phone',
        'message',
        'preferred_start_date',
        'preferred_end_date',
        'total_amount',
        'adults',
        'children',
        'status',
        'admin_notes',
        // Phase 3: server-generated price snapshot frozen at submission time.
        'quote_snapshot',
        // Booking-specific fields — only populated for type = 'tour_booking',
        // matching the "Request a Quote" form on the tour details page.
        'companions',
        'accommodation',
        'room_type',
        'bed_type',
        'budget_min',
        'budget_max',
        'adult_age_range',
        'children_age_range',
        'country',
    ];

    protected $casts = [
        'preferred_start_date' => 'datetime',    // or 'date' if you don't need time
        'preferred_end_date'   => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'budget_min'           => 'decimal:2',
        'budget_max'           => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'quote_snapshot'       => 'array',
    ];

    public function tour()
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function scopeContactOnly($query)
    {
        return $query->where('type', 'contact');
    }

    public function scopeTourBookingsOnly($query)
    {
        return $query->where('type', 'tour_booking');
    }

    public function isTourBooking(): bool
    {
        return $this->type === 'tour_booking';
    }

    /**
     * Human-readable label for admin listing/detail views.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->isTourBooking() ? 'Tour Booking Request' : 'General Contact';
    }
}
