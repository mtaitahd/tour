<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Video URL and Embed Map are optional on the tour package form.
 *
 * Both columns are nullable in the schema and both have always validated as
 * 'nullable|string' in the controller — but the create form still carried a red
 * required asterisk on each label, telling admins a field was mandatory when
 * nothing enforced it. The edit form never showed those asterisks, so the two
 * forms also disagreed with each other.
 */
class TourPackageOptionalVideoMapTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'          => 'Serengeti Migration',
            'slug'           => 'serengeti-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
        ], $overrides);
    }

    /* ── The form UI ──────────────────────────────────────────────────── */

    /**
     * Walk the rendered form and confirm the given fields carry neither a
     * required marker on their label nor a required attribute on their control.
     */
    private function assertNotMarkedRequired(string $html, array $fields): void
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);

        $xpath = new DOMXPath($document);

        foreach ($fields as $field) {
            $control = $xpath->query("//*[@name='{$field}']")->item(0);

            $this->assertNotNull($control, "no {$field} control was rendered");

            $this->assertFalse(
                $control->hasAttribute('required'),
                "{$field} still has a required attribute"
            );

            // In this Bootstrap horizontal form the label precedes the field's
            // column, so check the whole row the control sits in rather than
            // trying to pair a label with its input.
            $row = $xpath->query("//input[@name='{$field}']/ancestor::div[contains(@class,'row')][1] | //textarea[@name='{$field}']/ancestor::div[contains(@class,'row')][1]")->item(0);

            $this->assertNotNull($row, "{$field} is not inside a form row");

            $required = $xpath->query(".//label//*[contains(@class,'text-danger') and normalize-space(text())='*']", $row);

            $this->assertSame(0, $required->length, "{$field} is still marked required with a red asterisk");
        }
    }

    public function test_create_form_does_not_mark_video_url_or_embed_map_as_required(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertNotMarkedRequired($html, ['video_url', 'embed_map']);
    }

    public function test_edit_form_agrees_with_the_create_form(): void
    {
        // Embed Map is a legacy field on the edit form: its block only renders
        // when the package already has one. Give it one so the field is present
        // to be checked.
        $package = TourPackage::create([
            'title'     => 'Ruaha',
            'slug'      => 'ruaha-' . Str::random(5),
            'embed_map' => '<iframe src="https://maps.google.com/embed"></iframe>',
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $package))
            ->assertOk()
            ->getContent();

        $this->assertNotMarkedRequired($html, ['video_url', 'embed_map']);
    }

    /* ── Submitting without them ──────────────────────────────────────── */

    public function test_a_tour_can_be_created_without_a_video_url_or_embed_map(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.index'));

        $package = TourPackage::where('title', 'Serengeti Migration')->sole();

        $this->assertNull($package->video_url);
        $this->assertNull($package->embed_map);
    }

    public function test_a_tour_can_still_be_created_with_both_values(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload([
                'video_url' => 'https://www.youtube.com/watch?v=abc123',
                'embed_map' => '<iframe src="https://maps.google.com/embed"></iframe>',
            ]))
            ->assertSessionHasNoErrors();

        $package = TourPackage::where('title', 'Serengeti Migration')->sole();

        $this->assertSame('https://www.youtube.com/watch?v=abc123', $package->video_url);
        $this->assertSame('<iframe src="https://maps.google.com/embed"></iframe>', $package->embed_map);
    }

    public function test_a_tour_can_be_edited_without_a_video_url_or_embed_map(): void
    {
        $package = TourPackage::create([
            'title'     => 'Ruaha',
            'slug'      => 'ruaha-' . Str::random(5),
            'video_url' => 'https://www.youtube.com/watch?v=abc123',
            'embed_map' => '<iframe src="https://maps.google.com/embed"></iframe>',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.tour-packages.edit', $package))
            ->put(route('admin.tour-packages.update', $package), $this->payload([
                'title'     => 'Ruaha',
                'slug'      => $package->slug,
                // Submitted blank, the way the form does it when a tour has no
                // video. ConvertEmptyStringsToNull turns the empty string into
                // null before the controller sees it, which is what lands in the
                // nullable column.
                'video_url' => '',
                'embed_map' => '',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.index'));

        $package->refresh();

        $this->assertNull($package->video_url);
        $this->assertNull($package->embed_map);
    }
}
