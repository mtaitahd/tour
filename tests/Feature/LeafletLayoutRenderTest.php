<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the Leaflet layout fix in the admin Tour Package Create + Edit forms:
 * - Leaflet CSS + JS are loaded from the LOCAL vendor copy (not unpkg CDN)
 * - Every itinerary day uses the dedicated .itinerary-location-map-wrapper /
 *   .itinerary-location-map[data-location-map] structure
 * - The locally-hosted Leaflet marker images exist on disk
 */
class LeafletLayoutRenderTest extends TestCase
{
    use RefreshDatabase;

    private function assertLocalLeafletLoaded(string $html): void
    {
        $this->assertStringContainsString('assets/vendor/leaflet/leaflet.css', $html);
        $this->assertStringContainsString('assets/vendor/leaflet/leaflet.js', $html);
        $this->assertStringContainsString('itinerary-location-map-wrapper', $html);
        $this->assertStringContainsString('data-location-map', $html);
        $this->assertStringNotContainsString('unpkg.com/leaflet', $html);
    }

    public function test_create_page_loads_local_leaflet_and_uses_scoped_map_markup(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $html = $this->actingAs($admin)->get(route('admin.tour-packages.create'))->assertOk()->getContent();

        $this->assertLocalLeafletLoaded($html);
        $this->assertStringContainsString('assets/vendor/leaflet/images/marker-icon.png', $html);
    }

    public function test_edit_page_loads_local_leaflet_and_uses_scoped_map_markup(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tour = TourPackage::create([
            'slug' => 'leaflet-tour-2',
            'title' => 'Leaflet Tour',
            'status' => 'published',
            'pricing_source' => 'none',
            'itinerary' => [
                ['title' => 'Day 1', 'lat' => -3.3, 'lng' => 36.6, 'location_name' => 'Arusha', 'accommodations' => [], 'meals' => ''],
            ],
        ]);

        $html = $this->actingAs($admin)->get(route('admin.tour-packages.edit', $tour->id))->assertOk()->getContent();

        $this->assertLocalLeafletLoaded($html);
        $this->assertStringContainsString('assets/vendor/leaflet/images/marker-icon.png', $html);
    }

    public function test_local_leaflet_assets_exist_on_disk(): void
    {
        $this->assertFileExists(base_path('assets/vendor/leaflet/leaflet.css'));
        $this->assertFileExists(base_path('assets/vendor/leaflet/leaflet.js'));
        $this->assertFileExists(base_path('assets/vendor/leaflet/images/marker-icon.png'));
        $this->assertFileExists(base_path('assets/vendor/leaflet/images/marker-icon-2x.png'));
        $this->assertFileExists(base_path('assets/vendor/leaflet/images/marker-shadow.png'));
    }
}
