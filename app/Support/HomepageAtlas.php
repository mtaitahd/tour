<?php

namespace App\Support;

use App\Models\GalleryImage;
use App\Models\Setting;
use App\Models\TourPackage;

class HomepageAtlas
{
    public static function countries(): array
    {
        $overrides = collect(Setting::json('landing_atlas_countries'));
        $codes = ['tanzania' => 'TZ', 'kenya' => 'KE', 'uganda' => 'UG', 'rwanda' => 'RW'];
        $countries = LandingDefaults::rows('atlas');

        foreach ($countries as &$country) {
            $id = $country['id'];
            $override = $overrides->firstWhere('id', $id) ?? [];
            $country = array_replace($country, array_filter($override, fn ($value) => filled($value)));
            if (!empty($override['photo_image_id'])) {
                $image = GalleryImage::find($override['photo_image_id']);
                if ($image) $country['heroImage'] = $image->getUrl();
            }
            $country['continentLabel'] = $override['continentLabel'] ?? 'Africa';
            $country['primaryLabel'] = ($override['primaryLabel'] ?? '') ?: 'Explore '.$country['name'].' Safaris';
            $country['secondaryLabel'] = ($override['secondaryLabel'] ?? '') ?: 'Country Guide';
            $country['tourCount'] = TourPackage::where('status', 'published')
                ->where(function ($query) use ($id, $codes) {
                    $query->whereHas('categories', fn ($category) => $category->where('type', 'country')
                        ->whereRaw('LOWER(name) = ?', [$id]))
                        ->orWhereHas('destinations', fn ($destination) => $destination->where('country_code', $codes[$id]));
                })->count();
        }
        unset($country);

        return $countries;
    }
}
