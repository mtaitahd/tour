<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MediaController;
use App\Models\GalleryImage;
use App\Models\MediaCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'upload_test',
            'database.connections.upload_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'media-library.image_driver' => 'gd',
            'media-library.image_optimizers' => [],
            'media-library.queue_connection_name' => 'sync',
        ]);
        foreach ([
            '2026_02_06_071553_create_media_table.php',
            '2026_02_08_232331_create_global_media_table.php',
            '2026_06_26_100000_create_media_categories_table.php',
            '2026_06_26_100002_create_media_category_media_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
        Schema::table('media', fn (Blueprint $table) => $table->unsignedBigInteger('uploaded_by')->nullable());
        Storage::fake('public');
        $this->withoutMiddleware();
        $this->actingAs((new User())->forceFill(['id' => 9, 'role' => User::ROLE_SUPER_ADMIN]));
    }

    public function test_real_jpeg_upload_generates_thumbnails_and_keeps_the_chosen_subcategory(): void
    {
        $parent = MediaCategory::create(['name' => 'Kilimanjaro National Park']);
        $child = MediaCategory::create(['name' => 'Third Cave Camp', 'parent_id' => $parent->id]);
        $response = $this->postJson(route('admin.media.upload'), [
            'files' => [UploadedFile::fake()->image('Third Cave Camp Afro (1).JPG', 1200, 800)],
            'category_id' => $child->id,
        ])->assertOk()->assertJsonPath('success', true)->assertJsonCount(1, 'uploaded');
        $image = GalleryImage::findOrFail($response->json('uploaded.0.id'));
        $this->assertSame([$child->id], $image->categories->pluck('id')->all());
        $this->assertSame(9, $image->uploaded_by);
        Storage::disk('public')->assertExists($image->getPathRelativeToRoot());
        $this->assertTrue($image->hasGeneratedConversion('thumb-webp'));
    }

    public function test_legacy_cached_upload_route_returns_json_instead_of_redirecting(): void
    {
        Route::post('/legacy-media-upload-test', [MediaController::class, 'upload']);
        $this->postJson('/legacy-media-upload-test', [
            'files' => [UploadedFile::fake()->image('Camp.jpg', 300, 200)],
        ])->assertOk()->assertJsonPath('success', true)->assertJsonCount(1, 'uploaded');
    }
}
