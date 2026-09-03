<?php

namespace App\Models;

use App\Models\Concerns\AutoCurrentYearTitle;
use App\Models\Concerns\HasStandardMediaConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogPost extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasStandardMediaConversions, AutoCurrentYearTitle {
        HasStandardMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    // NOTE: this table also has a legacy 'featured_image' string column from the original
    // schema (see migrations). It is NOT used by this model — images are stored via the
    // 'featured_image' Spatie collection below, confirmed against production data (every
    // BlogPost media row has collection_name = 'featured_image'). It's deliberately left
    // out of $fillable and out of this model entirely, and is a candidate for removal in
    // a future cleanup migration once confirmed safe to drop.
    protected $fillable = [
        'title', 'slug', 'content', 'category_id', 'status', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords', 'is_featured', 'order',
        'featured_image_id', 'no_robots',
    ];

    // Replace $dates with this
    protected $casts = [
        'published_at' => 'datetime',   // auto-converts to Carbon
        'no_robots'    => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');  // ← Change from 'blog_category_id'
    }

    // Add this relationship
    public function translations()
    {
        return $this->hasMany(BlogPostTranslation::class, 'post_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

    // Note: BlogPost previously had no registerMediaConversions() at all, so its 251
    // existing featured images have no thumb/medium/WebP variants. Using the shared
    // HasStandardMediaConversions trait fixes that going forward, consistent with the
    // other three modules. Existing images are covered by the Phase 2/4 backfill command.

    // Relationship: featured image selected from the shared Media Library. Additive —
    // existing posts keep working via getFirstMedia('featured_image') until migrated
    // to the new picker. See Destination::heroImage() for the same pattern.
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'featured_image_id');
    }

    /**
     * Featured image URL shortcut, mirroring TourPackage::getHeroUrlAttribute(). Prefers
     * the new Media Library FK when set, falls back to the original Spatie collection.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featuredImageUrl('medium-webp');
    }

    /**
     * Same fallback logic as getFeaturedImageUrlAttribute(), but callable with any
     * conversion name — matches the ->heroUrl($conversion) pattern on the other three
     * models, so every public template can call ->featuredImageUrl($conversion)
     * instead of getFirstMediaUrl('featured_image', $conversion), which bypasses the
     * Media Library selection entirely.
     */
    public function featuredImageUrl(string $conversion = ''): ?string
    {
        if ($this->featuredImage) {
            return $this->featuredImage->getUrl($conversion) ?: $this->featuredImage->getUrl();
        }

        return $this->getFirstMediaUrl('featured_image', $conversion) ?: $this->getFirstMediaUrl('featured_image') ?: null;
    }

    public function hasFeaturedImage(): bool
    {
        return $this->featuredImage !== null || $this->getFirstMedia('featured_image') !== null;
    }

    /**
     * Single centralized resolver for blog card images — mirrors
     * TourPackage::cardImageUrl() so every card template resolves its image
     * and fallback through one place.
     */
    public function cardImageUrl(string $conversion = 'medium'): string
    {
        if ($this->hasFeaturedImage()) {
            return (string) $this->featuredImageUrl($conversion);
        }

        return asset('public/assets/images/kilimanjaro-hero-2.jpg');
    }

    /**
     * Short display name for cards/meta tags — mirrors TourPackage::cardTitle().
     */
    public function cardTitle(): string
    {
        return trim((string) ($this->meta_title ?: $this->title));
    }
}