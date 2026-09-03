<?php

namespace App\Models;

use App\Models\Concerns\AutoCurrentYearTitle;
use App\Models\Concerns\HasStandardMediaConversions;
use App\Services\MediaLibraryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TourPackage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasStandardMediaConversions, AutoCurrentYearTitle {
        HasStandardMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        // Core
        'slug',
        'title',
        'duration_days',
        'duration_nights',
        'base_price',
        'currency',

        // New fields
        'video_url',
        'embed_map',
        'season_pricing',   // JSON: [{season,price_2p,price_4p,price_6p}, ...]
        'trip_details',
        'faqs',             // JSON: [{question, answer}, ...]

        // Ratings / Level
        'physical_rating',
        'tour_level',

        // Departure
        'departure_dates',
        'starting_point',
        'ending_point',

        // Content
        'map_data',
        'overview',
        'highlights',
        'inclusions',
        'exclusions',
        'itinerary',
        'extra_sections',

        // SEO
        'meta_title',
        'meta_description',
        'meta_keywords',

        // Admin
        'is_featured',
        'status',
        'order',
        'no_robots',

        // Phase 2 pricing metadata
        'package_duration_type',
        'tour_type',
        'package_category',
        'pricing_source',

        // Media Library
        'hero_image_id',
    ];

    protected $casts = [
        'departure_dates'  => 'array',
        'highlights'       => 'array',
        'inclusions'       => 'array',
        'exclusions'       => 'array',
        'itinerary'        => 'array',
        'map_data'         => 'array',
        'extra_sections'   => 'array',
        'season_pricing'   => 'array',  // NEW
        'trip_details'     => 'array',
        'faqs'             => 'array',  // NEW
        'is_featured'      => 'boolean',
        'no_robots'        => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function destinations()
    {
        return $this->belongsToMany(Destination::class, 'tour_destinations')
                    ->withPivot('order', 'is_highlight');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TourCategory::class, 'tour_category_tour_package')
                    ->withTimestamps();
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_tour_package')
                    ->withTimestamps();
    }

    /**
     * New-system package prices (Phase 2). Legacy SILVER/GOLD/PLATINUM pricing
     * is NOT here — it lives in the season_pricing JSON column and is preserved
     * untouched.
     */
    public function packagePrices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    /**
     * Immutable calculator snapshots, newest first. Never rewritten in place.
     */
    public function priceCalculations()
    {
        return $this->hasMany(PriceCalculation::class);
    }

    // Relationship: hero image selected from the shared Media Library. Additive — see
    // Destination::heroImage() for the same pattern and rationale.
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'hero_image_id');
    }

    // ─── Media Collections ────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero')
             ->singleFile()
             ->useDisk('public');

        $this->addMediaCollection('gallery')
             ->useDisk('public');

        $this->addMediaCollection('safari_car_images')  // NEW
             ->useDisk('public');

        $this->addMediaCollection('itinerary_images')   // NEW – per-day images
             ->useDisk('public');

        $this->addMediaCollection('extra_sections')
             ->useDisk('public');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return season pricing keyed by season name for easy lookup in views.
     * e.g. $package->pricingBySeason['GOLD']['price_4p']
     */
    public function getPricingBySeasonAttribute(): array
    {
        $indexed = [];
        foreach ($this->season_pricing ?? [] as $row) {
            $indexed[$row['season']] = $row;
        }
        return $indexed;
    }

    /**
     * Hero image URL shortcut. Prefers the new Media Library FK (hero_image_id) when set,
     * since that's the picker-driven path going forward; falls back to the original
     * Spatie 'hero' collection lookup for tours that haven't been migrated to the picker
     * yet, so nothing breaks for existing data.
     */
    public function getHeroUrlAttribute(): ?string
    {
        return $this->heroUrl('medium-webp');
    }

    /**
     * Same fallback logic as getHeroUrlAttribute(), but callable with any conversion
     * name — matches the pattern on Destination/Page/BlogPost so every public template
     * across all four models can call ->heroUrl($conversion) consistently instead of
     * getFirstMediaUrl('hero', $conversion), which bypasses the Media Library
     * selection entirely.
     */
    public function heroUrl(string $conversion = ''): ?string
    {
        if ($this->heroImage) {
            return $this->heroImage->getUrl($conversion) ?: $this->heroImage->getUrl();
        }

        return $this->getFirstMediaUrl('hero', $conversion) ?: $this->getFirstMediaUrl('hero') ?: null;
    }

    public function hasHeroImage(): bool
    {
        return $this->heroImage !== null || $this->getFirstMedia('hero') !== null;
    }

    /**
     * Single centralized resolver for tour card images. Every card template
     * should call this instead of repeating heroUrl()/asset() fallback logic,
     * so the fallback file is defined in exactly one place.
     */
    public function cardImageUrl(string $conversion = 'medium'): string
    {
        if ($this->hasHeroImage()) {
            return (string) $this->heroUrl($conversion);
        }

        return asset('public/assets/images/safari-hero.jpg');
    }

    /**
     * Short display name for cards/meta tags. The `title` column often carries
     * SEO article tails ("...: 15 Incredible Reasons to Book This Wildlife
     * Adventure"), while meta_title holds the clean short name — prefer it.
     */
    public function cardTitle(): string
    {
        return trim((string) ($this->meta_title ?: $this->title));
    }

    /**
     * Gallery images, preferring the Media Library picker's ordered selection
     * (media_usages, context 'gallery') over the legacy Spatie 'gallery' collection.
     * See Destination::galleryImages() for the full rationale — this is the exact
     * same fix, applied here too, since tours/show.blade.php was found to read
     * getMedia('gallery') directly, the same bug already fixed for hero images.
     */
    public function galleryImages(): Collection
    {
        $images = app(MediaLibraryService::class)->orderedImagesFor($this, 'gallery');

        if ($images->isNotEmpty()) {
            return $images;
        }

        return $this->getMedia('gallery')->sortBy('order_column')->values();
    }

    /**
     * Safari car images, same fallback pattern as galleryImages() — picker selection
     * (media_usages, context 'safari_car_images') preferred over the legacy Spatie
     * collection.
     */
    public function safariCarImages(): Collection
    {
        $images = app(MediaLibraryService::class)->orderedImagesFor($this, 'safari_car_images');

        if ($images->isNotEmpty()) {
            return $images;
        }

        return $this->getMedia('safari_car_images')->sortBy('order_column')->values();
    }

    /**
     * Day-level images for one itinerary day, resolved from the picker-selected
     * image_ids stored directly in the itinerary JSON column (NOT media_usages —
     * unlike gallery/safari_car_images/hero, itinerary day images and accommodation
     * tier images were never wired through MediaLibraryService::recordUsage() in the
     * picker conversion; TourPackageController reads/writes their ids straight into
     * $this->itinerary[$dayIndex]['image_ids'] and
     * $this->itinerary[$dayIndex]['accommodations'][*]['image_id'] instead — see
     * TourPackageController::store()/update()). This resolves those ids into real
     * GalleryImage instances for display, in place of the old
     * getMedia('itinerary_images') call that only ever found images attached via
     * the now-removed file upload, never the picker's selections.
     *
     * @return Collection<int, GalleryImage>
     */
    public function dayImages(int $dayIndex): Collection
    {
        $ids = $this->itinerary[$dayIndex]['image_ids'] ?? [];

        if (empty($ids)) {
            return collect();
        }

        $images = GalleryImage::query()->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(fn ($id) => $images->get($id))->filter()->values();
    }

    /**
     * The accommodation image for one tier on one itinerary day, resolved the same
     * way as dayImages() above — from accommodations[*]['image_id'] in the JSON
     * column, matched by tier_key (e.g. 'silver', 'gold', 'platinum').
     */
    public function accommodationImage(int $dayIndex, string $tierKey): ?GalleryImage
    {
        $accommodations = $this->itinerary[$dayIndex]['accommodations'] ?? [];

        foreach ($accommodations as $accommodation) {
            if (strtolower($accommodation['tier_key'] ?? '') === strtolower($tierKey) && ! empty($accommodation['image_id'])) {
                return GalleryImage::find($accommodation['image_id']);
            }
        }

        return null;
    }

    /**
     * The image for one extra section, resolved from extra_sections[*]['image_id'] —
     * same storage mechanism as itinerary images, not media_usages.
     */
    public function extraSectionImage(int $sectionIndex): ?GalleryImage
    {
        $imageId = $this->extra_sections[$sectionIndex]['image_id'] ?? null;

        return $imageId ? GalleryImage::find($imageId) : null;
    }
}