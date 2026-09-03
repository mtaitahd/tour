<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    public $timestamps = true;

    // Helper to get value with fallback
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Helper to set value
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Decode a JSON-array-backed setting (why_choose_us_cards, partner_logos, etc.)
     * into a plain PHP array. Centralizes the json_decode(..., true) ?? [] pattern
     * that was starting to repeat across admin forms and public views.
     */
    public static function json(string $key, array $default = []): array
    {
        $raw = self::get($key);

        if (empty($raw)) {
            return $default;
        }

        return json_decode($raw, true) ?? $default;
    }

    /**
     * Generic single-image URL for any Media-Library-backed setting (hero image,
     * Our Story image, Get to Know About Us image, etc.) — same lookup logoUrl()
     * uses, extracted so new homepage image settings don't each repeat it.
     */
    public static function imageUrl(string $key, string $conversion = ''): ?string
    {
        if ($imageId = self::get($key)) {
            $image = GalleryImage::find($imageId);
            if ($image) {
                return $image->getUrl($conversion) ?: $image->getUrl();
            }
        }

        return null;
    }

    /**
     * Site logo URL, preferring the Media Library selection ('logo_image_id' setting)
     * when set, falling back to the legacy direct-upload 'logo_path' setting otherwise
     * — same fallback convention as User::avatarUrl() and Destination::heroUrl().
     */
    public static function logoUrl(string $conversion = ''): ?string
    {
        if ($url = self::imageUrl('logo_image_id', $conversion)) {
            return $url;
        }

        if ($legacyPath = self::get('logo_path')) {
            return \Storage::url($legacyPath);
        }

        return null;
    }

    /**
     * Whether a site logo is set at all, via either path.
     */
    public static function hasLogo(): bool
    {
        return (bool) self::logoUrl();
    }
}