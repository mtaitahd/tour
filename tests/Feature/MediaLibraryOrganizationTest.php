<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\MediaCategory;
use App\Services\MediaLibraryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaLibraryOrganizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'organization_test', 'database.connections.organization_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('media_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_type')->default('App\\Models\\GlobalMedia');
            $table->integer('model_id')->default(1);
            $table->timestamps();
        });
        Schema::create('media_category_media', function (Blueprint $table) {
            $table->integer('media_id');
            $table->integer('media_category_id');
            $table->timestamps();
        });
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->integer('media_id');
        });
    }

    private function nestedCategories(): array
    {
        $categories = [];
        foreach (['National Park', 'Mount Meru', 'Mount Meru Summit', 'Camps', 'Deep camp'] as $name) {
            $categories[] = MediaCategory::create([
                'name' => $name,
                'parent_id' => $categories ? end($categories)->id : null,
            ]);
        }

        return $categories;
    }

    public function test_all_nested_levels_appear_in_upload_and_picker_without_extra_queries(): void
    {
        $categories = $this->nestedCategories();
        DB::enableQueryLog();
        $tree = app(MediaLibraryService::class)->categoryTree();
        $queries = count(DB::getQueryLog());
        $options = view('admin.media.partials.category-options', ['categories' => $tree])->render();
        $this->assertStringContainsString('National Park &gt; Mount Meru &gt; Mount Meru Summit &gt; Camps &gt; Deep camp', $options);
        $this->assertSame(5, substr_count($options, '<option'));
        $this->assertSame($queries, count(DB::getQueryLog()));
        $this->assertSame(1, $queries);
        $this->assertStringContainsString('Deep camp', $tree->toJson());
        $upload = view('admin.media.partials.upload-modal', ['categories' => $tree])->render();
        $picker = view('admin.media.partials.modal-picker', [
            'categories' => $tree, 'pickerId' => 'organization', 'inputName' => 'image_id',
            'multiple' => false, 'selectedIds' => [],
        ])->render();
        $this->assertStringContainsString('value="' . end($categories)->id . '"', $upload);
        $this->assertStringContainsString('Deep camp', $picker);
    }

    public function test_parent_browsing_includes_deep_images_once_and_excludes_other_categories(): void
    {
        $categories = $this->nestedCategories();
        $other = MediaCategory::create(['name' => 'Other']);
        DB::table('media')->insert([['id' => 1, 'name' => 'Camp'], ['id' => 2, 'name' => 'Other']]);
        GalleryImage::find(1)->categories()->attach([$categories[0]->id, end($categories)->id]);
        GalleryImage::find(2)->categories()->attach($other->id);
        $service = app(MediaLibraryService::class);
        $this->assertSame([1], $service->query(['category_id' => $categories[0]->id])->pluck('id')->all());
        $this->assertSame([1], $service->query(['category_id' => end($categories)->id])->pluck('id')->all());
        $this->assertSame([2], $service->query(['category_id' => $other->id])->pluck('id')->all());
    }

    public function test_fifty_images_remain_accessible_across_pages_with_stable_ordering(): void
    {
        for ($id = 1; $id <= 50; $id++) {
            DB::table('media')->insert(['id' => $id, 'name' => 'Image ' . $id, 'created_at' => '2026-10-26 08:00:00']);
        }
        $query = app(MediaLibraryService::class)->query()->setEagerLoads([]);
        $first = (clone $query)->paginate(24, ['*'], 'page', 1);
        $last = (clone $query)->paginate(24, ['*'], 'page', 3);
        $this->assertSame(50, $first->total());
        $this->assertSame(3, $first->lastPage());
        $this->assertSame(24, $first->count());
        $this->assertSame([2, 1], $last->pluck('id')->all());
    }

    public function test_library_view_renders_compact_category_and_page_controls(): void
    {
        $this->nestedCategories();
        $this->app['request']->query->set('modal', '1');
        $html = view('admin.media.index', [
            'media' => new LengthAwarePaginator([], 50, 24, 1, ['path' => '/admin/media']),
            'categories' => app(MediaLibraryService::class)->categoryTree(),
            'tags' => collect(), 'filters' => [],
            'stats' => ['total_images' => 50, 'total_size_bytes' => 0, 'unused_count' => 50],
        ])->render();
        $this->assertStringContainsString('id="category-search"', $html);
        $this->assertStringContainsString('id="media-per-page"', $html);
        $this->assertStringContainsString('Image pages at top', $html);
        $this->assertStringContainsString('max-height: 420px', $html);
        $this->assertStringContainsString('Deep camp', $html);
        $this->assertStringContainsString('media-library-upload.js', $html);
    }
}
