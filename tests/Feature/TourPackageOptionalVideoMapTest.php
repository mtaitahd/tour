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
 * Video URL and Embed Map are not collected when creating a tour.
 *
 * Both were removed from the add-tour form. The columns themselves stay on
 * tour_packages and the public tour page still reads them, so nothing about
 * existing tours changed: they can be filled in on the edit form, which still
 * offers Video URL and shows Embed Map for tours that already have one.
 *
 * These tests hold that line: the create form offers neither field, the edit
 * form still manages both without marking them required, and a tour can be
 * created and updated with either or both values present.
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

    public function test_create_form_no_longer_collects_video_url_or_embed_map(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="video_url"', $html);
        $this->assertStringNotContainsString('name="embed_map"', $html);
        $this->assertStringNotContainsString('Video URL', $html);
        $this->assertStringNotContainsString('Embed Map', $html);
    }

    /**
     * The edit form still manages both, and — as it always did — does not claim
     * either is required: the columns are nullable and validate as
     * 'nullable|string'.
     */
    public function test_edit_form_still_manages_both_without_marking_them_required(): void
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

        $this->assertStringContainsString('name="video_url"', $html);
        $this->assertStringContainsString('name="embed_map"', $html);

        $this->assertNotMarkedRequired($html, ['video_url', 'embed_map']);
    }

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

    /* ── Creating without them ────────────────────────────────────────── */

    public function test_a_tour_created_from_the_form_starts_with_neither_value(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.index'));

        $package = TourPackage::where('title', 'Serengeti Migration')->sole();

        $this->assertNull($package->video_url);
        $this->assertNull($package->embed_map);
    }

    /**
     * Nothing enforces the removal at the controller: a direct POST that still
     * carries the values is stored, so any existing import or script that sends
     * them is not broken by the form losing the fields.
     */
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
