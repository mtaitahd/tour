<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Video URL and Embed Map are not collected anywhere on the tour form.
 *
 * Both were removed from the add form and then from the edit form as well.
 * The columns themselves stay on tour_packages and the public tour page still
 * reads them, so nothing about existing tours changed: a tour that already has
 * a video or an embedded map keeps it, because update() falls back to the
 * stored value whenever the request does not carry the key — which is exactly
 * what happens now that neither form submits one.
 *
 * These tests hold that line: neither form offers either field, a tour can be
 * created and updated without them, and direct POSTs / imports that still send
 * the values keep working.
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
     * The edit form no longer offers either field either. A package that
     * already has an embedded map still renders without the input — the stored
     * value is simply left alone on save.
     */
    public function test_edit_form_no_longer_collects_video_url_or_embed_map(): void
    {
        $package = TourPackage::create([
            'title'     => 'Ruaha',
            'slug'      => 'ruaha-' . Str::random(5),
            'embed_map' => '<iframe src="https://maps.google.com/embed"></iframe>',
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-packages.edit', $package))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="video_url"', $html);
        $this->assertStringNotContainsString('name="embed_map"', $html);
        $this->assertStringNotContainsString('Video URL', $html);
        $this->assertStringNotContainsString('Embed Map', $html);
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

    /**
     * What the edit form does now: it posts neither key at all. update() must
     * then keep the stored values rather than nulling them.
     */
    public function test_saving_from_the_form_keeps_existing_video_url_and_embed_map(): void
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
                'title' => 'Ruaha',
                'slug'  => $package->slug,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-packages.index'));

        $package->refresh();

        $this->assertSame('https://www.youtube.com/watch?v=abc123', $package->video_url);
        $this->assertSame('<iframe src="https://maps.google.com/embed"></iframe>', $package->embed_map);
    }

    /**
     * A direct POST that still carries blank values (an import, an old client)
     * nulls the columns — the controller keeps accepting the fields.
     */
    public function test_a_tour_can_still_be_edited_with_explicit_blank_values(): void
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
                // Submitted blank. ConvertEmptyStringsToNull turns the empty
                // string into null before the controller sees it, which is what
                // lands in the nullable column.
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
