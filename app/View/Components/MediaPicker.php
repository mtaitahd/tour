<?php

namespace App\View\Components;

use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-media-picker name="hero_image_id" /> — the brief's named reusable component.
 *
 * Renders a hidden input (or several, for multi-select) holding the selected media
 * id(s), a thumbnail preview of whatever's currently selected, a button that opens the
 * picker modal (see modal-picker.blade.php), and a small inline script that listens for
 * the modal's 'media-picker:selected' event and updates both the input and the preview.
 *
 * Usage:
 *   <x-media-picker name="hero_image_id" :selected="$destination->hero_image_id" />
 *   <x-media-picker name="gallery_image_ids" multiple :selected="$tour->galleryImageIds()" />
 */
class MediaPicker extends Component
{
    public string $pickerId;

    public ?GalleryImage $selectedImage = null;

    /** @var GalleryImage[] */
    public array $selectedImages = [];

    public function __construct(
        MediaLibraryService $mediaLibrary,
        public string $name,
        public bool $multiple = false,
        public string|array|null $selected = null,
        public string $label = 'Select Image',
    ) {
        // A stable, unique id per picker instance on the page, derived from the field
        // name — lets multiple pickers (e.g. hero + gallery on the same form) coexist
        // without colliding on modal ids or JS event targets.
        $this->pickerId = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);

        if ($this->multiple) {
            $ids = array_values(array_filter((array) $this->selected));
            if (! empty($ids)) {
                // whereIn() does not guarantee result order matches $ids, so the
                // fetched images are re-ordered in PHP to match the order they were
                // passed in — the same technique used by
                // MediaLibraryService::orderedImagesFor(), since both need to render
                // a gallery in a specific, caller-chosen sequence, not database order.
                $fetched = $mediaLibrary->query()->whereIn('id', $ids)->get()->keyBy('id');
                $this->selectedImages = collect($ids)
                    ->map(fn ($id) => $fetched->get($id))
                    ->filter()
                    ->values()
                    ->all();
            }
        } elseif (! empty($this->selected)) {
            $this->selectedImage = $mediaLibrary->query()->find($this->selected);
        }
    }

    public function render(): View
    {
        return view('components.media-picker');
    }
}
