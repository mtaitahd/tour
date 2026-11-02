<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TourTransportPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'tour_presentation', 'database.connections.tour_presentation' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->json('itinerary')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_02_100000_add_transport_type_to_tour_packages.php'))->up();
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    public function test_transport_choice_is_saved_and_selected_on_edit(): void
    {
        $tour = TourPackage::create(['transport_type' => 'bike']);
        $this->assertSame('bike', $tour->fresh()->transport_type);
        $html = view('admin.tour-packages.partials.transport-type', ['tourPackage' => $tour, 'errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $this->assertMatchesRegularExpression('/value="bike"\s+selected/', $html);
        $this->assertStringContainsString('Car', $html);
        $this->assertStringContainsString('Bicycle', $html);
    }

    public function test_all_ten_itinerary_images_are_retrieved_in_the_selected_order(): void
    {
        foreach (range(1, 10) as $id) DB::table('media')->insert(['id' => $id, 'name' => 'Photo ' . $id]);
        $ids = array_reverse(range(1, 10));
        $tour = TourPackage::create(['itinerary' => [['image_ids' => $ids]]]);
        $this->assertSame($ids, $tour->fresh()->dayImages(0)->pluck('id')->all());
    }

    public function test_google_business_link_is_configured_without_removing_later_edits_on_rollback(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_11_02_100001_set_afro_vertex_google_reviews_url.php');
        $migration->up();
        $this->assertSame('https://share.google/QJW9rNYeE8H0WHB3c', \App\Models\Setting::get('google_reviews_url'));
        \App\Models\Setting::set('google_reviews_url', 'https://example.org/updated');
        $migration->down();
        $this->assertSame('https://example.org/updated', \App\Models\Setting::get('google_reviews_url'));
    }

    public function test_transport_photos_use_the_transport_selection_and_its_order(): void
    {
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->integer('media_id');
            $table->string('model_type');
            $table->integer('model_id');
            $table->string('context');
            $table->integer('order');
        });
        $tour = TourPackage::create(['transport_type' => 'car']);
        foreach ([1, 2, 3] as $id) {
            DB::table('media')->insert(['id' => $id, 'name' => 'Image ' . $id]);
            DB::table('media_usages')->insert(['media_id' => $id, 'model_type' => TourPackage::class,
                'model_id' => $tour->id, 'context' => $id === 3 ? 'gallery' : 'safari_car_images', 'order' => 3 - $id]);
        }
        $this->assertSame([2, 1], $tour->safariCarImages()->pluck('id')->all());
    }
}
