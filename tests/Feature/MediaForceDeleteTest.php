<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MediaLibraryController;
use App\Models\GalleryImage;
use App\Models\User;
use App\Services\ImageProcessorService;
use App\Services\MediaLibraryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class MediaForceDeleteTest extends TestCase
{
    private function requestFor(User $user, array $input): Request
    {
        $this->actingAs($user);
        $request = Request::create('/admin/media/17/ajax', 'DELETE', $input);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function userWithRole(string $role): User
    {
        return (new User())->forceFill(['id' => 1, 'role' => $role]);
    }

    public function test_normal_delete_keeps_used_images_and_offers_super_admin_override(): void
    {
        $image = (new GalleryImage())->forceFill(['id' => 17]);
        $library = Mockery::mock(MediaLibraryService::class);
        $library->shouldReceive('deleteIfUnused')->once()->andReturn(false);
        $library->shouldReceive('usageLocations')->once()->with(17)->andReturn([]);
        $processor = Mockery::mock(ImageProcessorService::class);
        $processor->shouldNotReceive('delete');
        $controller = new MediaLibraryController($library, $processor);
        $result = $controller->destroyAjax($this->requestFor($this->userWithRole(User::ROLE_SUPER_ADMIN), []), $image);
        $this->assertSame(422, $result->status());
        $this->assertTrue($result->getData(true)['can_force_delete']);
    }

    public function test_confirmed_super_admin_delete_only_deletes_the_requested_image(): void
    {
        $image = (new GalleryImage())->forceFill(['id' => 17]);
        $library = Mockery::mock(MediaLibraryService::class);
        $library->shouldNotReceive('deleteIfUnused');
        $processor = Mockery::mock(ImageProcessorService::class);
        $processor->shouldReceive('delete')->once()->with($image)->andReturn(true);
        $controller = new MediaLibraryController($library, $processor);
        $request = $this->requestFor($this->userWithRole(User::ROLE_SUPER_ADMIN), ['force' => true, 'confirm_used' => true]);
        $this->assertTrue($controller->destroyAjax($request, $image)->getData(true)['success']);
    }

    public function test_force_delete_requires_confirmation(): void
    {
        $processor = Mockery::mock(ImageProcessorService::class);
        $processor->shouldNotReceive('delete');
        $controller = new MediaLibraryController(Mockery::mock(MediaLibraryService::class), $processor);
        $this->expectException(ValidationException::class);
        $controller->destroyAjax(
            $this->requestFor($this->userWithRole(User::ROLE_SUPER_ADMIN), ['force' => true]),
            (new GalleryImage())->forceFill(['id' => 17])
        );
    }

    public function test_editor_cannot_bypass_used_image_protection(): void
    {
        $processor = Mockery::mock(ImageProcessorService::class);
        $processor->shouldNotReceive('delete');
        $controller = new MediaLibraryController(Mockery::mock(MediaLibraryService::class), $processor);
        $this->expectException(AuthorizationException::class);
        $controller->destroyAjax(
            $this->requestFor($this->userWithRole(User::ROLE_PACKAGE_EDITOR), ['force' => true, 'confirm_used' => true]),
            (new GalleryImage())->forceFill(['id' => 17])
        );
    }
}
