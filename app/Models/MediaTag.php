<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    // Auto-generate slug from name when creating/updating
    protected static function booted()
    {
        static::creating(function (MediaTag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function (MediaTag $tag) {
            if ($tag->isDirty('name') && ! $tag->isDirty('slug')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationship: all media items carrying this tag
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_tag_media', 'media_tag_id', 'media_id')
                    ->withTimestamps();
    }

    // Helper: how many media items carry this tag
    public function getMediaCountAttribute(): int
    {
        return $this->media()->count();
    }
}
