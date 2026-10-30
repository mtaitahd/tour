<?php

namespace App\Models;

use App\Models\Concerns\HasStandardMediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * GlobalMedia is a pre-existing singleton (a single row with no real columns) used purely
 * as an anchor model so Spatie has *something* to attach "unowned" media to — e.g. images
 * uploaded into the 'general' collection from the admin Media Library before being
 * assigned to any specific Destination/Tour/Page/BlogPost.
 *
 * It is kept as-is in Phase 3 since Admin\MediaController and existing production data
 * (see Phase 1 analysis — media row id 80, collection 'general') depend on it. With
 * GalleryImage, MediaCategory, MediaTag, and MediaUsage now in place (see those models),
 * GlobalMedia's job shrinks to "the model a freshly uploaded, not-yet-attached image
 * belongs to" — everything else a real media library needs (organization, search, SEO,
 * usage tracking) is handled by those new models instead, attached to the underlying
 * GalleryImage/media row itself rather than to GlobalMedia. Retiring GlobalMedia entirely
 * is a candidate for a later phase once the Media Library UI (Phase 7) no longer needs a
 * placeholder owner for unattached uploads.
 */
class GlobalMedia extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasStandardMediaConversions {
        HasStandardMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',           // Allow setting id for firstOrCreate singleton
        // Add any other fields you might use in future (e.g. name, description)
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('general');
        $this->addMediaCollection('hero');
        $this->addMediaCollection('gallery');
        // Add any other collections you need
    }
}
