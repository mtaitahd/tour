<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MediaCompressionController;
use App\Models\GalleryImage;
use App\Models\MediaCategory;
use App\Services\MediaCompressionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaCompressionCategoriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'compression_test', 'database.connections.compression_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('media_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('collection_name');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->integer('size');
            $table->string('mime_type');
            $table->unsignedBigInteger('uploaded_by')->nullable();
        });
        Schema::create('media_category_media', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('media_category_id');
            $table->timestamps();
        });
        MediaCategory::create(['name' => 'Serengeti']);
        MediaCategory::create(['name' => 'Empty category']);
        DB::table('media')->insert([
            ['id' => 1, 'name' => 'Lion', 'collection_name' => 'hero', 'model_type' => 'App\\Models\\GlobalMedia', 'model_id' => 1, 'size' => 100, 'mime_type' => 'image/jpeg'],
            ['id' => 2, 'name' => 'Plain', 'collection_name' => 'general', 'model_type' => 'App\\Models\\GlobalMedia', 'model_id' => 1, 'size' => 200, 'mime_type' => 'image/png'],
            ['id' => 3, 'name' => 'Document', 'collection_name' => 'general', 'model_type' => 'App\\Models\\GlobalMedia', 'model_id' => 1, 'size' => 200, 'mime_type' => 'application/pdf'],
        ]);
        GalleryImage::find(1)->categories()->attach(1);
    }

    public function test_summary_includes_custom_empty_and_uncategorized_categories(): void
    {
        $rows = app(MediaCompressionService::class)->summary()->keyBy('id');
        $this->assertSame('Serengeti', $rows['1']['name']);
        $this->assertSame(1, $rows['1']['total']);
        $this->assertSame('Empty category', $rows['2']['name']);
        $this->assertSame(0, $rows['2']['total']);
        $this->assertSame(1, $rows['uncategorized']['total']);
        $this->assertCount(3, $rows);
    }

    public function test_selection_uses_category_assignments_and_all_deduplicates_images(): void
    {
        $controller = new class(app(MediaCompressionService::class)) extends MediaCompressionController {
            public function selected(array $input): array
            {
                return $this->resolveImages(new Request($input))->pluck('id')->all();
            }
        };
        $this->assertSame([1], $controller->selected(['category_id' => '1']));
        $this->assertSame([], $controller->selected(['category_id' => '2']));
        $this->assertSame([2], $controller->selected(['category_id' => 'uncategorized']));
        GalleryImage::find(1)->categories()->attach(2);
        $this->assertSame([1, 2], $controller->selected(['category_id' => 'all']));
    }
}
