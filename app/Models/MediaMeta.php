<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaMeta extends Model
{
    protected $table = 'media_meta';

    protected $fillable = [
        'media_id',
        'title',
        'alt_text',
        'caption',
        'description',
    ];

    // Relationship: the underlying Spatie media row this metadata belongs to
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
