<?php

namespace Tests\Feature;

use App\Models\ManagerList;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Manager tour lists tick categories from every tour_categories group —
 * country, region, tour type, duration — alongside the legacy listing
 * categories, all into the same category_ids column.
 *
 * The public /collections/{slug} scope matches with AND across groups and OR
 * inside one group: a list ticked "Tanzania" + "Private" shows only Tanzania
 * private tours, "Tanzania" + "Kenya" shows tours from either country, and a
 * list that only ticks legacy listing categories keeps the original any-of
 * behaviour because it is a single group.
 */
class ManagerListCategoryGroupsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<string, TourCategory> */
    private array $categories = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
        $this->categories = [
            'tanzania'       => $this->category('Tanzania', TourCategory::TYPE_COUNTRY),
            'kenya'          => $this->category('Kenya', TourCategory::TYPE_COUNTRY),
            'africa'         => $this->category('Africa', TourCategory::TYPE_REGION),
            'private'        => $this->category('Private', TourCategory::TYPE_TOUR_TYPE),
            'group'          => $this->category('Group', TourCategory::TYPE_TOUR_TYPE),
            'two_days'       => $this->category('2 Days', TourCategory::TYPE_DURATION),
            'tanzania_tours' => $this->category('Tanzania Tours', TourCategory::TYPE_CATEGORY),
            'kilimanjaro'    => $this->category('Kilimanjaro Climbing', TourCategory::TYPE_CATEGORY),
        ];
    }

    private function category(string $name, string $type): TourCategory
    {
        return TourCategory::create(['name' => $name, 'type' => $type]);
    }

    private function tour(string $title, array $categoryIds): TourPackage
    {
        $tour = TourPackage::create([
            'title'          => $title,
            'slug'           => Str::slug($title) . '-' . Str::random(5),
            'status'         => 'published',
            'pricing_source' => 'none',
        ]);
        $tour->categories()->attach($categoryIds);

        return $tour;
    }

    private function list(array $categoryIds): ManagerList
    {
        return ManagerList::create([
            'title'        => 'Collection ' . Str::random(4),
            'slug'         => 'collection-' . Str::random(6),
            'content_type' => 'tours',
            'status'       => 'published',
            'category_ids' => $categoryIds,
        ]);
    }

    /* ── The admin form ───────────────────────────────────────────────── */

    public function test_form_offers_a_checkbox_column_for_every_group(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.manager-lists.create', ['type' => 'tours']))
            ->assertOk()
            ->getContent();

        foreach (['Country', 'Region / Continent', 'Tour Type', 'Duration', 'Listing Categories'] as $heading) {
            $this->assertStringContainsString($heading, $html);
        }
        foreach ($this->categories as $category) {
            $this->assertStringContainsString('name="category_ids[]" value="' . $category->id . '"', $html);
        }
    }

    public function test_store_accepts_ids_from_several_groups_at_once(): void
    {
        $expected = [
            $this->categories['tanzania']->id,
            $this->categories['private']->id,
            $this->categories['tanzania_tours']->id,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.manager-lists.store'), [
                'title'        => 'Tanzania Private',
                'content_type' => 'tours',
                'category_ids' => $expected,
                'status'       => 'published',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.manager-lists.tours'));

        $list = ManagerList::where('title', 'Tanzania Private')->sole();

        $this->assertEqualsCanonicalizing($expected, array_map('intval', $list->category_ids));
    }

    public function test_edit_form_keeps_ticked_group_options_selected(): void
    {
        $list = $this->list([$this->categories['tanzania']->id, $this->categories['private']->id]);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.manager-lists.edit', $list))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="' . $this->categories['tanzania']->id . '" checked', $html);
        $this->assertStringContainsString('value="' . $this->categories['private']->id . '" checked', $html);
        $this->assertStringNotContainsString('value="' . $this->categories['kenya']->id . '" checked', $html);
    }

    /* ── The public collection page ───────────────────────────────────── */

    public function test_public_list_requires_every_selected_group(): void
    {
        $this->tour('Tanzania Private Adventure', [$this->categories['tanzania']->id, $this->categories['private']->id]);
        $this->tour('Tanzania Group Expedition', [$this->categories['tanzania']->id, $this->categories['group']->id]);
        $this->tour('Kenya Private Escape', [$this->categories['kenya']->id, $this->categories['private']->id]);

        $list = $this->list([$this->categories['tanzania']->id, $this->categories['private']->id]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertSee('Tanzania Private Adventure')
            ->assertDontSee('Tanzania Group Expedition')
            ->assertDontSee('Kenya Private Escape');
    }

    public function test_options_inside_one_group_combine_with_any_of_semantics(): void
    {
        $this->tour('Tanzania Classic', [$this->categories['tanzania']->id]);
        $this->tour('Kenya Classic', [$this->categories['kenya']->id]);

        $list = $this->list([$this->categories['tanzania']->id, $this->categories['kenya']->id]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertSee('Tanzania Classic')
            ->assertSee('Kenya Classic');
    }

    public function test_three_groups_narrow_down_to_a_single_tour(): void
    {
        $this->tour('Everything Match', [
            $this->categories['tanzania']->id,
            $this->categories['africa']->id,
            $this->categories['private']->id,
        ]);
        $this->tour('Wrong Duration', [
            $this->categories['tanzania']->id,
            $this->categories['africa']->id,
            $this->categories['group']->id,
        ]);

        $list = $this->list([
            $this->categories['tanzania']->id,
            $this->categories['africa']->id,
            $this->categories['private']->id,
        ]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertSee('Everything Match')
            ->assertDontSee('Wrong Duration');
    }

    public function test_legacy_only_list_keeps_the_original_any_of_behaviour(): void
    {
        $this->tour('Kilimanjaro Climb', [$this->categories['kilimanjaro']->id]);
        $this->tour('Tanzania Tour', [$this->categories['tanzania_tours']->id]);

        $list = $this->list([$this->categories['kilimanjaro']->id, $this->categories['tanzania_tours']->id]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertSee('Kilimanjaro Climb')
            ->assertSee('Tanzania Tour');
    }

    public function test_a_list_without_any_category_shows_no_tours(): void
    {
        $this->tour('Lonely Tour', [$this->categories['tanzania']->id]);

        $list = ManagerList::create([
            'title'        => 'Empty Selection',
            'slug'         => 'empty-selection-' . Str::random(4),
            'content_type' => 'tours',
            'status'       => 'published',
            'category_ids' => null,
        ]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertDontSee('Lonely Tour');
    }

    public function test_draft_and_no_robots_tours_stay_hidden(): void
    {
        $this->tour('Hidden Draft', [$this->categories['tanzania']->id])->update(['status' => 'draft']);
        $this->tour('Hidden No Robots', [$this->categories['tanzania']->id])->update(['no_robots' => true]);
        $this->tour('Visible Tour', [$this->categories['tanzania']->id]);

        $list = $this->list([$this->categories['tanzania']->id]);

        $this->get(route('manager-lists.show', $list->slug))
            ->assertOk()
            ->assertSee('Visible Tour')
            ->assertDontSee('Hidden Draft')
            ->assertDontSee('Hidden No Robots');
    }
}
