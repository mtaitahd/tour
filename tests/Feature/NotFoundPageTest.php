<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cover for the public 404 page (resources/views/errors/404.blade.php).
 *
 * Laravel renders that view automatically for any NotFoundHttpException, so these
 * tests pin the behaviour that matters: a missing public URL must return a real 404
 * status (not a soft 200, which would tell search engines the page exists) and must
 * still offer the visitor a way back to the site.
 */
class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_missing_public_url_returns_a_404_status(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
    }

    public function test_the_404_page_offers_a_link_back_to_the_site(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('Back to Home', false);
        $response->assertSee(route('home'), false);
    }

    public function test_the_404_page_is_rendered_in_the_public_layout(): void
    {
        $html = $this->get('/this-page-does-not-exist')->getContent();

        // Header and footer come from frontend.layouts.app — if the 404 could not
        // extend the public layout, a visitor would lose the site navigation.
        $this->assertStringContainsString('</html>', $html);
        $this->assertStringContainsString('404', $html);

        // It must not be indexable.
        $this->assertStringContainsString('noindex', $html);
    }
}
