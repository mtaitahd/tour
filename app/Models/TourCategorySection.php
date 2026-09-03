<?php

namespace App\Models;

use App\Models\Concerns\AutoCurrentYearTitle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCategorySection extends Model
{
    use AutoCurrentYearTitle;

    protected $fillable = [
        'tour_category_id',
        'title',
        'content',
        'image_id',
        'placement',
        'order',
    ];

    // The category this section belongs to.
    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    // The section's image, picker-selected — a direct FK to media.id, same pattern as
    // Destination::heroImage()/TourPackage::heroImage() etc.
    public function image(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'image_id');
    }

    // URL shortcut, mirroring the *Url() accessors already established on the four
    // content models (Destination::heroUrl(), etc.) — returns null rather than a
    // broken path when no image is set, so templates can use ?? for a fallback.
    public function imageUrl(string $conversion = ''): ?string
    {
        return $this->image?->getUrl($conversion) ?: $this->image?->getUrl();
    }
}
