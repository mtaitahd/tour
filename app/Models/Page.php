<?php

namespace App\Models;

use App\Models\Concerns\AutoCurrentYearTitle;
use App\Models\Concerns\HasStandardMediaConversions;
use App\Models\Concerns\ResolvesPublicFallbackAsset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasStandardMediaConversions, AutoCurrentYearTitle, ResolvesPublicFallbackAsset {
        HasStandardMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * The fixed set of "site information" pages.
     *
     * These describe the site itself (who we are, how to reach us, the legal
     * terms) rather than being standalone content, so they are managed on their
     * own admin screen (Site Information) instead of being mixed in with the
     * ordinary pages under Admin → Pages → All Pages.
     *
     * The slug is the identity here — these rows already exist and are already
     * linked from the footer/header, so membership is keyed on slug rather than
     * on a new database flag. This stays the single source of truth: the admin
     * listing, the site-information screen and the redirect guard all read it.
     *
     * @var array<string, string> slug => human label
     */
    public const SITE_INFO_PAGES = [
        'about-us'             => 'About Us',
        'why-choose-us'        => 'Why Choose Us',
        'contact'              => 'Contact Us',
        'terms-and-conditions' => 'Terms and Conditions',
    ];

    /** @return array<int, string> */
    public static function siteInfoSlugs(): array
    {
        return array_keys(self::SITE_INFO_PAGES);
    }

    public function isSiteInfo(): bool
    {
        return array_key_exists($this->slug, self::SITE_INFO_PAGES);
    }

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'order',
        'no_robots',
        'meta_title',
        'meta_description',
        'meta_keywords',
        // Extra/custom fields (keep these)
        'extra_heading',
        'extra_subheading',
        'extra_hero_image',
        'cta_text',
        'cta_link',
        'contact_heading',
        'contact_subheading',
        'contact_map_embed',
        'story_title',
        'why_choose_subtitle',
        'stats_counters',
        'custom_data',
        'story_gallery',
        // Media Library
        'hero_image_id',
        'story_image_id',
    ];

    protected $casts = [
        // stats_counters: [{value, suffix, label}, ...] — About page stat row.
        // custom_data: [{name, role, bio, photo_image_id}, ...] — About page team
        // grid. Both were already in $fillable/validation but had no cast, so they
        // were returned as raw JSON strings rather than usable arrays.
        // story_gallery: [{image_id, caption}, ...] — About page Our Story images.
        'stats_counters' => 'array',
        'custom_data'    => 'array',
        'story_gallery'  => 'array',
        'no_robots'      => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        // These fallbacks used to name 'asset/img/placeholder-page-hero.jpg' and
        // 'asset/img/placeholder-about.jpg', but neither file was ever committed
        // under public/ — the whole 'asset/' (singular) directory does not exist
        // in the repo, and production 404s on it too, so a page without a hero or
        // story image rendered a broken <img>. They now resolve against a bundled
        // asset that is actually deployed, trying both known locations of it.
        $this->addMediaCollection('hero')
             ->singleFile()
             ->useFallbackUrl(static::firstExistingPublicAsset(
                 'assets/images/safari-hero.jpg',
                 'front-end/html/assets/images/safari-hero.jpg',
             ));

        $this->addMediaCollection('story')
             ->singleFile()
             ->useFallbackUrl(static::firstExistingPublicAsset(
                 'assets/images/safari-hero.jpg',
                 'front-end/html/assets/images/safari-hero.jpg',
             ));

        // Optional: if you want a gallery later
        $this->addMediaCollection('gallery');
    }

    // Note: Page previously defined its own conversion set here (thumb/medium/large,
    // no WebP variants) — different from Destination/TourPackage. Using the shared
    // HasStandardMediaConversions trait brings Page in line with the other three
    // modules (adds thumb-webp/medium-webp/large-webp), per the Phase 1 plan to make
    // conversions consistent across all content types. New Page hero/story uploads will
    // get WebP variants; existing ones are covered by the Phase 2/4 backfill command.

    // Relationship: hero image selected from the shared Media Library. Additive.
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'hero_image_id');
    }

    // Relationship: story image selected from the shared Media Library. Additive.
    public function storyImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'story_image_id');
    }

    /**
     * Hero image URL, preferring the Media Library selection over the legacy 'hero'
     * Spatie collection. See Destination::heroUrl() for the full rationale — every
     * public template must call this instead of getFirstMediaUrl('hero') directly.
     */
    public function heroUrl(string $conversion = ''): ?string
    {
        if ($this->heroImage) {
            return $this->heroImage->getUrl($conversion) ?: $this->heroImage->getUrl();
        }

        return $this->getFirstMediaUrl('hero', $conversion) ?: null;
    }

    /**
     * Story image URL, same fallback pattern as heroUrl() but for the 'story' collection.
     */
    public function storyUrl(string $conversion = ''): ?string
    {
        if ($this->storyImage) {
            return $this->storyImage->getUrl($conversion) ?: $this->storyImage->getUrl();
        }

        return $this->getFirstMediaUrl('story', $conversion) ?: null;
    }

    public function hasHeroImage(): bool
    {
        return $this->heroImage !== null || $this->getFirstMedia('hero') !== null;
    }

    public function hasStoryImage(): bool
    {
        return $this->storyImage !== null || $this->getFirstMedia('story') !== null;
    }
}