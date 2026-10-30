<?php

namespace App\Models\Concerns;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Consolidates the media conversion definitions that were previously copy-pasted,
 * near-identically, into Destination, TourPackage, and Page (and missing entirely
 * from BlogPost — see Phase 1 analysis report). Every model that uses this trait
 * gets the same consistent thumb / medium / WebP set.
 *
 * Existing conversion names (thumb, medium, thumb-webp, medium-webp, large-webp) are
 * preserved exactly as they were, so no Blade view or URL call needs to change and no
 * already-generated files become orphaned.
 */
trait HasStandardMediaConversions
{
    public function registerMediaConversions(?Media $media = null): void
    {
        // Glide auto-orients every conversion by reading EXIF. Shared hosts may
        // not provide that PHP extension; keep the encoded orientation there.
        $orientation = function_exists('exif_read_data') ? 'auto' : '0';
        $this->addMediaConversion('thumb')
             ->orientation($orientation)
             ->width(368)
             ->height(232)
             ->sharpen(10);

        $this->addMediaConversion('medium')
             ->orientation($orientation)
             ->width(800)
             ->height(600);

        $this->addMediaConversion('thumb-webp')
             ->orientation($orientation)
             ->width(368)
             ->height(232)
             ->sharpen(10)
             ->format('webp')
             ->quality(80);

        $this->addMediaConversion('medium-webp')
             ->orientation($orientation)
             ->width(800)
             ->height(600)
             ->format('webp')
             ->quality(80);

        $this->addMediaConversion('large-webp')
             ->orientation($orientation)
             ->width(1200)
             ->format('webp')
             ->quality(82);
    }
}
