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
        $this->addMediaConversion('thumb')
             ->width(368)
             ->height(232)
             ->sharpen(10);

        $this->addMediaConversion('medium')
             ->width(800)
             ->height(600);

        $this->addMediaConversion('thumb-webp')
             ->width(368)
             ->height(232)
             ->sharpen(10)
             ->format('webp')
             ->quality(80);

        $this->addMediaConversion('medium-webp')
             ->width(800)
             ->height(600)
             ->format('webp')
             ->quality(80);

        $this->addMediaConversion('large-webp')
             ->width(1200)
             ->format('webp')
             ->quality(82);
    }
}
