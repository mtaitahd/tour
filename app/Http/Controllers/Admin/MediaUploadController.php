<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Models\GlobalMedia;
use App\Services\ImageProcessorService;
use App\Services\MediaLibraryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Dedicated upload endpoint — single, multiple, drag-and-drop, and bulk upload all
 * submit here as a 'files' array (see StoreMediaRequest). Kept separate from
 * MediaLibraryController per the brief's controller split (MediaLibraryController /
 * MediaUploadController / MediaPickerController), even though both ultimately use the
 * same ImageProcessorService underneath.
 */
class MediaUploadController extends Controller
{
    public function __construct(
        protected ImageProcessorService $imageProcessor,
        protected MediaLibraryService $mediaLibrary,
    ) {
    }

    /**
     * Handle an upload. If model_type/model_id/collection are provided, the image is
     * attached directly to that model's collection (e.g. uploading straight into a
     * Destination's gallery from that Destination's own edit page) and a usage record
     * is created immediately. Otherwise, the image is attached to the GlobalMedia
     * singleton's 'general' collection — i.e. uploaded into the library unattached,
     * ready to be picked later via MediaPickerController — matching the existing
     * Admin\MediaController::upload() behavior (see Phase 1 analysis).
     */
    public function store(StoreMediaRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $files = $request->file('files');

        $model = $this->resolveTargetModel($validated);
        $collection = $validated['collection'] ?? 'general';

        [$uploaded, $errors] = $this->imageProcessor->uploadMany($files, $model, $collection);

        foreach ($uploaded as $image) {
            // Record where this image landed, so it shows up in usage tracking even when
            // uploaded straight into a real content model's collection rather than via
            // the picker. Uploads into GlobalMedia's 'general' collection are
            // intentionally NOT recorded as a usage — they're unattached by definition,
            // sitting in the library until something actually uses them.
            if (! $model instanceof GlobalMedia) {
                $this->mediaLibrary->recordUsage($image->id, $model, $collection);
            }

            if (! empty($validated['category_id'])) {
                $image->categories()->sync([$validated['category_id']]);
            }

            if (! empty($validated['tag_ids'])) {
                $image->tags()->sync($validated['tag_ids']);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => count($errors) === 0,
                'uploaded' => collect($uploaded)->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->getUrl(),
                    'thumb_url' => $image->getUrl('thumb-webp') ?: $image->getUrl(),
                    'name' => $image->name,
                    'human_readable_size' => $image->human_readable_size,
                    'show_url' => route('admin.media.show', $image),
                    'edit_url' => route('admin.media.edit', $image),
                    'destroy_url' => route('admin.media.destroy.ajax', $image),
                ]),
                'errors' => $errors,
            ]);
        }

        $message = count($uploaded) . ' file(s) uploaded.';
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' file(s) failed validation.';
        }

        return redirect()
            ->route('admin.media.index')
            ->with(empty($errors) ? 'success' : 'warning', $message);
    }

    /**
     * Resolve which model new uploads should attach to. Mirrors the original
     * Admin\MediaController::upload()'s firstOrCreate-singleton pattern for the
     * unattached case, but delegates to a direct model lookup when one is specified.
     */
    protected function resolveTargetModel(array $validated): Model
    {
        if (! empty($validated['model_type']) && ! empty($validated['model_id'])) {
            $modelClass = MediaLibraryController::ALLOWED_USAGE_MODELS[$validated['model_type']]
                ?? null;

            if ($modelClass) {
                $found = $modelClass::find($validated['model_id']);
                if ($found) {
                    return $found;
                }
            }
        }

        $globalMedia = GlobalMedia::first();
        if (! $globalMedia) {
            $globalMedia = new GlobalMedia();
            $globalMedia->id = 1;
            $globalMedia->save();
        }

        return $globalMedia;
    }
}
