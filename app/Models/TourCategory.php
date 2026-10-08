<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TourCategory extends Model
{
    use HasFactory;

    /* ── Grouped taxonomy (type discriminator) ──────────────────────────────
     * One table holds every tour classification group. 'category' is the
     * legacy type — those rows are the public /{slug} landing pages
     * (tanzania-tours, kilimanjaro-climbing) and must keep working unchanged.
     * The grouped types below are filtered independently by the tour form's
     * per-group dropdowns.
     * Physical rating / tour level / months / duration_days are NOT here —
     * they stay hard columns on tour_packages (Option A). */
    public const TYPE_CATEGORY     = 'category';
    public const TYPE_COUNTRY      = 'country';
    public const TYPE_REGION       = 'region';
    public const TYPE_TOUR_TYPE    = 'tour_type';
    public const TYPE_DURATION     = 'duration';

    /** Groups shown as dropdowns on the Add/Edit Tour Package form. */
    public const FORM_GROUPS = [
        self::TYPE_COUNTRY   => 'Country',
        self::TYPE_REGION    => 'Region / Continent',
        self::TYPE_TOUR_TYPE => 'Tour Type',
        self::TYPE_DURATION  => 'Duration',
    ];

    /** Every type accepted by the admin (form groups + legacy pages). */
    public const TYPES = [
        self::TYPE_CATEGORY,
        self::TYPE_COUNTRY,
        self::TYPE_REGION,
        self::TYPE_TOUR_TYPE,
        self::TYPE_DURATION,
    ];

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'description',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'no_robots',
    ];

    // Auto-generate slug from name, same convention as MediaCategory/MediaTag.
    // Slugs are globally unique (they are public URLs under the /{categorySlug}
    // wildcard route), so a name collision must produce "tanzania-2" rather than
    // an uncaught QueryException / 500 from the DB unique index.
    protected static function booted()
    {
        static::creating(function (TourCategory $category) {
            if (empty($category->slug)) {
                $category->slug = static::uniqueSlug(static::slugify($category->name), null);
            }
        });

        static::updating(function (TourCategory $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = static::uniqueSlug(static::slugify($category->name), $category->id);
            }
        });
    }

    protected static function slugify(string $name): string
    {
        return Str::slug($name) ?: 'category';
    }

    /** First free slug in tour_categories: base, base-2, base-3, … */
    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $n = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . (++$n);
        }

        return $slug;
    }

    // ── Grouped-taxonomy scopes ──────────────────────────────────────────

    /** Rows belonging to one classification group (country, region, …). */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /** Only options that may be offered in a picker. */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Picker options for one group, ordered the same way everywhere.
     * $includeInactive is used by the Edit Tour form so an option that was
     * deactivated after being selected still shows (marked "(inactive)")
     * instead of being silently detached on save — same rule the Activities
     * picker already follows.
     */
    public static function pickerOptions(string $type, bool $includeInactive = false): array
    {
        return static::where('type', $type)
            ->orderBy('order')->orderBy('name')->get()
            ->map(fn (TourCategory $category) => [
                'value'    => $category->id,
                'label'    => $category->name
                    . ($includeInactive && $category->status !== self::STATUS_ACTIVE ? ' (inactive)' : ''),
                'selected' => false,
            ])
            ->all();
    }

    /** Human label for a type, used by the admin UI. */
    public static function typeLabel(string $type): string
    {
        return self::FORM_GROUPS[$type]
            ?? match ($type) {
                self::TYPE_CATEGORY => 'Listing Categories',
                default             => ucfirst(str_replace('_', ' ', $type)),
            };
    }

    /** "+ Add X" button text for a type, e.g. "Add Country". */
    public static function typeAddLabel(string $type): string
    {
        return 'Add ' . (self::FORM_GROUPS[$type] ?? ucfirst(str_replace('_', ' ', $type)));
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
