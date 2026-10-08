<?php

namespace Tests\Feature;

use App\Models\TourCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adding a category asks for exactly two things: the group it belongs to and
 * its name. Slug, status, description and order were removed from the add
 * modal on the Tour Categories page and from the standalone create page —
 * store() already fills them in (slug generated from the name, status active,
 * order 999, no description), so nothing else needs to be collected.
 *
 * The edit form keeps those fields: they were only removed from adding.
 */
class TourCategoryAddFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_add_modal_only_offers_group_and_name(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-categories.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="type"', $html);
        $this->assertStringContainsString('name="name"', $html);

        foreach (['slug', 'status', 'description', 'order'] as $field) {
            $this->assertStringNotContainsString('name="' . $field . '"', $html);
        }

        $this->assertStringNotContainsString('Auto-generated from name if left empty', $html);
        $this->assertStringNotContainsString('Lower numbers appear first in the dropdown', $html);
        $this->assertStringNotContainsString('Optional — not shown publicly yet', $html);
    }

    public function test_standalone_create_page_only_offers_group_and_name(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-categories.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="type"', $html);
        $this->assertStringContainsString('name="name"', $html);

        foreach (['slug', 'status', 'description', 'order'] as $field) {
            $this->assertStringNotContainsString('name="' . $field . '"', $html);
        }
    }

    public function test_storing_with_only_group_and_name_sets_every_default(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tour-categories.store'), [
                'name' => 'Ngorongoro',
                'type' => TourCategory::TYPE_COUNTRY,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tour-categories.index'));

        $category = TourCategory::where('name', 'Ngorongoro')->sole();

        $this->assertSame(TourCategory::TYPE_COUNTRY, $category->type);
        $this->assertSame('ngorongoro', $category->slug);
        $this->assertSame(TourCategory::STATUS_ACTIVE, $category->status);
        $this->assertEquals(999, $category->order);
        $this->assertNull($category->description);
    }

    public function test_edit_form_still_offers_the_full_set_of_fields(): void
    {
        $category = TourCategory::create(['name' => 'Tanzania', 'type' => TourCategory::TYPE_COUNTRY]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.tour-categories.edit', $category))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="slug"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('name="order"', $html);
    }
}
