<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\Support\UrlGenerator\UrlGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomUrlGenerator implements UrlGenerator
{
    protected Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function getUrl(): string
    {
        // Serve directly from storage/app/public (no symlink)
        return asset('storage/' . $this->media->getPathRelativeToRoot());
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        return $this->getBaseMediaDirectoryUrl() . '/conversions';
    }

    public function getBaseMediaDirectoryUrl(): string
    {
        return asset('storage/' . $this->media->collection_name);
    }

    public function getPath(): string
    {
        return storage_path('app/public/' . $this->media->getPathRelativeToRoot());
    }

    public function getResponsiveImagesDirectoryPath(): string
    {
        return $this->getBaseMediaDirectoryPath() . '/conversions';
    }

    public function getBaseMediaDirectoryPath(): string
    {
        return storage_path('app/public/' . $this->media->collection_name);
    }
}