<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\MediaUsage;
use App\Http\Requests\Media\StoreMediaRequest;

class MediaController extends Controller
{
    public function index()
    {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::query()
                    ->orderByDesc('created_at')
                    ->paginate(24);

        return view('admin.media.index', compact('media'));
    }
    public function upload(StoreMediaRequest $request)
    {
        // Old cached routes may still point here after a deployment. Keep the
        // upload response and category assignment identical to the current route.
        return app(MediaUploadController::class)->store($request);
    }
    public function destroy(Media $media)
    {
        MediaUsage::where('media_id', $media->id)->delete();
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Image removed']);
    }
}
