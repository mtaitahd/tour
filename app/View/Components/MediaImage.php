<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * <x-media-image :image="$image" /> — the brief's named reusable component for
 * responsive image output. Renders a <picture> element with a WebP source, a srcset
 * across the standard conversion sizes, lazy loading, and a sensible fallback when no
 * image is provided (rather than a broken image tag).
 *
 * Accepts a GalleryImage/Media instance directly (any model already using
 * InteractsWithMedia can pass the result of getFirstMedia('collection') straight in),
 * or null — useful for "this destination has no hero yet" without an @if wrapper at
 * every call site.
 *
 * Usage:
 *   <x-media-image :image="$destination->getFirstMedia('hero')" alt="{{ $destination->name }}" />
 *   <x-media-image :image="$tourPackage->heroImage" class="rounded shadow" sizes="(max-width: 768px) 100vw, 50vw" />
 */
class MediaImage extends Component
{
    public ?string $webpUrl;

    public ?string $fallbackUrl;

    public ?string $srcset;

    public function __construct(
        public ?Media $image = null,
        public string $alt = '',
        public string $class = '',
        public string $sizes = '(max-width: 768px) 100vw, 800px',
        public string $loading = 'lazy',
        public ?string $placeholder = null,
    ) {
        if (! $this->image) {
            $this->webpUrl = null;
            $this->fallbackUrl = $this->placeholder;
            $this->srcset = null;
            return;
        }

        $this->fallbackUrl = $this->image->getUrl('medium-webp') ?: $this->image->getUrl();
        $this->webpUrl = $this->image->getUrl('medium-webp') ?: null;

        // Build a srcset across the standard conversion sizes (see
        // HasStandardMediaConversions), skipping any that weren't actually generated
        // for this particular image — older media backfilled in Phase 4 should already
        // have the full set, but this stays defensive rather than assuming.
        $sizeMap = [
            'thumb-webp' => 400,
            'medium-webp' => 800,
            'large-webp' => 1200,
        ];

        $srcsetParts = [];
        foreach ($sizeMap as $conversion => $width) {
            if ($this->image->hasGeneratedConversion($conversion)) {
                $srcsetParts[] = $this->image->getUrl($conversion) . " {$width}w";
            }
        }

        $this->srcset = ! empty($srcsetParts) ? implode(', ', $srcsetParts) : null;
    }

    public function render(): View
    {
        return view('components.media-image');
    }
}
