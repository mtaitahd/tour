<?php

namespace App\Models;

use App\Services\MediaLibraryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'hero_image_id',
        'order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
    ];

    // Auto-generate slug from name when creating/updating
    protected static function booted()
    {
        static::creating(function ($activity) {
            $activity->slug = Str::slug($activity->name);
        });

        static::updating(function ($activity) {
            if ($activity->isDirty('name')) {
                $activity->slug = Str::slug($activity->name);
            }
        });
    }

    // Relationship: many-to-many with tours
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'activity_tour_package')
                    ->withTimestamps();
    }

    // Image, selected from the shared Media Library. Replaces the old plain
    // 'image' string column, which was never actually backed by a schema column or
    // an upload path anywhere — no legacy data to fall back to, so this is
    // picker-only from day one, same as Accommodation/Testimonial.
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'hero_image_id');
    }

    public function heroUrl(string $conversion = ''): ?string
    {
        if (! $this->heroImage) {
            return null;
        }

        return $this->heroImage->getUrl($conversion) ?: $this->heroImage->getUrl();
    }

    public function hasHeroImage(): bool
    {
        return $this->heroImage !== null;
    }

    // Scope: only active activities
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: featured activities
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helper: get tour count
    public function getTourCountAttribute()
    {
        return $this->tours()->where('status', 'published')->count();
    }
}
