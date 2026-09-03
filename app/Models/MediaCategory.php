<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'order',
    ];

    // Auto-generate slug from name when creating/updating
    protected static function booted()
    {
        static::creating(function (MediaCategory $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (MediaCategory $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationship: parent category (e.g. "Destinations" is the parent of "Kilimanjaro")
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaCategory::class, 'parent_id');
    }

    // Relationship: direct children of this category
    public function children(): HasMany
    {
        return $this->hasMany(MediaCategory::class, 'parent_id')->orderBy('order')->orderBy('name');
    }

    // Relationship: all media items assigned to this category
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_category_media', 'media_category_id', 'media_id')
                    ->withTimestamps();
    }

    // Scope: only top-level (root) categories, for building the tree from the top
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper: full breadcrumb path, e.g. "Destinations > Kilimanjaro"
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->name]);
        $node = $this;

        while ($node->parent) {
            $node = $node->parent;
            $path->prepend($node->name);
        }

        return $path->implode(' > ');
    }

    // Helper: how many media items are directly in this category
    public function getMediaCountAttribute(): int
    {
        return $this->media()->count();
    }
}
