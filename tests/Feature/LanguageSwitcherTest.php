<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards locale_url() — the language switcher + hreflang links. Regression test
 * for the bug where, after the default-locale public routes were made UNNAMED
 * (to keep route:cache valid), locale_url() fell back to the homepage on every
 * English page, so the switcher never reached the Indonesian version of the
 * current page. locale_url() is now path-based and must not depend on route names.
 */
class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_page_links_to_indonesian_version_of_same_page(): void
    {
        $res = $this->get('/services');
        $res->assertOk();

        // hreflang + switcher must point to /id/services — NOT the homepage.
        $res->assertSee(url('/id/services'), false);
    }

    public function test_indonesian_page_links_back_to_default(): void
    {
        $res = $this->get('/id/services');
        $res->assertOk();

        $res->assertSee(url('/services'), false);
    }

    public function test_locale_url_helper_swaps_prefix_regardless_of_route_name(): void
    {
        // Drive a real request so request()->path() is populated, then assert the
        // helper builds the right URLs from the path (no route name involved).
        $this->get('/about'); // default-locale, UNNAMED route

        $this->assertSame(url('/id/about'), locale_url('id'));
        $this->assertSame(url('/about'), locale_url('en'));
    }
}
