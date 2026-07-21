<?php

namespace Tests\Feature;

use App\Jobs\SendContactReply;
use App\Jobs\SendNewMessageNotification;
use App\Models\Admin;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_contact_form(): void
    {
        Queue::fake();

        // The public form always requires Turnstile (unlike admin login, which only
        // enforces it when configured), so a happy-path submit must stub it.
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['*siteverify*' => Http::response(['success' => true])]);

        $this->post(route('contact.store'), [
            'name' => 'John Visitor',
            'email' => 'john@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to know more about your services.',
            'cf-turnstile-response' => 'dummy-token',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'email' => 'john@example.com',
            'status' => Message::STATUS_UNREAD,
        ]);

        Queue::assertPushed(SendNewMessageNotification::class);
    }

    public function test_contact_form_validation_fails_without_required_fields(): void
    {
        $this->post(route('contact.store'), [])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_honeypot_blocks_spam_submission(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'spam',
            'message' => 'buy now buy now buy now',
            'website' => 'http://spam.example', // honeypot filled
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_opening_message_marks_it_as_read(): void
    {
        $message = Message::create([
            'name' => 'A', 'email' => 'a@b.com', 'subject' => 'Hi',
            'message' => 'Body', 'status' => Message::STATUS_UNREAD,
        ]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.messages.show', $message))
            ->assertOk();

        $this->assertSame(Message::STATUS_READ, $message->fresh()->status);
    }

    public function test_admin_can_reply_to_message(): void
    {
        Queue::fake();

        $message = Message::create([
            'name' => 'A', 'email' => 'a@b.com', 'subject' => 'Hi',
            'message' => 'Body', 'status' => Message::STATUS_UNREAD,
        ]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.messages.reply', $message), [
                'subject' => 'Re: Hi',
                'reply_message' => 'Thanks for reaching out!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('message_replies', ['message_id' => $message->id]);
        $this->assertSame(Message::STATUS_REPLIED, $message->fresh()->status);
        $this->assertNotNull($message->fresh()->replied_at);

        Queue::assertPushed(SendContactReply::class);
    }

    public function test_admin_can_archive_and_spam(): void
    {
        $admin = Admin::factory()->create();
        $message = Message::create([
            'name' => 'A', 'email' => 'a@b.com', 'subject' => 'Hi', 'message' => 'B',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.messages.archive', $message))->assertRedirect();
        $this->assertSame(Message::STATUS_ARCHIVED, $message->fresh()->status);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.messages.spam', $message))->assertRedirect();
        $this->assertSame(Message::STATUS_SPAM, $message->fresh()->status);
    }

    public function test_regular_admin_cannot_access_trash_or_force_delete(): void
    {
        $admin = Admin::factory()->create();
        $message = Message::create(['name' => 'A', 'email' => 'a@b.com', 'subject' => 'S', 'message' => 'B']);

        $this->actingAs($admin, 'admin')->get(route('admin.messages.trash'))->assertForbidden();
        $this->actingAs($admin, 'admin')->delete(route('admin.messages.destroy', $message))->assertForbidden();
    }

    public function test_super_admin_can_soft_delete_restore_and_force_delete(): void
    {
        $super = Admin::factory()->superAdmin()->create();
        $message = Message::create(['name' => 'A', 'email' => 'a@b.com', 'subject' => 'S', 'message' => 'B']);

        // soft delete
        $this->actingAs($super, 'admin')->delete(route('admin.messages.destroy', $message))->assertRedirect();
        $this->assertSoftDeleted('messages', ['id' => $message->id]);

        // restore
        $this->actingAs($super, 'admin')->patch(route('admin.messages.restore', $message->id))->assertRedirect();
        $this->assertDatabaseHas('messages', ['id' => $message->id, 'deleted_at' => null]);

        // force delete
        $this->actingAs($super, 'admin')->delete(route('admin.messages.destroy', $message)); // soft delete again
        $this->actingAs($super, 'admin')->delete(route('admin.messages.force-delete', $message->id))->assertRedirect();
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }
}
