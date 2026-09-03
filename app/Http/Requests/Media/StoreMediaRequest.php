<?php

namespace App\Http\Requests\Media;

use App\Models\GalleryImage;
use App\Services\ImageProcessorService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryImage::class) ?? false;
    }

    /**
     * Validation rules mirror ImageProcessorService's own constants exactly, so the
     * error a person sees on submit and the rule the service itself enforces can never
     * drift apart. Supports single, multiple, drag-and-drop, and bulk upload — all of
     * which submit through the same 'files' array field.
     */
    public function rules(): array
    {
        $mimes = implode(',', ImageProcessorService::ALLOWED_MIMES);
        $maxKb = ImageProcessorService::MAX_FILE_SIZE_KB;
        $minDim = ImageProcessorService::MIN_DIMENSION;
        $maxDim = ImageProcessorService::MAX_DIMENSION;

        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                'image',
                "mimes:{$mimes}",
                "max:{$maxKb}",
                "dimensions:min_width={$minDim},min_height={$minDim},max_width={$maxDim},max_height={$maxDim}",
            ],

            // Optional: assign straight to a collection on an existing model (e.g.
            // uploading directly into a Destination's gallery from that model's own
            // edit page, rather than via the standalone Media Library).
            'model_type' => ['nullable', 'string'],
            'model_id' => ['nullable', 'integer', 'required_with:model_type'],
            'collection' => ['nullable', 'string', 'max:255'],

            // Optional: assign to a category/tags at upload time, so the person doesn't
            // have to upload then immediately edit.
            'category_id' => ['nullable', 'integer', 'exists:media_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:media_tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Please select at least one image to upload.',
            'files.*.image' => 'Each file must be an image.',
            'files.*.mimes' => 'Images must be JPG, JPEG, PNG, or WEBP.',
            'files.*.max' => 'Each image must be 5MB or smaller.',
            'files.*.dimensions' => 'Image dimensions are outside the allowed range.',
        ];
    }
}
