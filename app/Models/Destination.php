<?php

namespace App\Models;

use App\Models\Concerns\HasStandardMediaConversions;
use App\Services\MediaLibraryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Destination extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasStandardMediaConversions {
        HasStandardMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'name', 'slug', 'country_code', 'type',
        'latitude', 'longitude', 'description',
        'faqs',             // JSON: [{question, answer}, ...]
        'reviews_embed',    // Admin-pasted review link or embed snippet
        'meta_title', 'meta_description', 'meta_keywords',
        'is_featured', 'order',
        'hero_image_id',
    ];

    protected $casts = [
        'faqs' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')
             ->singleFile()                             // only one hero image
             ->useFallbackUrl(asset('assets/img/placeholder-destination.jpg'));

        $this->addMediaCollection('gallery')
             ->useFallbackUrl(asset('assets/img/placeholder.jpg'));
    }

    public function tours()
    {
        return $this->belongsToMany(TourPackage::class, 'tour_destinations')
                    ->withPivot('order', 'is_highlight');
    }

    // Relationship: hero image selected from the shared Media Library (new picker-driven
    // path). This is additive — existing destinations that still have their hero attached
    // via the 'hero' Spatie collection keep working through getFirstMedia('hero') exactly
    // as before. Once a destination's hero is (re)selected through the new Media Picker,
    // this FK is populated and can be preferred over the collection lookup.
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'hero_image_id');
    }

    /**
     * Hero image URL, preferring the Media Library selection (hero_image_id) when set,
     * falling back to the original Spatie 'hero' collection otherwise. Every public
     * template should call this method instead of getFirstMediaUrl('hero') directly —
     * calling getFirstMediaUrl('hero') bypasses the Media Library selection entirely,
     * which is the exact bug this method exists to fix (see conversation history: every
     * frontend template was calling getFirstMediaUrl('hero') directly, so picking a new
     * hero via the picker saved hero_image_id correctly but never actually displayed,
     * since nothing was reading that field).
     *
     * @param string $conversion Defaults to no conversion (original size). Pass 'thumb',
     *                            'medium', etc. to match what getFirstMediaUrl($collection,
     *                            $conversion) would have returned.
     */
    public function heroUrl(string $conversion = ''): ?string
    {
        if ($this->heroImage) {
            return $this->heroImage->getUrl($conversion) ?: $this->heroImage->getUrl();
        }

        return $this->getFirstMediaUrl('hero', $conversion) ?: null;
    }

    /**
     * Whether this destination has a hero image at all, via either path — lets templates
     * keep their existing @if(...) guard structure without calling two methods.
     */
    public function hasHeroImage(): bool
    {
        return $this->heroImage !== null || $this->getFirstMedia('hero') !== null;
    }

    /**
     * Gallery images, preferring the Media Library picker's ordered selection over the
     * legacy Spatie 'gallery' collection. Every public template must call this instead
     * of getMedia('gallery') directly — calling getMedia('gallery') bypasses the
     * picker-based gallery entirely, which is the same class of bug already fixed for
     * hero/featured/story images (see heroUrl() above): the gallery picker correctly
     * saves selections into media_usages, but a template reading getMedia('gallery')
     * never sees them, so picking new gallery images appeared to do nothing on the
     * public site even though the admin side saved correctly.
     *
     * Returns a Collection of GalleryImage instances in the chosen display order. Each
     * one already behaves like a Spatie Media instance (->getUrl(), conversions, etc.)
     * since GalleryImage extends Media directly (see Phase 3) — so existing template
     * code that calls ->getUrl('thumb') etc. on each item keeps working unchanged,
     * only the collection-fetching call itself needs to change.
     */
    public function galleryImages(): Collection
    {
        $images = app(MediaLibraryService::class)->orderedImagesFor($this, 'gallery');

        if ($images->isNotEmpty()) {
            return $images;
        }

        // Fallback for any destination whose gallery hasn't been migrated/touched via
        // the picker yet — see Phase migration command media:migrate-galleries, which
        // should normally have already covered this, but the fallback costs nothing
        // and avoids a destination's gallery appearing empty if that command was
        // somehow never run against it.
        return $this->getMedia('gallery')->sortBy('order_column')->values();
    }

    /**
     * Whether this destination has any gallery images at all, via either path.
     */
    public function hasGalleryImages(): bool
    {
        return $this->galleryImages()->isNotEmpty();
    }
}