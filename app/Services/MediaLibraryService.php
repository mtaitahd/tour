<?php

namespace App\Services;

use App\Models\GalleryImage;
use App\Models\MediaCategory;
use App\Models\MediaTag;
use App\Models\MediaUsage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The query/organization layer of the Media Library: search, category/tag filtering,
 * sorting, and usage tracking. ImageProcessorService handles getting files onto disk;
 * this service handles finding, organizing, and safely deleting what's already there.
 */
class MediaLibraryService
{
    /** Matches the existing admin/media/index.blade.php pagination size. */
    public const DEFAULT_PER_PAGE = 24;

    /**
     * Build the base query for browsing the library, with optional search, category,
     * tag, and date filters layered on top. Mirrors the brief's "Search & Filters"
     * requirement: search by title/filename/tags/category, filter by upload date.
     *
     * @param array{
     *   search?: string|null,
     *   category_id?: int|null,
     *   tag_id?: int|null,
     *   uploaded_from?: string|null,
     *   uploaded_to?: string|null,
     *   sort?: string|null,
     * } $filters
     */
    public function query(array $filters = []): Builder
    {
        $query = GalleryImage::query()->with(['meta', 'categories', 'tags', 'uploader'])->withCount('usages');

        if (! empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('file_name', 'like', "%{$term}%")
                  ->orWhereHas('meta', function (Builder $metaQuery) use ($term) {
                      $metaQuery->where('title', 'like', "%{$term}%")
                                ->orWhere('alt_text', 'like', "%{$term}%")
                                ->orWhere('caption', 'like', "%{$term}%");
                  })
                  ->orWhereHas('tags', function (Builder $tagQuery) use ($term) {
                      $tagQuery->where('name', 'like', "%{$term}%");
                  });
            });
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];

            $query->whereHas('categories', function (Builder $q) use ($categoryId) {
                $q->where('media_categories.id', $categoryId);
            });
        }

        if (! empty($filters['tag_id'])) {
            $tagId = (int) $filters['tag_id'];

            $query->whereHas('tags', function (Builder $q) use ($tagId) {
                $q->where('media_tags.id', $tagId);
            });
        }

        if (! empty($filters['uploaded_from'])) {
            $query->whereDate('created_at', '>=', $filters['uploaded_from']);
        }

        if (! empty($filters['uploaded_to'])) {
            $query->whereDate('created_at', '<=', $filters['uploaded_to']);
        }

        $this->applySort($query, $filters['sort'] ?? 'newest');

        return $query;
    }

    /**
     * Run the filtered query and paginate it — the method the Media Library index page
     * calls directly.
     */
    public function paginate(array $filters = [], int $perPage = self::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Sort by newest/oldest upload date, name, or usage count (most/least used) —
     * covers the brief's "Sort by usage" / "Sort by newest" requirement. The usages
     * count itself comes from query()'s unconditional withCount('usages') (added to
     * eliminate a per-image N+1 in the grid view — see GalleryImage::
     * getUsageCountAttribute()), so sorting by it here is just an orderBy against the
     * already-selected usages_count column, not a second count.
     */
    protected function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'most_used' => $query->orderByDesc('usages_count'),
            'least_used' => $query->orderBy('usages_count'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->orderBy('created_at', 'desc'), // 'newest'
        };
    }

    /**
     * Record that a media item is being used in a particular place. Called whenever an
     * image is attached — whether via a Spatie collection (collection name as context)
     * or via a direct FK like hero_image_id, or even a JSON-embedded reference inside
     * itinerary/extra_sections (a descriptive context string in that case — see Phase 1
     * analysis and the media_usages migration for why this exists).
     *
     * Safe to call repeatedly for the same usage — relies on the unique constraint on
     * (media_id, model_type, model_id, context) and just updates the timestamp if the
     * row already exists, rather than creating duplicates.
     */
    public function recordUsage(int $mediaId, Model $model, string $context): MediaUsage
    {
        return MediaUsage::updateOrCreate(
            [
                'media_id' => $mediaId,
                'model_type' => $model::class,
                'model_id' => $model->getKey(),
                'context' => $context,
            ],
            [] // nothing else to update — the row's existence is all that matters
        );
    }

    /**
     * Remove a recorded usage — called when an image is detached/replaced/removed from
     * wherever it was being used, so the usage table stays accurate and an image that's
     * no longer referenced anywhere can eventually be deleted.
     */
    public function forgetUsage(int $mediaId, Model $model, string $context): void
    {
        MediaUsage::where('media_id', $mediaId)
            ->where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->where('context', $context)
            ->delete();
    }

    /**
     * Remove every recorded usage for a model — called when the model itself is deleted,
     * so its old image references don't linger in media_usages forever.
     */
    public function forgetAllUsagesFor(Model $model): void
    {
        MediaUsage::where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->delete();
    }

    /**
     * Replace the entire ordered set of images for one context on one model — the
     * multi-select picker's save path for galleries and similar multi-image fields
     * (Destination/TourPackage gallery, safari car images, itinerary day images,
     * extra section images). This is a full replace, not an add: whatever was
     * previously recorded for this exact (model, context) is removed first, then the
     * new list is recorded in the given order. Safe to call with an empty array to
     * clear a gallery entirely.
     *
     * Deliberately NOT a diff/merge — these collections are now picker-only (no
     * direct upload alongside them, per the decision to fully replace upload with
     * the picker for these five fields), so the picker's current selection is always
     * the complete, authoritative list for that context, not an addition to whatever
     * was there before.
     *
     * @param int[] $mediaIds Ordered list of media ids — array position becomes
     *                         the stored 'order' value.
     */
    public function setOrderedUsages(Model $model, string $context, array $mediaIds): void
    {
        MediaUsage::where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->where('context', $context)
            ->delete();

        foreach (array_values($mediaIds) as $position => $mediaId) {
            MediaUsage::create([
                'media_id' => $mediaId,
                'model_type' => $model::class,
                'model_id' => $model->getKey(),
                'context' => $context,
                'order' => $position,
            ]);
        }
    }

    /**
     * The ordered list of GalleryImage instances currently recorded for one context on
     * one model — the read side of setOrderedUsages(), used by both the admin edit
     * forms (to show the current gallery for re-ordering/removal) and any public
     * template that needs to display a gallery.
     *
     * @return \Illuminate\Support\Collection<int, GalleryImage>
     */
    public function orderedImagesFor(Model $model, string $context): Collection
    {
        $mediaIds = MediaUsage::where('model_type', $model::class)
            ->where('model_id', $model->getKey())
            ->where('context', $context)
            ->orderBy('order')
            ->pluck('media_id');

        if ($mediaIds->isEmpty()) {
            return collect();
        }

        // Fetch all in one query, then re-order in PHP to match $mediaIds exactly —
        // a plain whereIn() does not guarantee result order matches the IN list.
        $images = GalleryImage::query()->whereIn('id', $mediaIds)->get()->keyBy('id');

        return $mediaIds->map(fn ($id) => $images->get($id))->filter()->values();
    }

    /**
     * Whether a media item is currently used anywhere. Thin wrapper around
     * GalleryImage::is_in_use for callers that only have an id, not a loaded model.
     */
    public function isInUse(int $mediaId): bool
    {
        return MediaUsage::where('media_id', $mediaId)->exists();
    }

    /**
     * Attempt to delete a media item, but refuse if it's still in use anywhere —
     * the brief's "Prevent deletion while in use" requirement. Returns false (rather
     * than throwing) when blocked, so callers can show a friendly message with the
     * usage list rather than handling an exception.
     */
    public function deleteIfUnused(GalleryImage $image, ImageProcessorService $processor): bool
    {
        if ($this->isInUse($image->id)) {
            return false;
        }

        return $processor->delete($image);
    }

    /**
     * The list of places a media item is used, for display in the admin detail view
     * (the brief's "Used in 5 locations" — but with the actual locations, not just the
     * count). Each entry includes a human-readable label built from the model type and
     * context, e.g. "Destination #3 — gallery" or "Mount Kilimanjaro Safari — hero".
     *
     * @return array<int, array{model_type: string, model_id: int, context: string, label: string}>
     */
    public function usageLocations(int $mediaId): array
    {
        return MediaUsage::where('media_id', $mediaId)
            ->get()
            ->map(function (MediaUsage $usage) {
                $shortType = class_basename($usage->model_type);
                $record = $usage->usable;
                $name = $record?->name ?? $record?->title ?? $record?->slug ?? "#{$usage->model_id}";

                return [
                    'model_type' => $usage->model_type,
                    'model_id' => $usage->model_id,
                    'context' => $usage->context,
                    'label' => "{$shortType}: {$name} — {$usage->context}",
                ];
            })
            ->all();
    }

    /**
     * Category tree, root-down, with each node's direct children eager loaded one level
     * at a time — used to render the nested category picker/filter in the admin UI
     * without an N+1 query per level.
     */
    public function categoryTree(): Collection
    {
        return MediaCategory::root()
            ->with('children.children.children') // supports up to 3 levels of nesting out of the box
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * All tags, with usage counts, ordered alphabetically — used to render the tag
     * filter list in the admin UI.
     */
    public function allTagsWithCounts(): Collection
    {
        return MediaTag::query()
            ->withCount('media')
            ->orderBy('name')
            ->get();
    }

    /**
     * Quick stats for the library's dashboard header — total images, total storage used,
     * and how many are currently unused (candidates for cleanup).
     */
    public function stats(): array
    {
        return [
            'total_images' => GalleryImage::count(),
            'total_size_bytes' => GalleryImage::sum('size'),
            'unused_count' => GalleryImage::query()
                ->whereDoesntHave('usages')
                ->count(),
        ];
    }
}
