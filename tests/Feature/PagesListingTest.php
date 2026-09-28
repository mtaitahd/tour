<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The redesigned /pages listing: what it shows, what it deliberately does not
 * show, and that every filter actually narrows the result set.
 *
 * Content length is chosen to land in each reading-time band on purpose:
 * 500 / 6000 / 20000 characters against thresholds of 3000 and 8000.
 */
class PagesListingTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(string $title, string $slug, int $chars, int $order, string $updatedAt): Page
    {
        $page = Page::create([
            'title' => $title,
            'slug' => $slug,
            'content' => str_repeat('Safari ', (int) ceil($chars / 6)),
            'status' => 'published',
            'order' => $order,
        ]);

        // Query-builder update so Eloquent does not overwrite updated_at with now().
        Page::whereKey($page->id)->update(['updated_at' => $updatedAt]);

        return $page->fresh();
    }

    private function seedPages(): void
    {
        // One page per reading-time band AND per recency band, so every filter
        // has exactly one member and one non-member. "Packing List" is dated
        // 200 days back, i.e. this calendar year but outside the 3-month
        // "recent" window; without that spread the "year" bucket is empty and
        // the recency tests cannot tell a correct bucket from a wrong one.
        //
        // The "safeti" slug is deliberate: the third page sorts before
        // "Packing List" alphabetically but after it by the `order` column, so
        // the two orderings disagree and the sort test means something.
        $this->makePage('Visa Requirements', 'visa-requirements', 500, 5, now()->subDays(2));
        $this->makePage('Packing List', 'packing-list', 6000, 3, now()->subDays(200));
        $this->makePage('Safety Tips', 'safeti-tips', 20000, 1, now()->subDays(400));

        // Excluded: site-information pages and drafts.
        $this->makePage('About Us', 'about-us', 900, 2, now());
        $this->makePage('Private Notes', 'private-notes', 900, 2, now());
        Page::where('slug', 'private-notes')->update(['status' => 'draft']);
    }

    /**
     * The titles of the rendered cards, in the order they appear in the grid.
     *
     * Scoped to the card articles on purpose: the page furniture legitimately
     * mentions excluded pages ("More About Us", a footer link to /pages/about-us),
     * so a page-level assertDontSee would fail for reasons that have nothing to
     * do with what the listing chose to render.
     *
     * @return array<int, string>
     */
    private function cardTitles(string $html): array
    {
        preg_match_all(
            '/<article class="sfb-tour-card sfb-page-card">.*?<h2>(.*?)<\/h2>/s',
            $html,
            $matches
        );

        return array_map(fn (string $t): string => trim(strip_tags($t)), $matches[1] ?? []);
    }

    private function positionOf(string $haystack, string $needle): int
    {
        $at = strpos($haystack, $needle);
        $this->assertNotFalse($at, "Expected the listing HTML to contain \"{$needle}\".");

        return $at;
    }

    public function test_listing_renders_the_default_heading(): void
    {
        $this->seedPages();

        $this->get('/pages')
            ->assertOk()
            ->assertSee('Travel Information');
    }

    public function test_listing_has_no_alphabet_navigation(): void
    {
        $this->seedPages();

        $response = $this->get('/pages')->assertOk();

        $response->assertDontSee('Browse A-Z');
        $response->assertDontSee('Browse A–Z');
        $response->assertDontSee('sfb-az');
    }

    public function test_listing_excludes_site_information_and_drafts(): void
    {
        $this->seedPages();

        $titles = $this->cardTitles($this->get('/pages')->assertOk()->getContent());

        $this->assertEqualsCanonicalizing(
            ['Visa Requirements', 'Packing List', 'Safety Tips'],
            $titles,
            'The grid should contain the three ordinary published pages and nothing else.'
        );
    }

    public function test_result_count_reflects_only_listable_pages(): void
    {
        $this->seedPages();

        $html = $this->get('/pages')->assertOk()->getContent();
        // Collapsed so a Blade-indented <strong>3</strong>\n  pages still matches.
        $flat = preg_replace('/\s+/', ' ', $html);

        $this->assertStringContainsString('1&ndash;3 of 3', $flat, 'The results header should report 1-3 of 3.');
        $this->assertStringContainsString('<strong>3</strong> pages available', $flat);
        $this->assertCount(3, $this->cardTitles($html));
    }

    public function test_legacy_letter_parameter_is_removed_from_the_url(): void
    {
        $this->seedPages();

        $response = $this->get('/pages?letter=A');

        $response->assertRedirect();
        $this->assertStringNotContainsString('letter', $response->headers->get('Location'));
    }

    public function test_reading_time_filter_narrows_the_results(): void
    {
        $this->seedPages();

        $titles = $this->cardTitles($this->get('/pages?read[]=long')->assertOk()->getContent());

        $this->assertSame(['Safety Tips'], $titles);
    }

    public function test_updated_filter_narrows_the_results(): void
    {
        $this->seedPages();

        $this->assertSame(
            ['Visa Requirements'],
            $this->cardTitles($this->get('/pages?updated[]=recent')->assertOk()->getContent()),
            '"Updated in the last 3 months" should match only the 2-day-old page.'
        );

        $this->assertSame(
            ['Packing List'],
            $this->cardTitles($this->get('/pages?updated[]=year')->assertOk()->getContent()),
            '"Earlier this year" should match only the 200-day-old page.'
        );

        $this->assertSame(
            ['Safety Tips'],
            $this->cardTitles($this->get('/pages?updated[]=older')->assertOk()->getContent()),
            '"A year ago or earlier" should match only the 400-day-old page.'
        );
    }

    public function test_two_boxes_in_the_same_group_widen_the_results(): void
    {
        $this->seedPages();

        $titles = $this->cardTitles(
            $this->get('/pages?read[]=short&read[]=long')->assertOk()->getContent()
        );

        $this->assertEqualsCanonicalizing(['Visa Requirements', 'Safety Tips'], $titles);
    }

    public function test_filters_of_different_groups_combine(): void
    {
        $this->seedPages();

        // Long AND recent matches nothing: the only long page is 400 days old.
        $html = $this->get('/pages?read[]=long&updated[]=recent')->assertOk()->getContent();

        $this->assertSame([], $this->cardTitles($html));
        $this->assertStringContainsString('of 0', $html);
    }

    public function test_each_sort_puts_a_different_page_first(): void
    {
        $this->seedPages();

        $alpha = $this->get('/pages?sort=alpha')->assertOk()->getContent();
        $this->assertLessThan(
            $this->positionOf($alpha, 'Safety Tips'),
            $this->positionOf($alpha, 'Packing List'),
            'Alphabetical order should list "Packing List" before "Safety Tips".'
        );

        $featured = $this->get('/pages?sort=featured')->assertOk()->getContent();
        $this->assertLessThan(
            $this->positionOf($featured, 'Packing List'),
            $this->positionOf($featured, 'Safety Tips'),
            'Featured order should list "Safety Tips" (order 1) first.'
        );

        $recent = $this->get('/pages?sort=recent')->assertOk()->getContent();
        $this->assertLessThan(
            $this->positionOf($recent, 'Packing List'),
            $this->positionOf($recent, 'Visa Requirements'),
            'Recently updated should list "Visa Requirements" (2 days old) first.'
        );
    }

    public function test_zero_count_options_are_hidden(): void
    {
        $this->seedPages();

        // Unfiltered, all three bands have a member, so all three are offered.
        $this->get('/pages')->assertOk()
            ->assertSee('Quick read')
            ->assertSee('In-depth')
            ->assertSee('Deep dive');

        // Searching for one page narrows the scope the reading-time counts are
        // computed on, leaving two bands with nothing in them. Offering those
        // would be a dead end: ticking either returns an empty page.
        $response = $this->get('/pages?search=Packing')->assertOk();
        $response->assertSee('In-depth');
        $response->assertDontSee('Quick read');
        $response->assertDontSee('Deep dive');
    }

    public function test_a_selected_option_with_no_results_stays_visible(): void
    {
        $this->seedPages();

        // "Quick read" has no match inside the search results, but it is the
        // active filter, so it still has to render — otherwise the reader is
        // stuck on an empty page with no visible way to untick the box.
        $response = $this->get('/pages?search=Packing&read[]=short')->assertOk();

        $response->assertSee('Quick read');
        $response->assertSee('checked', false);
    }

    public function test_active_filters_are_shown_and_can_be_removed(): void
    {
        $this->seedPages();

        $html = $this->get('/pages?read[]=long')->assertOk()->getContent();

        $this->assertStringContainsString('Selected filters:', $html);

        // Every "remove" chip and the "Clear all filters" link must land on the
        // unfiltered listing — with a single value ticked, removing it empties
        // the group, so the parameter is dropped rather than sent as an empty
        // array that the controller would have to special-case.
        preg_match_all('/(?:sfb-selected-chip|sfb-pages-filters__actions)[^>]*href="([^"]*)"/', $html, $matches);
        $this->assertNotEmpty($matches[1] ?? [], 'Expected a way to remove an active filter.');

        foreach ($matches[1] as $href) {
            $this->assertStringNotContainsString('read', $href, "Chip URL still filters: {$href}");
        }

        $this->assertContains(route('pages.index'), $matches[1], 'Expected a link back to the unfiltered listing.');
    }

    public function test_search_still_narrows_the_results(): void
    {
        $this->seedPages();

        $this->assertSame(
            ['Packing List'],
            $this->cardTitles($this->get('/pages?search=Packing')->assertOk()->getContent())
        );
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->seedPages();

        $this->get('/pages?read[]=nonsense')->assertRedirect();
        $this->get('/pages?sort=nonsense')->assertRedirect();
    }

    public function test_heading_can_be_overridden_without_a_code_change(): void
    {
        $this->seedPages();

        Setting::set('pages_listing_title', 'Visa Desk');

        $response = $this->get('/pages')->assertOk();

        $response->assertSee('Visa Desk');
        $response->assertDontSee('Travel Information');
    }

    public function test_tours_heading_can_be_overridden_too(): void
    {
        Setting::set('tours_listing_title', 'Signature Journeys');

        $this->get('/tours')->assertOk()->assertSee('Signature Journeys');
    }

    public function test_settings_stored_as_html_are_flattened_before_display(): void
    {
        Setting::set('tours_listing_intro', '<p>Curated <strong>safari</strong> itineraries.</p>');

        $this->get('/tours')->assertOk()
            ->assertSee('Curated safari itineraries')
            ->assertDontSee('<strong>safari</strong>');
    }
}

/**
 * Admin → Website Content → Listing Titles.
 */
class ListingTitlesAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_screen_shows_the_shipped_default_before_anything_is_saved(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.listing-titles.index'))
            ->assertOk()
            ->assertSee('Travel Information')
            ->assertSee('Default');
    }

    public function test_saving_a_heading_takes_effect_on_the_public_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.listing-titles.update'), [
                'key' => 'pages_listing_title',
                'value_pages_listing_title' => 'Visa Desk',
            ])
            ->assertRedirect(route('admin.listing-titles.index'))
            ->assertSessionHas('success');

        $this->assertSame('Visa Desk', Setting::get('pages_listing_title'));

        $this->get('/pages')->assertOk()->assertSee('Visa Desk');
    }

    public function test_an_emptied_field_is_a_validation_error_rather_than_a_blank_heading(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.listing-titles.index'))
            ->post(route('admin.listing-titles.update'), [
                'key' => 'pages_listing_title',
                'value_pages_listing_title' => '   ',
            ])
            ->assertRedirect(route('admin.listing-titles.index'))
            ->assertSessionHasErrors('value_pages_listing_title');

        $this->assertNull(Setting::get('pages_listing_title'));
    }

    public function test_a_key_outside_the_catalogue_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.listing-titles.index'))
            ->post(route('admin.listing-titles.update'), [
                'key' => 'site_name',
                'value_site_name' => 'Hijacked',
            ])
            ->assertSessionHasErrors('key');

        $this->assertNull(Setting::get('site_name'));
    }

    public function test_reset_removes_the_override_and_restores_the_default(): void
    {
        $admin = $this->admin();
        Setting::set('pages_listing_title', 'Visa Desk');

        $this->actingAs($admin)
            ->delete(route('admin.listing-titles.destroy', 'pages_listing_title'))
            ->assertRedirect(route('admin.listing-titles.index'));

        $this->assertNull(Setting::get('pages_listing_title'));

        $this->get('/pages')->assertOk()->assertSee('Travel Information');
    }

    public function test_a_key_outside_the_catalogue_cannot_be_deleted(): void
    {
        Setting::set('site_name', 'Afro Vertex');

        $this->actingAs($this->admin())
            ->delete(route('admin.listing-titles.destroy', 'site_name'))
            ->assertNotFound();

        $this->assertSame('Afro Vertex', Setting::get('site_name'));
    }

    public function test_a_user_without_the_settings_permission_cannot_reach_the_screen(): void
    {
        $this->actingAs(User::factory()->noRole()->create())
            ->get(route('admin.listing-titles.index'))
            ->assertForbidden();
    }
}
