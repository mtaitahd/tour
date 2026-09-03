<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUsage extends Model
{
    protected $fillable = [
        'media_id',
        'model_type',
        'model_id',
        'context',
        'order',
    ];

    // Relationship: the underlying Spatie media row being tracked
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    // Relationship: the content record (Destination, TourPackage, BlogPost, Page, ...)
    // that uses this media item. model_type/model_id are stored exactly like Spatie's own
    // polymorphic columns, so this resolves the same way a normal morphTo would.
    public function usable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    // Scope: usages for one specific media item
    public function scopeForMedia($query, int $mediaId)
    {
        return $query->where('media_id', $mediaId);
    }

    // Scope: usages belonging to a particular context (e.g. 'hero', or a JSON-embedded
    // path like 'itinerary.day_2') — useful when displaying *where* an image is used,
    // not just whether it is.
    public function scopeInContext($query, string $context)
    {
        return $query->where('context', $context);
    }
}
