<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\MediaLibraryController;
use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Page;
use App\Models\TourPackage;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the ALLOWED_USAGE_MODELS allow-list and its reverse lookup
 * (aliasForModel()) — the security fix from Phase 5 that stops detach() requests from
 * naming an arbitrary PHP class to query against. Both are plain static data/logic
 * with no database dependency, so they're fully testable in isolation here.
 */
class MediaLibraryControllerAliasTest extends TestCase
{
    public function test_allowed_usage_models_only_contains_the_four_content_models(): void
    {
        $this->assertSame(
            [
                'destination' => Destination::class,
                'tour_package' => TourPackage::class,
                'page' => Page::class,
                'blog_post' => BlogPost::class,
            ],
            MediaLibraryController::ALLOWED_USAGE_MODELS
        );
    }

    public function test_alias_for_model_resolves_each_known_class(): void
    {
        $this->assertSame('destination', MediaLibraryController::aliasForModel(Destination::class));
        $this->assertSame('tour_package', MediaLibraryController::aliasForModel(TourPackage::class));
        $this->assertSame('page', MediaLibraryController::aliasForModel(Page::class));
        $this->assertSame('blog_post', MediaLibraryController::aliasForModel(BlogPost::class));
    }

    public function test_alias_for_model_returns_null_for_an_unknown_class(): void
    {
        // This is the actual security property being tested: a class that isn't one
        // of the four content models — including, deliberately, a class that doesn't
        // exist at all — must never resolve to anything detach()/the upload resolver
        // could act on.
        $this->assertNull(MediaLibraryController::aliasForModel(\stdClass::class));
        $this->assertNull(MediaLibraryController::aliasForModel('App\\Models\\User'));
        $this->assertNull(MediaLibraryController::aliasForModel('Totally\\Made\\Up\\ClassName'));
    }
}
