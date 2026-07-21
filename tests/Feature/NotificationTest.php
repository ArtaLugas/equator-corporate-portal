<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Message;
use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_notifies_active_admins_only(): void
    {
        $active1 = Admin::factory()->create();
        $active2 = Admin::factory()->superAdmin()->create();
        $inactive = Admin::factory()->inactive()->create();

        // The public form always requires Turnstile; stub it so this test exercises
        // notification fan-out rather than CAPTCHA validation.
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['*siteverify*' => Http::response(['success' => true])]);

        $this->post(route('contact.store'), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Hello team',
            'message' => 'I have a question about your services.',
            'cf-turnstile-response' => 'dummy-token',
        ])->assertRedirect();

        $this->assertSame(1, $active1->fresh()->notifications()->count());
        $this->assertSame(1, $active2->fresh()->notifications()->count());
        $this->assertSame(0, $inactive->fresh()->notifications()->count());
    }

    public function test_admin_can_mark_all_notifications_read(): void
    {
        $admin = Admin::factory()->create();
        $message = Message::create(['name' => 'A', 'email' => 'a@b.com', 'subject' => 'S', 'message' => 'B']);
        $admin->notify(new NewContactMessage($message));

        $this->assertSame(1, $admin->unreadNotifications()->count());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_opening_notification_marks_read_and_redirects(): void
    {
        $admin = Admin::factory()->create();
        $message = Message::create(['name' => 'A', 'email' => 'a@b.com', 'subject' => 'S', 'message' => 'B']);
        $admin->notify(new NewContactMessage($message));

        $notification = $admin->notifications()->first();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.notifications.read', $notification->id))
            ->assertRedirect(route('admin.messages.show', $message->id));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notifications_index_loads(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications');
    }
}
