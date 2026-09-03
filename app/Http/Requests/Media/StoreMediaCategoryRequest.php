<?php

namespace App\Http\Requests\Media;

use App\Models\GalleryImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryImage::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('media_categories', 'slug')],

            // Nullable: a category with no parent is a root category (e.g. "Destinations").
            // 'not_in' guards against a category being made its own parent on update.
            'parent_id' => [
                'nullable',
                'integer',
                'exists:media_categories,id',
                Rule::notIn([$this->route('category')?->id]),
            ],

            'description' => ['nullable', 'string', 'max:1000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
