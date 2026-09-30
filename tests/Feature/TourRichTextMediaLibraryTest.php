<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The Media Library button in the tour form's rich-text editors.
 *
 * The Overview, itinerary day descriptions and extra sections are Quill editors,
 * and their toolbar had no image button at all — so the only way to put an image
 * inside a tour's details was to hand-type <img> HTML, which Quill discards on the
 * next edit because it re-serialises content from its own document model. This
 * covers the wiring that replaced that: a gallery button that opens the same
 * picker modal every <x-media-picker> uses and inserts the chosen image at the
 * cursor.
 *
 * What cannot be asserted here is the click itself — inserting the image is
 * browser-side JavaScript — so these tests pin the pieces it depends on: the
 * button is built into the toolbar, the picker modal it opens exists and is
 * single-select, and it inserts the conversion the picker already resolves rather
 * than a full-size original.
 */
class TourRichTextMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    private function package(): TourPackage
    {
        return TourPackage::create([
            'title' => 'Ruaha',
            'slug'  => 'ruaha-' . Str::random(5),
        ]);
    }

    private function assertGalleryButtonIsWired(string $html, string $form): void
    {
        // The button is added to the toolbar config…
        $this->assertStringContainsString('QuillMedia.galleryButton()', $html, "{$form} does not add the gallery button to its toolbar");
        $this->assertStringContainsString('QuillMedia.attach(', $html, "{$form} does not register its editors with the gallery button");

        // …the shared partial is what supplies both, plus the picker modal it opens.
        $this->assertStringContainsString('window.QuillMedia', $html, "{$form} is missing the QuillMedia helper");

        // The insert uses preview_url, which MediaPickerController maps to the
        // medium-webp conversion — a tour page should not carry full-size originals.
        $this->assertStringContainsString('image.preview_url', $html, "{$form} does not insert the picker's preview (medium) conversion");
        $this->assertStringNotContainsString('image.thumb_url', $html, "{$form} would insert a thumbnail into the page content");

        // A Quill 'image' embed, not pasted markup: markup typed into the source is
        // what used to vanish on the next edit.
        $this->assertStringContainsString("insertEmbed(index, 'image'", $html);
    }

    public function test_create_form_offers_the_media_library_button(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertGalleryButtonIsWired($html, 'the create form');
    }

    public function test_edit_form_offers_the_media_library_button(): void
    {
        $package = $this->package();

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $package))
            ->assertOk()
            ->getContent();

        $this->assertGalleryButtonIsWired($html, 'the edit form');
    }

    /**
     * The button opens a picker modal of its own, in single-select mode: it inserts
     * one image at the cursor, and a tour form full of editors must not render a
     * grid of them.
     */
    public function test_the_button_opens_a_single_select_picker_modal(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="picker-quill-media"', $html);
        $this->assertStringContainsString('data-multiple="0"', $html);

        // It is the existing media picker, so it searches the same library the
        // hero-image picker does — same endpoint, same permissions.
        $this->assertStringContainsString(route('admin.media.picker.search'), $html);
    }

    /**
     * Existing content is loaded into Quill through the clipboard parser, so an
     * image already saved in an overview survives the next edit. Loading it by
     * assigning quill.root.innerHTML left the document model empty, which is what
     * made hand-typed images disappear.
     */
    public function test_existing_editor_content_is_parsed_into_the_document_model(): void
    {
        foreach (['create' => route('admin.tour-packages.create'), 'edit' => route('admin.tour-packages.edit', $this->package())] as $form => $url) {
            $html = $this->actingAs($this->admin)->get($url)->assertOk()->getContent();

            $this->assertStringContainsString(
                'dangerouslyPasteHTML',
                $html,
                "the {$form} form loads editor content without parsing it, so images in it are lost on save"
            );
        }
    }
}
