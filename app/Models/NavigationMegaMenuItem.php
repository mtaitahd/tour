<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One admin-configured entry in the three-column desktop mega menu / mobile
 * accordion. Each row is either:
 *  - a standalone "custom" item (source_type = 'custom', source_id = null) created
 *    directly in the dedicated "Mega Nav" admin module — it carries its own title,
 *    heading, short description, image and explicit link URL; or
 *  - linked to a single source (TourPackage or Page), created from the source's
 *    legacy mega-menu form, with presentation overrides for menu label, short
 *    description, CTA button label/URL, context image, badge, display order, and the
 *    Show-in-Mega-Menu toggle.
 *
 * Public rendering is deliberately strict: an item only appears when is_active is
 * true AND its source still exists AND (for tours) the tour is published, (for
 * pages) the page is published. Draft/archived/deleted sources never render, which
 * also means no broken links can come from this table.
 */
class NavigationMegaMenuItem extends Model
{
    use HasFactory;

    public const SOURCE_TOUR = 'tour';
    public const SOURCE_PAGE = 'page';
    public const SOURCE_CUSTOM = 'custom';

    protected $table = 'navigation_mega_items';

    protected $fillable = [
        'parent_menu_key',
        'source_type',
        'source_id',
        'menu_label',
        'heading',
        'short_description',
        'button_label',
        'button_url_override',
        'image_id',
        'badge_text',
        'display_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source_id'     => 'integer',
        'image_id'      => 'integer',
        'display_order' => 'integer',
        'is_active'     => 'boolean',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
    ];

    /** The image picked from the Media Library for this item's right column. */
    public function image(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'image_id');
    }

    /** Split relations — source_type decides which of tour()/page() is populated. */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'source_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'source_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isTour(): bool
    {
        return $this->source_type === self::SOURCE_TOUR;
    }

    public function isPage(): bool
    {
        return $this->source_type === self::SOURCE_PAGE;
    }

    /**
     * Whether this is a standalone admin-created item with no TourPackage/Page
     * source behind it (source_type = 'custom', source_id = null).
     */
    public function isCustom(): bool
    {
        return $this->source_type === self::SOURCE_CUSTOM;
    }

    /**
     * The live source model (TourPackage or Page), or null when the source no
     * longer exists — and always null for standalone custom items.
     */
    public function resolveSource(): ?Model
    {
        if ($this->isCustom()) {
            return null;
        }

        if ($this->isTour()) {
            return $this->tour;
        }

        if ($this->isPage()) {
            return $this->page;
        }

        return null;
    }

    /**
     * Whether the source currently qualifies for public display. Standalone custom
     * items have no source to check — they only need to be active (which the public
     * query already enforces). Tours must be 'published' (draft/archived never
     * render); pages must be 'published' too.
     */
    public function sourceIsPublishable(): bool
    {
        if ($this->isCustom()) {
            return true;
        }

        $source = $this->resolveSource();

        if (! $source) {
            return false;
        }

        if ($this->isTour()) {
            return ($source instanceof TourPackage) && $source->status === 'published';
        }

        return ($source instanceof Page) && $source->status === 'published';
    }

    /**
     * Target URL for this item's card/CTA: an explicit override wins, otherwise the
     * source's public route. Standalone custom items have no source route, so they
     * must carry an explicit override (validated on the Mega Nav form). Returns null
     * when the item has no URL — callers must skip such items rather than render a
     * broken link.
     */
    public function url(): ?string
    {
        $override = trim((string) $this->button_url_override);

        if ($override !== '') {
            return $override;
        }

        if ($this->isCustom()) {
            return null;
        }

        $source = $this->resolveSource();

        if (! $source) {
            return null;
        }

        if ($this->isTour() && $source instanceof TourPackage) {
            return route('tour.show', $source->slug);
        }

        if ($this->isPage() && $source instanceof Page) {
            return route('page.show', $source->slug);
        }

        return null;
    }

    /**
     * CTA button label — the admin override when set, otherwise a safe per-type
     * default so a visible CTA never renders blank.
     */
    public function buttonLabel(): string
    {
        $label = trim((string) $this->button_label);

        if ($label !== '') {
            return $label;
        }

        return $this->isTour() ? 'View Tour Details' : 'Read More';
    }

    /**
     * Contextual image: the menu image wins, then the source's hero image, then a
     * site-wide fallback. The right mega-menu column always has something to show.
     */
    public function imageUrl(): string
    {
        if ($this->image) {
            return (string) $this->image->getUrl();
        }

        $source = $this->resolveSource();

        if ($source && method_exists($source, 'cardImageUrl')) {
            $card = $source->cardImageUrl();
            if ($card) {
                return $card;
            }
        }

        if ($source && method_exists($source, 'heroUrl')) {
            $hero = $source->heroUrl();
            if ($hero) {
                return $hero;
            }
        }

        return (string) asset((string) config('navigation.fallback_image', 'asset/img/placeholder-page-hero.jpg'));
    }

    /**
     * Short description for the middle column: the admin's custom text when set,
     * otherwise a stripped, word-limited excerpt of the source content (overview/
     * content). Always returns plain text, never raw HTML.
     */
    public function descriptionText(int $wordLimit = 0): string
    {
        $custom = trim((string) $this->short_description);

        if ($custom !== '') {
            return $wordLimit > 0 ? Str::words($custom, $wordLimit, '…') : $custom;
        }

        $source = $this->resolveSource();

        $excerpt = '';
        if ($this->isTour() && $source instanceof TourPackage) {
            $excerpt = (string) $source->overview;
        } elseif ($this->isPage() && $source instanceof Page) {
            $excerpt = (string) $source->content;
        }

        $excerpt = trim(strip_tags($excerpt));
        $excerpt = preg_replace('/\s+/u', ' ', $excerpt) ?? '';

        $limit = $wordLimit > 0 ? $wordLimit : (int) config('navigation.description_word_limit', 45);

        return $excerpt === '' ? '' : Str::words($excerpt, $limit, '…');
    }
}