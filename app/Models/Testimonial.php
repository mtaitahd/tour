<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location', 'rating', 'content',
        'tour_package_id', 'destination_id',
        'avatar_image_id',
        'is_featured', 'order', 'status',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_featured' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // Avatar/client photo, selected from the shared Media Library. No legacy
    // fallback — same reasoning as Accommodation::heroImage().
    public function avatarImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'avatar_image_id');
    }

    public function avatarUrl(string $conversion = ''): ?string
    {
        if (! $this->avatarImage) {
            return null;
        }

        return $this->avatarImage->getUrl($conversion) ?: $this->avatarImage->getUrl();
    }

    public function hasAvatar(): bool
    {
        return $this->avatarImage !== null;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * General-purpose testimonials only — not scoped to any specific tour or
     * destination. Used by the homepage's testimonials section.
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('tour_package_id')->whereNull('destination_id');
    }

    /**
     * Testimonials for a specific tour, for use on that tour's page — this is
     * what would eventually replace the "Reviews (Coming Soon)" placeholder there.
     */
    public function scopeForTour($query, $tourPackageId)
    {
        return $query->where('tour_package_id', $tourPackageId);
    }
}
