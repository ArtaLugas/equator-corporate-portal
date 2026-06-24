<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Message;
use App\Services\LeadAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadCrmTest extends TestCase
{
    use RefreshDatabase;

    private function lead(array $attrs = []): Message
    {
        return Message::create(array_merge([
            'name' => 'Lead', 'email' => 'lead@example.com', 'subject' => 'Enquiry',
            'message' => 'Hello from a lead.', 'status' => Message::STATUS_UNREAD,
        ], $attrs));
    }

    public function test_middleware_captures_first_touch_attribution_into_session(): void
    {
        $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=esg2026&gclid=abc123')
            ->assertOk()
            ->assertSessionHas('lead_source.utm_source', 'google')
            ->assertSessionHas('lead_source.utm_campaign', 'esg2026')
            ->assertSessionHas('lead_source.gclid', 'abc123');

        // First-touch wins: a later page without UTM does not overwrite it.
        $this->get('/about')->assertSessionHas('lead_source.utm_source', 'google');
    }

    public function test_contact_submission_persists_auto_captured_lead_metadata(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['*siteverify*' => Http::response(['success' => true])]);
        Queue::fake();

        $this->withSession(['lead_source' => [
            'landing_page' => 'http://localhost/?utm_source=google',
            'referrer' => 'https://www.google.com/search',
            'utm_source' => 'google', 'utm_campaign' => 'esg2026', 'gclid' => 'abc123',
        ]])->post('/contact', [
            'name' => 'Jane', 'email' => 'jane@example.com', 'subject' => 'ESIA',
            'message' => 'I would like to discuss an ESIA engagement.',
            'cf-turnstile-response' => 'dummy',
        ])->assertRedirect()->assertSessionHas('success');

        $msg = Message::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame('google', $msg->utm_source);
        $this->assertSame('esg2026', $msg->utm_campaign);
        $this->assertSame('abc123', $msg->gclid);
        $this->assertSame('https://www.google.com/search', $msg->referrer);
        $this->assertSame('en', $msg->locale);            // active website locale
        $this->assertNotNull($msg->ip_address);
        $this->assertNotNull($msg->user_agent);
    }

    public function test_lead_analytics_aggregations(): void
    {
        $this->lead(['landing_page' => 'http://localhost/services/esia?x=1', 'utm_campaign' => 'esg2026', 'referrer' => 'https://google.com/search', 'locale' => 'en']);
        $this->lead(['landing_page' => 'http://localhost/services/esia', 'utm_campaign' => 'esg2026', 'referrer' => 'https://google.com/', 'locale' => 'en', 'email' => 'b@example.com']);
        $this->lead(['landing_page' => 'http://localhost/', 'utm_campaign' => 'webinar', 'locale' => 'id', 'email' => 'c@example.com']);
        // Spam is excluded from analytics.
        $this->lead(['landing_page' => 'http://localhost/spammy', 'utm_campaign' => 'esg2026', 'status' => Message::STATUS_SPAM, 'email' => 'spam@example.com']);

        $a = app(LeadAnalytics::class);

        $this->assertSame(['/services/esia' => 2, '/' => 1], $a->topLandingPages());
        $this->assertSame(['esg2026' => 2, 'webinar' => 1], $a->topCampaigns());
        $this->assertSame(['google.com' => 2], $a->topReferrers());
        $this->assertSame(['en' => 2, 'id' => 1], $a->topLocales());

        $perMonth = $a->leadsPerMonth();
        $this->assertCount(12, $perMonth);
        $this->assertSame(3, $perMonth[now()->format('Y-m')]); // spam excluded
    }

    public function test_analytics_dashboard_and_detail_panel_render(): void
    {
        $admin = Admin::factory()->create(['status' => 'active']);
        $msg = $this->lead(['utm_campaign' => 'esg2026', 'landing_page' => 'http://localhost/services/esia']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.messages.analytics'))
            ->assertOk()
            ->assertSee('Lead Analytics')
            ->assertSee('Top Campaign')
            ->assertSee('esg2026');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.messages.show', $msg))
            ->assertOk()
            ->assertSee('Lead Information')
            ->assertSee('esg2026')
            ->assertSee('/services/esia');
    }
}
