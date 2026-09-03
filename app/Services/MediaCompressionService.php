<?php

namespace App\Services;

use App\Models\GalleryImage;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * The "Compress Images" engine for the admin Media Library (kizza-tours style).
 *
 * It works on the *generated* conversion files (thumb-webp / medium-webp /
 * large-webp) that Spatie Media Library already produces for every image — it never
 * touches the uploaded original, which stays exactly as the user uploaded it. This
 * keeps the source of truth intact while giving the admin a way to shrink the
 * WebP variants that are actually served to visitors.
 *
 * Two operating modes, chosen by the admin:
 *   - quality : re-encode each WebP conversion at a fixed quality (1-100).
 *   - filesize: keep re-encoding at progressively lower quality until the output
 *               is at or below a target size in KB, or the quality floor is hit.
 */
class MediaCompressionService
{
    /** The WebP conversions this tool is allowed to compress. */
    public const COMPRESSIBLE_CONVERSIONS = ['thumb-webp', 'medium-webp', 'large-webp'];

    /** Lower boundary we never compress below, to keep images usable. */
    public const MIN_QUALITY = 25;

    /** Upper boundary for quality mode (WebP is near-lossless-value above this). */
    public const MAX_QUALITY = 95;

    /**
     * Build a category summary of every media item, grouped by its collection name
     * (gallery, hero, safari_car_images, featured_image, ...) and its owning model.
     * This is what the compression page uses to "arrange based on categories".
     *
     * @return Collection<int, array{
     *   collection: string,
     *   models: Collection<int, array{label: string, count: int, size_bytes: int}>
     * }>
     */
    public function summary(): Collection
    {
        $media = GalleryImage::query()
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
            ->with('uploader')
            ->orderBy('collection_name')
            ->get([
                'id', 'name', 'collection_name', 'model_type', 'model_id', 'size', 'mime_type', 'uploaded_by',
            ]);

        // Model type -> friendly label for the owning content type.
        $modelLabels = [
            'App\\Models\\TourPackage' => 'Tours',
            'App\\Models\\Destination' => 'Destinations',
            'App\\Models\\Page' => 'Pages',
            'App\\Models\\BlogPost' => 'Blog Posts',
            'App\\Models\\GlobalMedia' => 'Media Library',
        ];

        return $media
            ->groupBy('collection_name')
            ->map(function (Collection $items, string $collection) use ($modelLabels) {
                $byModel = $items->groupBy('model_type')->map(function (Collection $rows, string $modelType) use ($modelLabels) {
                    $attributed = $rows->filter(fn ($m) => $m->uploaded_by)->count();

                    return [
                        'label' => $modelLabels[$modelType] ?? class_basename($modelType),
                        'model_type' => $modelType,
                        'count' => $rows->count(),
                        'admin_uploaded' => $attributed,
                        'size_bytes' => (int) $rows->sum('size'),
                    ];
                })->values();

                return [
                    'collection' => $collection,
                    'models' => $byModel,
                    'total' => $items->count(),
                    'admin_uploaded' => $items->filter(fn ($m) => $m->uploaded_by)->count(),
                ];
            })
            ->values();
    }

    /**
     * The list of individual images that belong to the given collections (used to let
     * the page show what will actually be compressed when "all" is chosen).
     *
     * @param string[] $collections
     * @return Collection<int, GalleryImage>
     */
    public function imagesInCollections(array $collections): Collection
    {
        return GalleryImage::query()
            ->whereIn('collection_name', $collections)
            ->get();
    }

    /**
     * Compress a set of media items. Returns one row per item with per-file results.
     *
     * @param int[] $mediaIds
     * @param string $mode 'quality'|'filesize'
     */
    public function compress(array $mediaIds, string $mode, int $value): array
    {
        $results = [];

        foreach ($mediaIds as $mediaId) {
            $media = GalleryImage::find($mediaId);
            if (! $media) {
                continue;
            }

            $results[] = $this->compressMedia($media, $mode, $value);
        }

        return $results;
    }

    /**
     * Compress one media item's WebP conversions, returning a result row.
     *
     * @return array{
     *   media_id: int,
     *   name: string,
     *   collection: string,
     *   thumb_url: string,
     *   files: array<int, array{conversion: string, orig: int, new: int, optimal: bool, quality: int}>
     * }|null
     */
    public function compressMedia(GalleryImage $media, string $mode, int $value): array
    {
        $origTotal = 0;
        $newTotal = 0;
        $rows = [];

        foreach (self::COMPRESSIBLE_CONVERSIONS as $conversion) {
            try {
                $row = $this->compressConversion($media, $conversion, $mode, $value);
            } catch (RuntimeException $e) {
                continue; // conversion missing / not an image / not compressible — skip
            }

            $origTotal += $row['orig'];
            $newTotal += $row['new'];
            $rows[] = $row;
        }

        if (empty($rows)) {
            return null;
        }

        return [
            'media_id' => $media->id,
            'name' => $media->name,
            'collection' => $media->collection_name,
            'thumb_url' => $media->getUrl('thumb-webp') ?: $media->getUrl('thumb') ?: $media->getUrl(),
            'orig' => $origTotal,
            'new' => $newTotal,
            'files' => $rows,
        ];
    }

    /**
     * Re-encode a single conversion file. Quality mode uses the chosen quality; filesize
     * mode iterates quality downward until the file is at/below the target (or the
     * quality floor is reached). The already-existing file is overwritten in place.
     *
     * @return array{conversion: string, orig: int, new: int, optimal: bool, quality: int}
     */
    protected function compressConversion(GalleryImage $media, string $conversion, string $mode, int $value): array
    {
        $path = $media->getPath($conversion);

        if (! is_file($path)) {
            throw new RuntimeException('Conversion file not found.');
        }

        // Only re-encode actual WebP images — this tool is scoped to the WebP variants.
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'webp') {
            throw new RuntimeException('Not a WebP conversion.');
        }

        $source = @imagecreatefromwebp($path);
        if (! $source) {
            throw new RuntimeException('Unable to decode WebP.');
        }

        $floor = self::MIN_QUALITY;

        if ($mode === 'quality') {
            // Quality mode: encode exactly once at the chosen quality (clamped). No
            // down-iterating — the admin asked for this specific quality.
            $quality = max($floor, min(self::MAX_QUALITY, $value));

            ob_start();
            imagewebp($source, null, $quality);
            $blob = ob_get_clean();
            imagedestroy($source);

            $origSize = filesize($path);
            $newSize = strlen($blob);

            if ($newSize < $origSize) {
                file_put_contents($path, $blob);
                @touch($path);
                return [
                    'conversion' => $conversion,
                    'orig' => $origSize,
                    'new' => $newSize,
                    'optimal' => false,
                    'quality' => $quality,
                ];
            }

            // Re-encoding didn't help — keep what was there.
            return [
                'conversion' => $conversion,
                'orig' => $origSize,
                'new' => $origSize,
                'optimal' => true,
                'quality' => $quality,
            ];
        }

        // Filesize mode: iterate quality downward trying to hit the target size,
        // stopping early once under target or at the quality floor.
        $targetBytes = max(1, $value * 1024);
        $startQuality = self::MAX_QUALITY;

        $bestQuality = $startQuality;
        $bestBlob = null;
        $bestSize = PHP_INT_MAX;

        for ($quality = $startQuality; $quality >= $floor; $quality -= 10) {
            ob_start();
            imagewebp($source, null, $quality);
            $blob = ob_get_clean();
            $size = strlen($blob);

            if ($size < $bestSize) {
                $bestSize = $size;
                $bestBlob = $blob;
                $bestQuality = $quality;
            }

            if ($size <= $targetBytes) {
                break; // reached the target — stop lowering quality
            }
        }

        imagedestroy($source);

        if ($bestBlob === null) {
            throw new RuntimeException('Compression produced no output.');
        }

        $origSize = filesize($path);
        $newSize = strlen($bestBlob);

        if ($newSize < $origSize) {
            file_put_contents($path, $bestBlob);
            @touch($path);
        } else {
            $newSize = $origSize;
            $bestQuality = null; // unchanged
        }

        return [
            'conversion' => $conversion,
            'orig' => $origSize,
            'new' => $newSize,
            'optimal' => $newSize >= $origSize || ($origSize <= $targetBytes),
            'quality' => $bestQuality,
        ];
    }
}
