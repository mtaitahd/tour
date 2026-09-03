<?php

namespace App\Models;

use App\Services\MediaLibraryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'tier',
        'destination_id', 'location',
        'description', 'amenities',
        'price_from', 'currency',
        'hero_image_id',
        'is_featured', 'order', 'status',
    ];

    protected $casts = [
        'amenities'   => 'array',
        'is_featured' => 'boolean',
        'price_from'  => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // Hero image, selected from the shared Media Library. No legacy fallback here
    // (unlike Destination::heroUrl()) — this model never had direct uploads, so
    // hero_image_id is the only source of truth from day one.
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

    /**
     * Single centralized resolver for accommodation card images — mirrors
     * TourPackage::cardImageUrl() so every card template resolves its image
     * and fallback through one place.
     */
    public function cardImageUrl(string $conversion = 'medium'): string
    {
        if ($this->hasHeroImage()) {
            return (string) $this->heroUrl($conversion);
        }

        return asset('public/assets/images/safari-hero.jpg');
    }

    /**
     * Gallery images, in chosen display order — tracked entirely via media_usages
     * (context 'gallery'), same read path as Destination::galleryImages() minus the
     * legacy Spatie fallback, since there's nothing to fall back to here.
     *
     * @return Collection<int, GalleryImage>
     */
    public function galleryImages(): Collection
    {
        return app(MediaLibraryService::class)->orderedImagesFor($this, 'gallery');
    }

    public function hasGalleryImages(): bool
    {
        return $this->galleryImages()->isNotEmpty();
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
}
