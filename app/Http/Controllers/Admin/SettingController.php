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
            'why_choose_us_cards.*.icon'        => 'nullable|string|max:100',
            'why_choose_us_cards.*.title'       => 'nullable|string|max:255',
            'why_choose_us_cards.*.description' => 'nullable|string',
            'partner_logos.*.name'          => 'nullable|string|max:255',
            'partner_logos.*.link'          => 'nullable|url|max:255',
            'partner_logos.*.logo_image_id' => 'nullable|integer|exists:media,id',
        ]);

        // Capture previous selections for every Media-Library-backed setting before
        // the generic settings loop below overwrites them, so usage can be
        // forgotten/recorded correctly for whichever ones actually changed — same
        // convention as the logo image swap.
        $imageSettingKeys = ['logo_image_id', 'hero_image_id', 'our_story_image_id', 'get_to_know_image_id', 'activities_menu_image_id'];
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

        // Handle "Why Choose Us" cards repeater (homepage) — plain text only (icon
        // class + title + description), no images, so unlike a media-backed field this
        // needs no usage tracking at all.
        if ($request->has('why_choose_us_cards')) {
            $cards = [];

            foreach ($request->why_choose_us_cards as $cardData) {
                $card = [
                    'icon'        => $cardData['icon'] ?? '',
                    'title'       => $cardData['title'] ?? '',
                    'description' => $cardData['description'] ?? '',
                ];

                if (!empty($card['title']) || !empty($card['description'])) {
                    $cards[] = $card;
                }
            }

            Setting::set('why_choose_us_cards', json_encode($cards));
        }

        // Handle Partner Logos repeater (footer) — fixed slots, same reasoning as
        // Page::custom_data's Team Members: each slot needs its own Media Library
        // picker, which can't be safely cloned via JS, so a fixed number of slots are
        // always pre-rendered in the form instead of a fully dynamic add/remove list.
        if ($request->has('partner_logos')) {
            $partners = [];

            foreach ($request->partner_logos as $partnerData) {
                $partner = [
                    'name'           => $partnerData['name'] ?? '',
                    'link'           => $partnerData['link'] ?? '',
                    'logo_image_id'  => $partnerData['logo_image_id'] ?? null,
                ];

                if (!empty($partner['name']) || !empty($partner['logo_image_id'])) {
                    $partners[] = $partner;
                }
            }

            Setting::set('partner_logos', json_encode($partners));

            // Usage tracking for the logos, as one ordered set — same mechanism as
            // Destination/TourPackage galleries and Page's team_photo set.
            $partnerLogosSetting = Setting::where('key', 'partner_logos')->first();
            $partnerLogoIds = collect($partners)->pluck('logo_image_id')->filter()->values()->all();
            $mediaLibrary->setOrderedUsages($partnerLogosSetting, 'partner_logo', $partnerLogoIds);
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
