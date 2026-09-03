<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TourCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'no_robots',
    ];

    // Auto-generate slug from name, same convention as MediaCategory/MediaTag.
    protected static function booted()
    {
        static::creating(function (TourCategory $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (TourCategory $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationship: every tour package assigned to this category.
    public function tourPackages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'tour_category_tour_package')
                    ->withTimestamps();
    }

    // Freeform content sections (Overview, Why Choose This Region, Who This Is For,
    // etc.) shown on this category's public listing page — same flexible
    // title/content/image/order pattern as TourPackage's extra_sections, but as real
    // rows on their own table (tour_category_sections) rather than a JSON column,
    // since this is a brand-new feature with no legacy data shape to carry forward.
    public function sections()
    {
        return $this->hasMany(TourCategorySection::class)->orderBy('order');
    }

    // Sections meant to render above the tour grid, in order.
    public function sectionsAboveGrid()
    {
        return $this->sections()->where('placement', 'above_grid');
    }

    // Sections meant to render below the tour grid, in order.
    public function sectionsBelowGrid()
    {
        return $this->sections()->where('placement', 'below_grid');
    }

    // Scope: only categories that currently have at least one published tour — used by
    // the footer/nav so a category with nothing in it doesn't link to an empty page.
    public function scopeWithPublishedTours($query)
    {
        return $query->whereHas('tourPackages', function ($q) {
            $q->where('status', 'published');
        });
    }
}
