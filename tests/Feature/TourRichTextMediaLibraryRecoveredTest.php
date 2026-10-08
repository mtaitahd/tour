<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Rich text on the tour form: the Overview is the admin's notepad, and both it and
 * the remaining Quill editors can take images from the Media Library.
 *
 * The Overview used to be a Quill editor with a much smaller toolbar than the
 * destination form's Description, even though the layout already ships a full
 * TinyMCE notepad for exactly this. It is now that notepad, verbatim, with a
 * Media Library button added to its toolbar. The smaller Quill fields (itinerary
 * day descriptions, extra sections) are left as they are, and keep their own
 * gallery button.
 *
 * What cannot be asserted here is the clicking itself — inserting an image is
 * browser-side JavaScript — so these tests pin the pieces it depends on: which
 * editor the Overview gets, which tools its toolbar carries, and that the button
 * opens a picker that inserts the library image rather than a thumbnail.
 */
class TourRichTextMediaLibraryRecoveredTest extends TestCase
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

    /**
     * @return array<string, string> form name => rendered create/edit form
     */
    private function bothForms(): array
    {
        $forms = [
            'create' => $this->actingAs($this->admin)
                ->get(route('admin.tour-packages.create'))
                ->assertOk()
                ->getContent(),
            'edit' => $this->actingAs($this->admin)
                ->get(route('admin.tour-packages.edit', $this->package()))
                ->assertOk()
                ->getContent(),
        ];

        return $forms;
    }

    private function overviewTag(string $html): string
    {
        $this->assertSame(
            1,
            preg_match('/<textarea[^>]*name="overview"[^>]*>/', $html, $matches),
            'the Overview is not a single form field'
        );

        return $matches[0];
    }

    public function test_overview_is_the_full_notepad_on_both_forms(): void
    {
        foreach ($this->bothForms() as $form => $html) {
            $tag = $this->overviewTag($html);

            $this->assertStringContainsString('id="tour-overview"', $tag, "the {$form} form's Overview lost its id");

            // Initialised by the form itself, not by the layout's global
            // .tinymce-editor pass — that is what lets its toolbar differ.
            $this->assertStringNotContainsString(
                'tinymce-editor',
                $tag,
                "the {$form} form's Overview is claimed by the global notepad init, so its toolbar cannot be extended"
            );

            // The notepad itself: the same options the layout gives every
            // .tinymce-editor, so the Overview is no longer a cut-down editor.
            $this->assertStringContainsString("selector: '#tour-overview'", $html, "the {$form} form does not initialise the Overview as a notepad");
            $this->assertStringContainsString("menubar: 'file edit view insert format tools table help'", $html);
            $this->assertStringContainsString('link image media table', $html, "the {$form} form's notepad lost its image/media/table tools");
            $this->assertStringContainsString('alignleft aligncenter alignright alignjustify', $html);
            $this->assertStringContainsString('forecolor backcolor', $html);

            // And it still saves without JavaScript, which the old hidden-input
            // arrangement also did but only because Quill mirrored it.
            $this->assertStringContainsString('editor.save()', $html);
        }
    }

    public function test_overview_notepad_offers_the_media_library(): void
    {
        foreach ($this->bothForms() as $form => $html) {
            $this->assertStringContainsString(
                "overviewConfig.toolbar + ' | mediagallery'",
                $html,
                "the {$form} form's Overview toolbar has no Media Library button"
            );
            $this->assertStringContainsString("addButton('mediagallery'", $html);

            // A picker modal of its own, single-select, under its own id so it
            // cannot collide with the Quill editors' picker on the same page.
            $this->assertStringContainsString('id="picker-overview-media"', $html);
            $this->assertStringContainsString('editor.insertContent(', $html, "the {$form} form's notepad button inserts nothing");

            // The medium-webp conversion, not a grid thumbnail: preview_url is what
            // MediaPickerController resolves to medium-webp (getUrl('medium-webp')
            // ?: getUrl()), and the picker's own grid legitimately uses thumb_url.
            $this->assertStringContainsString('image.preview_url', $html);
        }
    }

    /**
     * The smaller Quill fields (itinerary days, extra sections) are untouched, and
     * still get a gallery button of their own.
     */
    public function test_the_quill_fields_still_offer_the_media_library(): void
    {
        foreach ($this->bothForms() as $form => $html) {
            $this->assertStringContainsString('QuillMedia.galleryButton()', $html, "the {$form} form does not add the gallery button to its Quill toolbars");
            $this->assertStringContainsString('QuillMedia.attach(', $html, "the {$form} form does not register its Quill editors with the gallery button");
            $this->assertStringContainsString('window.QuillMedia', $html);
            $this->assertStringContainsString('id="picker-quill-media"', $html);
            $this->assertStringContainsString('data-multiple="0"', $html);

            // A Quill 'image' embed, not pasted markup: markup typed into the
            // source is what used to vanish on the next edit.
            $this->assertStringContainsString("insertEmbed(index, 'image'", $html);
            $this->assertStringContainsString('image.preview_url', $html);

            // Both pickers hit the same library the hero-image picker does, so the
            // form needs the media permission it already needed.
            $this->assertStringContainsString(route('admin.media.picker.search'), $html);
        }
    }

    /**
     * Existing Quill content is loaded through the clipboard parser, so an image
     * already saved in an itinerary day or extra section survives the next edit.
     * Loading it by assigning quill.root.innerHTML left the document model empty,
     * which is what made hand-typed images disappear.
     */
    public function test_existing_quill_content_is_parsed_into_the_document_model(): void
    {
        foreach ($this->bothForms() as $form => $html) {
            $this->assertStringContainsString(
                'dangerouslyPasteHTML',
                $html,
                "the {$form} form loads Quill content without parsing it, so images in it are lost on save"
            );
        }
    }

    public function test_overview_content_is_still_accepted_by_the_controller(): void
    {
        $html = '<p>Day one in the Serengeti.</p><img src="' . asset('storage/media/demo-medium.webp') . '" alt="Serengeti plains">';

        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), [
                'title'          => 'Serengeti Migration',
                'slug'           => 'serengeti-' . Str::random(5),
                'status'         => 'published',
                'pricing_source' => 'none',
                'overview'       => $html,
            ])
            ->assertRedirect();

        $stored = TourPackage::where('slug', 'like', 'serengeti-%')->latest('id')->first();

        $this->assertSame($html, $stored->overview, 'the Overview was not stored verbatim');
    }
}
