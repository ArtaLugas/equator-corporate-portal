<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Privacy Policy, Cookie Policy, and the cookie-consent system.
 */
class PrivacyComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_renders_in_both_locales(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee(__('legal.updated_label'))
            ->assertSee('Your rights')          // a UU PDP / GDPR section
            ->assertSee(route('contact'));       // contact CTA

        $this->get('/id/privacy')
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Hak Anda');
    }

    public function test_cookie_policy_renders_with_cookie_table(): void
    {
        $this->get('/cookies')
            ->assertOk()
            ->assertSee('Cookie Policy')
            ->assertSee('equator_cookie_consent')   // a row in the cookie table
            ->assertSee('Necessary')
            ->assertSee('Cloudflare');

        $this->get('/id/cookies')
            ->assertOk()
            ->assertSee('Kebijakan Cookie');
    }

    public function test_footer_exposes_legal_links_and_consent_banner_is_present(): void
    {
        $response = $this->get('/')->assertOk();

        // Footer legal links.
        $response->assertSee(route('privacy'));
        $response->assertSee(route('cookies'));
        $response->assertSee(__('footer.privacy'));
        $response->assertSee(__('footer.cookies'));

        // Consent banner markup is server-rendered (JS only toggles visibility).
        $response->assertSee(__('cookie_consent.title'));
        $response->assertSee(__('cookie_consent.accept_all'));
        $response->assertSee(__('cookie_consent.reject_optional'));
        $response->assertSee('equator_cookie_consent');
    }

    public function test_contact_form_discloses_data_use_with_policy_link(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee(__('contact.privacy'))
            ->assertSee(route('privacy'));
    }

    public function test_cookie_consent_helper(): void
    {
        // No consent cookie yet → necessary granted, optional denied.
        $this->assertTrue(cookie_consent('necessary'));
        $this->assertFalse(cookie_consent('analytics'));
        $this->assertSame([], cookie_consent());

        // With a stored choice on the request.
        $payload = json_encode(['version' => 1, 'categories' => ['analytics' => true, 'marketing' => false]]);
        $this->app->instance('request', Request::create('/', 'GET', [], ['equator_cookie_consent' => $payload]));

        $this->assertTrue(cookie_consent('analytics'));
        $this->assertFalse(cookie_consent('marketing'));
        $this->assertTrue(cookie_consent('necessary'));  // always on
        $this->assertSame(['analytics' => true, 'marketing' => false], cookie_consent());
    }

    public function test_consent_categories_are_config_driven(): void
    {
        // The banner/payload follows config — adding a category needs no view change.
        $this->assertSame(
            ['necessary', 'analytics', 'marketing', 'preferences'],
            array_keys(config('cookie_consent.categories'))
        );
        $this->assertTrue(config('cookie_consent.categories.necessary.required'));
        $this->assertFalse(config('cookie_consent.categories.analytics.required'));
    }
}
