<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * GalleryImage extends Spatie's own Media model rather than wrapping or duplicating it.
 * Every row in the existing `media` table already IS a GalleryImage once this class is
 * registered as Spatie's media_model (see config/media-library.php) — no migration of
 * existing rows is required, and every model that already uses InteractsWithMedia
 * (Destination, TourPackage, BlogPost, Page) keeps working exactly as before.
 *
 * This class is what gives the Media Library its "library" behavior: categories, tags,
 * SEO metadata, and usage tracking, all as native Eloquent relationships on top of the
 * data Spatie already manages (file, disk, conversions, responsive images).
 */
class GalleryImage extends Media
{
    // Relationship: the admin user who uploaded this image (see the uploaded_by column).
    // Every gallery image is verifiably an admin upload — this lets the admin UI show
    // and filter by who added it.
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper: display name of whoever uploaded this image, with a gentle fallback when
    // the row predates attribution (imports / seeds).
    public function getUploadedByNameAttribute(): string
    {
        if ($this->relationLoaded('uploader') && $this->uploader) {
            return $this->uploader->name;
        }

        return $this->uploader?->name ?: 'System / legacy';
    }

    // Relationship: SEO/descriptive metadata (1:1) — title, alt text, caption, description
    public function meta(): HasOne
    {
        return $this->hasOne(MediaMeta::class, 'media_id');
    }

    // Relationship: categories this image belongs to (many-to-many, supports nesting via MediaCategory)
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(MediaCategory::class, 'media_category_media', 'media_id', 'media_category_id')
                    ->withTimestamps();
    }

    // Relationship: tags assigned to this image (many-to-many)
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_tag_media', 'media_id', 'media_tag_id')
                    ->withTimestamps();
    }

    // Relationship: every recorded usage of this image across the CMS (Destination hero,
    // TourPackage gallery, itinerary JSON references, etc.) — see MediaUsage for details.
    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'media_id');
    }

    // Helper: "Used in 5 locations" — total usage count regardless of context. Uses the
    // eager-loaded usages_count (from withCount('usages'), see MediaLibraryService::
    // query()) when available, to avoid one COUNT query per image in a grid of 24+
    // results; falls back to a live count only when this attribute wasn't pre-loaded
    // (e.g. a single GalleryImage fetched outside the library's own query builder).
    public function getUsageCountAttribute(): int
    {
        if (array_key_exists('usages_count', $this->attributes)) {
            return (int) $this->attributes['usages_count'];
        }

        return $this->usages()->count();
    }

    // Helper: whether this image is currently used anywhere — drives the
    // "prevent deletion while in use" rule in MediaLibraryService.
    public function getIsInUseAttribute(): bool
    {
        return $this->usages()->exists();
    }

    // Helper: convenience accessor so the admin UI can show "Untitled" instead of blank
    // when no SEO title has been set yet.
    public function getDisplayTitleAttribute(): string
    {
        return $this->meta?->title ?: $this->name;
    }

    /**
     * Width and height in pixels. Spatie's Media model doesn't store these as columns
     * at all (confirmed — no width/height attribute exists anywhere in the package),
     * so they're read directly from the file on disk via PHP's native getimagesize(),
     * which the brief's Detail View needs (Width, Height alongside File Size, MIME
     * Type). Cached on the instance per-request via a static property keyed by id,
     * since the Detail and Edit views both read both dimensions and there's no reason
     * to hit the filesystem twice for the same image in one request.
     */
    protected static array $dimensionCache = [];

    protected function dimensions(): array
    {
        if (! isset(self::$dimensionCache[$this->id])) {
            $path = $this->getPath();
            $size = is_file($path) ? @getimagesize($path) : false;
            self::$dimensionCache[$this->id] = $size ? ['width' => $size[0], 'height' => $size[1]] : ['width' => null, 'height' => null];
        }

        return self::$dimensionCache[$this->id];
    }

    public function getWidthAttribute(): ?int
    {
        return $this->dimensions()['width'];
    }

    public function getHeightAttribute(): ?int
    {
        return $this->dimensions()['height'];
    }
}
