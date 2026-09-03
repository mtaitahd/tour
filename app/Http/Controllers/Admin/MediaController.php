<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Http\Request;
use App\Models\GlobalMedia;
use App\Models\MediaUsage;

class MediaController extends Controller
{
    public function index()
    {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
                    ->orderByDesc('created_at')
                    ->paginate(24);

        return view('admin.media.index', compact('media'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'files.*'    => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'collection' => 'nullable|string|in:general,hero,gallery',
        ]);

        // Get or create singleton without mass-assigning id
        $globalMedia = GlobalMedia::first();
        if (!$globalMedia) {
            $globalMedia = new GlobalMedia();
            $globalMedia->id = 1;          // Set manually
            $globalMedia->save();
        }

        foreach ($request->file('files') as $file) {
            $globalMedia->addMedia($file)
                        ->toMediaCollection($request->input('collection', 'general'));
        }

        return redirect()->route('admin.media.index')
                         ->with('success', count($request->file('files')) . ' file(s) uploaded!');
    }
    public function destroy(Media $media)
    {
        MediaUsage::where('media_id', $media->id)->delete();
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Image removed']);
    }
}