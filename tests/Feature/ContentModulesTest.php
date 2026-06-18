<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Faq;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModulesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::factory()->create();
    }

    public function test_admin_can_create_faq(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.faqs.store'), [
                'question' => 'What is X?',
                'answer' => 'X is Y.',
                'display_order' => 1,
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', ['question' => 'What is X?']);
    }

    public function test_faq_requires_question_and_answer(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.faqs.store'), [])
            ->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_admin_can_delete_faq(): void
    {
        $faq = Faq::create(['question' => 'Q', 'answer' => 'A', 'display_order' => 0]);

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.faqs.destroy', $faq))
            ->assertRedirect();

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_create_social_link(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.social-links.store'), [
                'platform' => 'Instagram',
                'url' => 'https://instagram.com/equator',
                'icon_class' => 'fa-instagram',
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.social-links.index'));

        $this->assertDatabaseHas('social_links', ['platform' => 'Instagram', 'status' => 'active']);
    }

    public function test_social_link_requires_valid_url(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.social-links.store'), [
                'platform' => 'Bad',
                'url' => 'not-a-url',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('url');
    }

    public function test_general_settings_can_be_updated(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.general.update'), [
                'company_name' => 'My Company',
                'email' => 'hello@my.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', ['company_name' => 'My Company']);
    }

    public function test_email_settings_restricted_to_super_admin(): void
    {
        // Regular admin forbidden
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.settings.email'))
            ->assertForbidden();

        // Super admin allowed
        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get(route('admin.settings.email'))
            ->assertOk();
    }
}
