<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // route('media') resolves the {media} route parameter — see routes/web.php.
        // Authorization is against the actual GalleryImage instance, not just the class,
        // so MediaPolicy::update() has the real model available if it ever needs it.
        return $this->user()?->can('update', $this->route('media')) ?? false;
    }

    /**
     * Covers the brief's "Detail View" editable fields: Title, Alt Text, Caption,
     * Description, plus Categories and Tags. File name, size, dimensions, MIME type,
     * and upload date are never editable here — those are Spatie-managed facts about
     * the file itself, not metadata, so they're display-only in the UI.
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:media_categories,id'],

            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:media_tags,id'],
        ];
    }
}
