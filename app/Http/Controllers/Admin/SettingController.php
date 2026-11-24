<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(Request $request)
    {
        $allowed = ['site_name', 'logo_image_id', 'default_currency', 'timezone', 'footer_phone',
            'whatsapp_number', 'site_email', 'wechat_id', 'line_id', 'ga_id', 'enable_sitemap'];
        $request->merge(['settings' => array_intersect_key((array) $request->input('settings', []), array_flip($allowed))]);

        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
            // Selected via <x-media-picker name="settings[...]">  — existing image ids
            // from the Media Library, not uploaded files.
            'settings.logo_image_id'            => 'nullable|integer|exists:media,id',
            'settings.hero_image_id'            => 'nullable|integer|exists:media,id',
            'settings.our_story_image_id'       => 'nullable|integer|exists:media,id',
            'settings.get_to_know_image_id'     => 'nullable|integer|exists:media,id',
            'settings.activities_menu_image_id' => 'nullable|integer|exists:media,id',
        ]);

        // Capture previous selections for every Media-Library-backed setting before
        // the generic settings loop below overwrites them, so usage can be
        // forgotten/recorded correctly for whichever ones actually changed — same
        // convention as the logo image swap.
        $imageSettingKeys = ['logo_image_id'];
        $previousImageValues = [];
        foreach ($imageSettingKeys as $key) {
            $previousImageValues[$key] = Setting::get($key);
        }

        foreach ($request->settings as $key => $value) {
            Setting::set($key, $value);
        }

        $mediaLibrary = app(MediaLibraryService::class);
        foreach ($imageSettingKeys as $key) {
            if (! $request->has("settings.$key")) {
                continue;
            }

            $newValue = $request->filled("settings.$key") ? (int) $request->input("settings.$key") : null;
            $previousValue = $previousImageValues[$key] ? (int) $previousImageValues[$key] : null;

            if ($newValue === $previousValue) {
                continue;
            }

            $settingRow = Setting::where('key', $key)->first();

            if ($previousValue) {
                $previousImage = GalleryImage::find($previousValue);
                if ($previousImage) {
                    $mediaLibrary->forgetUsage($previousImage->id, $settingRow, $key);
                }
            }

            if ($newValue) {
                $newImage = GalleryImage::find($newValue);
                if ($newImage) {
                    $mediaLibrary->recordUsage($newImage->id, $settingRow, $key);
                }
            }
        }

        return redirect()->route('admin.settings.index')
                         ->with('success', 'Settings updated successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
