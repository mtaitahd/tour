<?php

namespace App\Services;

use App\Models\GalleryImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Handles everything to do with getting an image file onto disk and into the Media
 * Library: validation, the actual Spatie upload call, and deletion.
 *
 * Compression, WebP conversion, and responsive image generation are NOT reimplemented
 * here — Spatie Media Library (via spatie/image, already a dependency) already does all
 * of that through registerMediaConversions(), which every model now defines consistently
 * via App\Models\Concerns\HasStandardMediaConversions (see Phase 3). This service's job
 * is the upload/attach/delete plumbing around that, plus validation rules consistent
 * with what every controller already enforces (Phase 1 analysis: 5MB max,
 * jpeg/png/jpg/webp — no GIF, matching the brief's required formats).
 */
class ImageProcessorService
{
    /** Allowed MIME types, matching the brief's required formats (JPG, JPEG, PNG, WEBP). */
    public const ALLOWED_MIMES = ['jpeg', 'png', 'jpg', 'webp'];

    /** Matches the 5MB ceiling already enforced by every existing controller. */
    public const MAX_FILE_SIZE_KB = 5120;

    /** Minimum width/height in pixels — guards against unusably small uploads. */
    public const MIN_DIMENSION = 100;

    /** Maximum width/height in pixels — guards against unreasonably huge source files. */
    public const MAX_DIMENSION = 8000;

    /**
     * Validate a single uploaded file against the Media Library's rules.
     * Throws ValidationException on failure, same as a Form Request would.
     */
    public function validate(UploadedFile $file): void
    {
        $validator = Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'image',
                    'mimes:' . implode(',', self::ALLOWED_MIMES),
                    'max:' . self::MAX_FILE_SIZE_KB,
                    'dimensions:min_width=' . self::MIN_DIMENSION
                        . ',min_height=' . self::MIN_DIMENSION
                        . ',max_width=' . self::MAX_DIMENSION
                        . ',max_height=' . self::MAX_DIMENSION,
                ],
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate a batch of files at once (multiple/drag-and-drop/bulk upload). Returns
     * the list of validation error messages, keyed by original filename, for any files
     * that failed — callers can decide whether to abort the whole batch or skip just
     * the failed ones (see MediaLibraryController in Phase 5, which skips and reports).
     *
     * @param UploadedFile[] $files
     * @return array<string, array<string>>
     */
    public function validateBatch(array $files): array
    {
        $errors = [];

        foreach ($files as $file) {
            try {
                $this->validate($file);
            } catch (ValidationException $e) {
                $errors[$file->getClientOriginalName()] = $e->validator->errors()->all();
            }
        }

        return $errors;
    }

    /**
     * Upload one file directly onto a HasMedia model's collection. This is the same
     * underlying call every controller already makes (addMedia(...)->toMediaCollection(...)),
     * extracted here so it's one call site instead of four near-identical ones.
     *
     * Returns the resulting GalleryImage (every media row is one — see Phase 3).
     */
    public function upload(UploadedFile $file, HasMedia $model, string $collection): GalleryImage
    {
        $this->validate($file);

        /** @var GalleryImage $media */
        $media = $model->addMedia($file)->toMediaCollection($collection);

        // Record who uploaded this image so the media library (and every gallery it
        // feeds) is verifiably made up of admin-uploaded images. Only set when there
        // is an authenticated user; left null for imports/seeds.
        if (($user = auth()->user())) {
            $media->uploaded_by = $user->getKey();
            $media->save();
        }

        return $media;
    }

    /**
     * Upload multiple files onto a HasMedia model's collection in one call — supports the
     * brief's "Multiple Upload" / "Drag & Drop" / "Bulk Upload" requirements. Files that
     * fail validation are skipped (not thrown), and reported back in the second tuple
     * element so the caller can show a partial-success message.
     *
     * @param UploadedFile[] $files
     * @return array{0: GalleryImage[], 1: array<string, array<string>>}
     */
    public function uploadMany(array $files, HasMedia $model, string $collection): array
    {
        $uploaded = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $uploaded[] = $this->upload($file, $model, $collection);
            } catch (ValidationException $e) {
                $errors[$file->getClientOriginalName()] = $e->validator->errors()->all();
            }
        }

        return [$uploaded, $errors];
    }

    /**
     * Permanently delete a media item — removes the database row, the original file,
     * and every generated conversion/responsive image on disk (Spatie handles all of
     * that cleanup internally on Media::delete()).
     *
     * This does NOT check usage — that safeguard belongs in MediaLibraryService, which
     * knows about MediaUsage and can give a meaningful "still in use" error before ever
     * reaching this method. Calling this directly bypasses that check by design, for
     * cases (like the backfill command) where the caller has already decided deletion
     * is safe.
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }

    /**
     * Regenerate every registered conversion for a single media item. Used by the Phase
     * 4 backfill command to bring older uploads (which predate the current WebP
     * conversion set, or — for BlogPost — predate having any conversions at all) up to
     * the same consistent output as new uploads.
     */
    public function regenerateConversions(Media $media): void
    {
        // --ids is declared as an array option ({--ids=*}) in Spatie's own command, so it
        // must be passed as an actual array here, not a comma-joined string.
        Artisan::call('media-library:regenerate', [
            '--ids' => [(string) $media->id],
            '--force' => true,
        ]);
    }
}
