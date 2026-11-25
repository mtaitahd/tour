<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LandingPageController extends Controller
{
    public function edit(string $section = 'hero')
    {
        $sections = config('landing');
        $sections['seo']['fields']['home_meta_title']['default'] =
            Setting::get('site_name', 'Afro-Vertex Tours & Safaris') . ' | East Africa Safaris, Climbs & Beach Tours';
        $sections['seo']['fields']['home_meta_title']['blank_uses_default'] = true;
        abort_unless(isset($sections[$section]), 404);
        if ($section === 'gallery' && Setting::get('landing_gallery_photos') === null) {
            $homepage = app(\App\Http\Controllers\HomeController::class)->index()->getData();
            $sections[$section]['fields']['landing_gallery_photos']['rows'] = $homepage['galleryImages'];
        }

        return view('admin.landing.edit', [
            'sections' => $sections,
            'section' => $section,
            'editor' => $sections[$section],
        ]);
    }

    public function update(Request $request, string $section)
    {
        $editor = config('landing.'.$section);
        abort_unless(is_array($editor), 404);
        $rules = [];
        foreach ($editor['fields'] as $key => $field) {
            $rules["settings.$key"] = match ($field['type']) {
                'image' => ['nullable', 'integer', Rule::exists('media', 'id')],
                'list' => ['nullable', 'array', 'max:100'],
                'checkbox' => ['nullable', 'boolean'],
                default => ['nullable', 'string', 'max:'.($field['max'] ?? 20000)],
            };
            if ($field['type'] === 'list') {
                $rules["settings.$key.*"] = ['array:'.implode(',', $field['columns'])];
                foreach ($field['columns'] as $column) {
                    $rules["settings.$key.*.$column"] = str_ends_with($column, '_image_id')
                        ? ['nullable', 'integer', Rule::exists('media', 'id')]
                        : ['nullable', 'string', 'max:20000'];
                }
            }
        }
        $validated = $request->validate($rules);
        DB::transaction(function () use ($editor, $validated) {
            foreach ($editor['fields'] as $key => $field) {
                $value = $validated['settings'][$key] ?? null;
                if ($field['type'] === 'list') {
                    if ($key === 'home_about_polaroids') {
                        $value = collect($value ?? [])->map(function ($row) {
                            $image = !empty($row['photo_image_id'])
                                ? \App\Models\GalleryImage::find($row['photo_image_id']) : null;
                            if ($image) $row['src'] = $image->getUrl();
                            return $row;
                        })->all();
                    }
                    $value = json_encode(array_values(array_filter($value ?? [],
                        fn ($row) => count(array_filter($row, fn ($cell) => filled($cell))) > 0)), JSON_THROW_ON_ERROR);
                } elseif ($field['type'] === 'checkbox') {
                    $value = $value ? '1' : '0';
                } else {
                    $value = trim((string) $value);
                    // An untouched empty input should continue using the public default.
                    if ($value === '' && !Setting::where('key', $key)->exists()) {
                        continue;
                    }
                }
                $setting = Setting::set($key, $value);
                if ($field['type'] === 'image' || $field['type'] === 'list') {
                    $ids = $field['type'] === 'image' ? array_filter([$value]) : collect(json_decode($value, true))
                        ->flatMap(fn ($row) => collect($row)->filter(fn ($cell, $column) => str_ends_with($column, '_image_id'))->values())
                        ->filter()->all();
                    app(\App\Services\MediaLibraryService::class)->setOrderedUsages($setting, $key, array_values($ids));
                }
            }
        });

        return redirect()->route('admin.landing.edit', $section)->with('success', $editor['title'].' updated.');
    }
}
