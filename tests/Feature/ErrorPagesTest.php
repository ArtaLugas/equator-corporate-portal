<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Branded error pages (404/403/419/429/500/503). The views are intentionally
 * database-free, so they render via the view() helper without RefreshDatabase.
 */
class ErrorPagesTest extends TestCase
{
    private const CODES = ['404', '403', '419', '429', '500', '503'];

    public function test_all_error_views_render_with_brand_seo_and_a11y(): void
    {
        foreach (self::CODES as $code) {
            $html = view("errors.{$code}")->render();

            // Content from the lang files.
            $this->assertStringContainsString(__("errors.{$code}.title"), $html, "title for {$code}");
            $this->assertStringContainsString($code, $html, "code shown for {$code}");

            // Both required CTAs are present.
            $this->assertStringContainsString(__('errors.cta_home'), $html, "Back to Home CTA for {$code}");
            $this->assertStringContainsString(__('errors.cta_contact'), $html, "Contact Us CTA for {$code}");

            // SEO: error pages must not be indexed.
            $this->assertStringContainsString('name="robots" content="noindex', $html, "noindex for {$code}");

            // Accessibility: single landmarked heading + lang attribute.
            $this->assertStringContainsString('<h1', $html, "h1 for {$code}");
            $this->assertStringContainsString('role="main"', $html, "main landmark for {$code}");
            $this->assertStringContainsString('<html lang=', $html, "lang attribute for {$code}");
        }
    }

    public function test_error_views_perform_no_database_queries(): void
    {
        // Rendering must never touch the DB — an error page has to survive a
        // database outage. Any query here would throw and fail the test.
        \DB::enableQueryLog();

        foreach (self::CODES as $code) {
            view("errors.{$code}")->render();
        }

        $this->assertSame([], \DB::getQueryLog(), 'Error views must not query the database.');
        \DB::disableQueryLog();
    }

    public function test_unknown_url_returns_branded_404(): void
    {
        $response = $this->get('/__non_existent_path__/error-pages-test');

        $response->assertNotFound();
        $response->assertSee(__('errors.404.title'));
        $response->assertSee(__('errors.cta_home'));
    }
}
