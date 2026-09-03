<?php

namespace App\Http\Requests\Media;

use App\Models\GalleryImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryImage::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('media_tags', 'slug')],
        ];
    }
}
