<?php

namespace App\Http\Requests\Media;

use App\Models\GalleryImage;
use Illuminate\Foundation\Http\FormRequest;

class MediaPickerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Browsing the library to pick an image only requires the same permission as
        // viewing the library itself.
        return $this->user()?->can('viewAny', GalleryImage::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:media_categories,id'],
            'tag_id' => ['nullable', 'integer', 'exists:media_tags,id'],
            'sort' => ['nullable', 'string', 'in:newest,oldest,name,most_used,least_used'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
