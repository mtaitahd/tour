<?php

namespace Tests\Unit;

use App\Services\ImageProcessorService;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for ImageProcessorService's validation constants — no database,
 * no Laravel application boot, no filesystem access. These run in complete isolation
 * (see phpunit.xml: bootstrap is vendor/autoload.php only, not Laravel's full app
 * bootstrap), which is genuinely all that's needed here, since these constants are
 * plain PHP values with no dependency on anything else.
 *
 * What this does NOT cover: actual file upload/validation behavior (needs a real
 * UploadedFile and Laravel's validator), attaching media to a model (needs a database),
 * or conversion regeneration (needs the filesystem and Spatie's conversion pipeline).
 * Those require a real Laravel application with a database connection — see the Phase
 * 10 Testing Checklist for the manual/feature-test coverage of that behavior, which
 * could not be executed in this sandbox (no MySQL server available — confirmed, not
 * assumed, while building this phase).
 */
class ImageProcessorServiceTest extends TestCase
{
    public function test_allowed_mimes_matches_the_brief_exactly(): void
    {
        // The brief specifies JPG, JPEG, PNG, WEBP — no GIF, even though a few older
        // controllers elsewhere in the app inconsistently allowed it (see Phase 1
        // analysis report). This locks that decision in so it can't silently drift.
        $this->assertSame(['jpeg', 'png', 'jpg', 'webp'], ImageProcessorService::ALLOWED_MIMES);
        $this->assertNotContains('gif', ImageProcessorService::ALLOWED_MIMES);
    }

    public function test_max_file_size_matches_existing_controller_convention(): void
    {
        // 5MB, matching what every existing controller already enforced (Phase 1
        // analysis: grepped every admin controller's validation rules before choosing
        // this number, rather than inventing a new ceiling).
        $this->assertSame(5120, ImageProcessorService::MAX_FILE_SIZE_KB);
    }

    public function test_dimension_bounds_are_sane(): void
    {
        $this->assertGreaterThan(0, ImageProcessorService::MIN_DIMENSION);
        $this->assertGreaterThan(
            ImageProcessorService::MIN_DIMENSION,
            ImageProcessorService::MAX_DIMENSION
        );
    }
}
