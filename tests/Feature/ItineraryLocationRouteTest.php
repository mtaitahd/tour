<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Itinerary location route map feature tests.
 *
 * Covers: backend coordinate storage, validation, clearing, public map data,
 * legacy fallbacks, location-search endpoint, and frontend map rendering.
 */
class ItineraryLocationRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    private function tourData(array $overrides = []): array
    {
        return array_merge([
            'title'            => 'Test Tour',
            'slug'             => 'test-tour-' . Str::random(5),
            'status'           => 'published',
            'pricing_source'   => 'none',
        ], $overrides);
    }

    private function dayWithLocation(int $i, ?float $lat = null, ?float $lng = null, string $loc = ''): array
    {
        $day = [
            'title'       => "Day {$i}",
            'description' => "<p>Desc {$i}</p>",
        ];
        if ($lat !== null && $lng !== null) {
            $day['lat']          = $lat;
            $day['lng']          = $lng;
            $day['location_name'] = $loc;
        }
        return $day;
    }

    private function minimalTourPayload(array $days = []): array
    {
        return array_merge($this->tourData(), [
            'itinerary_days' => array_map(fn ($i, $d) => array_merge(['title' => "Day {$i}", 'description' => "<p>X</p>"], $d), array_keys($days), $days) ?: [],
        ]);
    }

    /* ── SAVE + LOAD ──────────────────────────────────────────────────── */

    public function test_store_saves_itinerary_location_coordinates(): void
    {
        $payload = $this->tourData();
        $payload['itinerary_days'] = [
            ['title' => 'Day 1', 'description' => '<p>Machame Gate</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Machame Gate, Tanzania'],
            ['title' => 'Day 2', 'description' => '<p>Machame Camp</p>', 'lat' => -3.1, 'lng' => 37.1, 'location_name' => 'Machame Camp, Tanzania'],
        ];

        $id = $this->actingAs($this->admin)
            ->post(route('admin.tour-packages.store'), $payload)
            ->assertRedirect()
            ->getSession()->get('flash_notification');

        $tour = TourPackage::orderByDesc('id')->first();

        $this->assertCount(2, $tour->itinerary);
        $this->assertEquals(-3.0, $tour->itinerary[0]['lat']);
        $this->assertEquals(37.0, $tour->itinerary[0]['lng']);
        $this->assertEquals('Machame Gate, Tanzania', $tour->itinerary[0]['location_name']);
        $this->assertEquals(-3.1, $tour->itinerary[1]['lat']);
        $this->assertEquals(37.1, $tour->itinerary[1]['lng']);
    }

    public function test_update_saves_itinerary_location_coordinates(): void
    {
        $tour = TourPackage::create($this->tourData());

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), array_merge(
                $this->tourData(['slug' => $tour->slug]),
                ['itinerary_days' => [
                    ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -6.5, 'lng' => 39.3, 'location_name' => 'Zanzibar'],
                ]]
            ));

        $tour->refresh();
        $this->assertEquals(-6.5, $tour->itinerary[0]['lat']);
        $this->assertEquals(39.3, $tour->itinerary[0]['lng']);
        $this->assertEquals('Zanzibar', $tour->itinerary[0]['location_name']);
    }

    /* ── CLEARING ──────────────────────────────────────────────────────── */

    public function test_clearing_itinerary_location_removes_coordinates(): void
    {
        $tour = TourPackage::create($this->tourData());
        $tour->update(['itinerary' => [
            ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
        ]]);

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), array_merge(
                $this->tourData(['slug' => $tour->slug]),
                ['itinerary_days' => [
                    ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => '', 'lng' => '', 'location_name' => ''],
                ]]
            ));

        $tour->refresh();
        $this->assertArrayNotHasKey('lat', $tour->itinerary[0]);
        $this->assertArrayNotHasKey('lng', $tour->itinerary[0]);
    }

    /* ── EMPTY + INVALID ──────────────────────────────────────────────── */

    public function test_empty_location_values_are_stored_as_null(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => '', 'lng' => '', 'location_name' => ''],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertArrayNotHasKey('lat', $tour->itinerary[0]);
    }

    public function test_latitude_without_longitude_is_rejected(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => ''],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertArrayNotHasKey('lat', $tour->itinerary[0]);
    }

    public function test_longitude_without_latitude_is_rejected(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => '', 'lng' => 37.0],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertArrayNotHasKey('lng', $tour->itinerary[0]);
    }

    public function test_invalid_latitude_is_rejected(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => 999, 'lng' => 37.0],
            ],
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.tour-packages.store'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('tour_packages', 0);
    }

    public function test_invalid_longitude_is_rejected(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 999],
            ],
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.tour-packages.store'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('tour_packages', 0);
    }

    public function test_non_numeric_coordinate_is_rejected(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => 'abc', 'lng' => 37.0],
            ],
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.tour-packages.store'), $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('tour_packages', 0);
    }

    /* ── PRESERVATION ──────────────────────────────────────────────────── */

    public function test_existing_itinerary_fields_are_preserved_when_saving_location(): void
    {
        $tour = TourPackage::create($this->tourData());
        $tour->update(['itinerary' => [
            [
                'title'          => 'Day 1',
                'description'    => '<p>Keep me</p>',
                'accommodations' => [['type' => 'GOLD', 'name' => 'Lodge']],
                'meals'          => 'B,L,D',
                'image_ids'      => [1, 2],
            ],
        ]]);

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), array_merge(
                $this->tourData(['slug' => $tour->slug]),
                ['itinerary_days' => [
                    [
                        'title'          => 'Day 1',
                        'description'    => '<p>Keep me</p>',
                        'meals'          => 'B,L,D',
                        'lat'            => -3.0,
                        'lng'            => 37.0,
                        'location_name'  => 'Machame Gate',
                    ],
                ]]
            ));

        $tour->refresh();
        $day = $tour->itinerary[0];
        $this->assertEquals('Keep me', strip_tags($day['description']));
        $this->assertEquals(-3.0, $day['lat']);
        $this->assertEquals(37.0, $day['lng']);
        $this->assertEquals('Machame Gate', $day['location_name']);
        $this->assertEquals('B,L,D', $day['meals']);
    }

    /* ── PUBLIC MAP DATA ────────────────────────────────────────────────── */

    public function test_public_tour_page_passes_route_map_days_with_coordinates(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
                ['title' => 'Day 2', 'description' => '<p>Y</p>', 'lat' => -3.1, 'lng' => 37.1, 'location_name' => 'Camp'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('td-leaflet-route');
        $response->assertSee('data-route=');
        $response->assertSee('leaflet');
    }

    public function test_public_tour_page_skips_days_without_coordinates(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
                ['title' => 'Day 2', 'description' => '<p>Y</p>'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();

        $content = $response->getContent();
        preg_match('/data-route=\'([^\']*)\'/', $content, $m);
        $data = json_decode(str_replace('&quot;', '"', $m[1] ?? '[]'), true);
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]['day']);
    }

    public function test_public_tour_page_one_valid_marker_renders_map(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('td-leaflet-route');
    }

    /* ── LEGACY FALLBACKS ───────────────────────────────────────────────── */

    public function test_legacy_google_map_falls_back_when_no_itinerary_coordinates(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'embed_map' => '<iframe src="https://www.google.com/maps/embed?pb=test"></iframe>',
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('google.com/maps/embed');
        $response->assertDontSee('td-leaflet-route');
    }

    public function test_css_timeline_falls_back_when_no_embed_or_coordinates(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>'],
                ['title' => 'Day 2', 'description' => '<p>Y</p>'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertDontSee('td-leaflet-route');
        $response->assertSee('td-routemap');
    }

    public function test_unsafe_legacy_map_url_does_not_render(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'embed_map' => '<iframe src="javascript:alert(1)"></iframe>',
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertDontSee('javascript:alert');
    }

    public function test_leaflet_itinerary_section_shows_when_coordinates_exist(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0],
                ['title' => 'Day 2', 'description' => '<p>Y</p>'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('td-leaflet-route');
        $response->assertSee('Day by Day');
    }

    /* ── LOCATION SEARCH ────────────────────────────────────────────────── */

    public function test_location_search_requires_authentication(): void
    {
        $this->post(route('admin.location-search'), ['q' => 'Arusha'])
            ->assertRedirect();
    }

    public function test_location_search_requires_minimum_three_characters(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'ab'])
            ->assertStatus(422);
    }

    public function test_location_search_returns_nominatim_results(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Machame Gate, Hai, Kilimanjaro, Tanzania', 'lat' => '-3.0', 'lon' => '37.0'],
            ], 200),
        ]);

        $results = $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'Machame Gate'])
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->json('results');

        $this->assertEqualsWithDelta(-3.0, $results[0]['lat'], 0.001);
        $this->assertEqualsWithDelta(37.0, $results[0]['lng'], 0.001);
        $this->assertEquals('Machame Gate, Hai, Kilimanjaro, Tanzania', $results[0]['display_name']);
    }

    public function test_location_search_normalizes_response(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Arusha, Tanzania', 'lat' => '-3.38', 'lon' => '36.68'],
                ['display_name' => 'Arusha Airport, Tanzania', 'lat' => '-3.35', 'lon' => '36.63'],
            ], 200),
        ]);

        $results = $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'Arusha'])
            ->json('results');

        $this->assertCount(2, $results);
        foreach ($results as $r) {
            $this->assertArrayHasKey('display_name', $r);
            $this->assertArrayHasKey('lat', $r);
            $this->assertArrayHasKey('lng', $r);
        }
    }

    public function test_location_search_caches_identical_queries(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Kilimanjaro, Tanzania', 'lat' => '-3.0', 'lon' => '37.3'],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'Kilimanjaro'])
            ->assertOk();

        $cached = Cache::get('nominatim:search:kilimanjaro');
        $this->assertNotNull($cached);
        $this->assertCount(1, $cached);
    }

    public function test_location_search_handles_upstream_error_gracefully(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response(null, 503),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'Machame'])
            ->assertOk()
            ->assertJsonPath('message', 'No matching locations found');
    }

    public function test_location_search_returns_empty_message_on_no_results(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([], 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'xyznonexistent'])
            ->assertOk()
            ->assertJsonPath('message', 'No matching locations found');
    }

    /* ── COORDINATE COERCION ──────────────────────────────────────────── */

    public function test_float_coordinates_are_preserved_with_precision(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.1234567, 'lng' => 37.9876543, 'location_name' => 'Test'],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertEqualsWithDelta(-3.1234567, $tour->itinerary[0]['lat'], 0.0000001);
        $this->assertEqualsWithDelta(37.9876543, $tour->itinerary[0]['lng'], 0.0000001);
    }

    /* ── LEGACY TOUR WITH NO LOCATION KEYS ──────────────────────────── */

    public function test_legacy_itinerary_without_location_keys_is_valid(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>Legacy</p>', 'meals' => 'B,L'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('Day by Day');
    }

    /* ── EMBED MAP NOT REMOVED ON UPDATE ────────────────────────────── */

    public function test_embed_map_column_is_not_erased(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'embed_map' => '<iframe src="https://www.google.com/maps/embed?pb=old"></iframe>',
        ]));

        $this->actingAs($this->admin)
            ->put(route('admin.tour-packages.update', $tour), array_merge(
                $this->tourData(['slug' => $tour->slug]),
                ['itinerary_days' => [['title' => 'Day 1', 'description' => '<p>X</p>']]]
            ));

        $tour->refresh();
        $this->assertNotNull($tour->embed_map);
        $this->assertStringContainsString('google.com', $tour->embed_map);
    }

    /* ── EDGE CASES ──────────────────────────────────────────────────── */

    public function test_day_without_title_and_without_description_is_skipped(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => '', 'description' => '', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertNull($tour->itinerary);
    }

    public function test_day_without_title_but_with_description_is_saved(): void
    {
        $payload = array_merge($this->tourData(), [
            'itinerary_days' => [
                ['title' => '', 'description' => '<p>Has desc</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
            ],
        ]);

        $this->actingAs($this->admin)->post(route('admin.tour-packages.store'), $payload)->assertRedirect();

        $tour = TourPackage::orderByDesc('id')->first();
        $this->assertCount(1, $tour->itinerary);
        $this->assertEquals(-3.0, $tour->itinerary[0]['lat']);
    }

    /* ── RATE LIMITING ───────────────────────────────────────────────── */

    public function test_cached_identical_query_does_not_make_second_external_request(): void
    {
        $callCount = 0;
        Http::fake(function () use (&$callCount) {
            $callCount++;
            return Http::response([
                ['display_name' => 'Test Place', 'lat' => '-3.0', 'lon' => '37.0'],
            ], 200);
        });

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'TestPlace']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'TestPlace']);

        $this->assertEquals(1, $callCount, 'Second identical request should hit cache, not Nominatim');
    }

    public function test_cached_result_returns_immediately_without_lock(): void
    {
        Cache::put('nominatim:search:cachedplace', [
            ['display_name' => 'Cached Place', 'lat' => -3.0, 'lng' => 37.0],
        ], 3600);

        $callCount = 0;
        Http::fake(function () use (&$callCount) {
            $callCount++;
            return Http::response([], 200);
        });

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'CachedPlace'])
            ->assertOk();

        $this->assertNotEmpty($response->json('results'));
        $this->assertEquals(0, $callCount, 'Cached hit must not trigger any HTTP call');
    }

    public function test_upstream_timeout_returns_safe_response(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
        });

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'TimeoutPlace'])
            ->assertOk()
            ->assertJsonPath('message', 'No matching locations found');
    }

    public function test_upstream_500_returns_safe_response(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response(null, 500),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'ServerError'])
            ->assertOk()
            ->assertJsonPath('message', 'No matching locations found');
    }

    public function test_upstream_invalid_json_returns_safe_response(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response('not json', 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'BadJson'])
            ->assertOk()
            ->assertJsonPath('message', 'No matching locations found');
    }

    public function test_search_uses_configurable_user_agent(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Test', 'lat' => '-3.0', 'lon' => '37.0'],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'UA Test']);

        Http::assertSent(function ($request) {
            $ua = $request->header('User-Agent')[0] ?? '';
            return str_contains($ua, 'AfroVertexTours')
                && str_contains($ua, 'afroverto.com')
                && str_contains($ua, 'info@afroverto.com');
        });
    }

    public function test_lock_release_happens_on_success(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Lock Test', 'lat' => '-3.0', 'lon' => '37.0'],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'LockTest'])
            ->assertOk();

        // If the lock wasn't released, the next request would be stuck.
        // A second request completing proves the lock was released.
        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'LockTest2'])
            ->assertOk();
    }

    public function test_upstream_error_stores_empty_cache(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response(null, 503),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'ErrorCache'])
            ->assertOk();

        $cached = Cache::get('nominatim:search:errorcache');
        $this->assertNotNull($cached, 'Empty result should be cached to prevent retry storms');
        $this->assertEmpty($cached);
    }

    public function test_success_stores_results_in_cache(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['display_name' => 'Success Place', 'lat' => '-3.0', 'lon' => '37.0'],
            ], 200),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.location-search'), ['q' => 'SuccessCache'])
            ->assertOk();

        $cached = Cache::get('nominatim:search:successcache');
        $this->assertNotNull($cached);
        $this->assertCount(1, $cached);
        $this->assertEquals('Success Place', $cached[0]['display_name']);
    }

    /* ── TYPICAL / EDGE CASES ──────────────────────────────────────── */

    public function test_tour_with_only_one_mapped_day_still_renders(): void
    {
        $tour = TourPackage::create(array_merge($this->tourData(), [
            'itinerary' => [
                ['title' => 'Day 1', 'description' => '<p>X</p>', 'lat' => -3.0, 'lng' => 37.0, 'location_name' => 'Gate'],
                ['title' => 'Day 2', 'description' => '<p>Y</p>'],
            ],
        ]));

        $response = $this->get(route('tour.show', $tour->slug));
        $response->assertOk();
        $response->assertSee('td-leaflet-route');
    }
}
