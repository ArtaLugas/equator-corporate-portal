<?php

namespace Tests\Feature;

use App\Jobs\SendContactAutoReply;
use App\Jobs\SendNewMessageNotification;
use App\Mail\ContactAutoReplyMail;
use App\Models\Admin;
use App\Models\Message;
use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactAutoReplyTest extends TestCase
{
    use RefreshDatabase;

    private function newMessage(array $attrs = []): Message
    {
        return Message::create(array_merge([
            'name' => 'Jane Visitor',
            'email' => 'jane@example.com',
            'subject' => 'ESIA enquiry',
            'message' => 'I would like to discuss an ESIA for our coastal project.',
            'status' => Message::STATUS_UNREAD,
        ], $attrs));
    }

    public function test_message_gets_a_unique_reference_number(): void
    {
        $a = $this->newMessage();
        $b = $this->newMessage(['email' => 'other@example.com']);

        $this->assertMatchesRegularExpression('/^EQ-\d{8}-\d{6}$/', $a->reference);
        $this->assertSame(
            'EQ-'.$a->created_at->format('Ymd').'-'.str_pad((string) $a->id, 6, '0', STR_PAD_LEFT),
            $a->reference
        );
        $this->assertNotSame($a->reference, $b->reference);
    }

    public function test_job_sends_auto_reply_to_the_visitor(): void
    {
        Mail::fake();

        (new SendContactAutoReply($this->newMessage(), 'en'))->handle();

        Mail::assertSent(ContactAutoReplyMail::class, fn ($mail) => $mail->hasTo('jane@example.com'));
    }

    public function test_auto_reply_renders_in_both_locales_with_required_content(): void
    {
        $message = $this->newMessage();

        app()->setLocale('en');
        $en = (new ContactAutoReplyMail($message))->render();
        $this->assertStringContainsString($message->reference, $en);          // reference number
        $this->assertStringContainsString('Thank you for reaching out', $en); // thanks + confirmation
        $this->assertStringContainsString('one business day', $en);           // response-time estimate
        $this->assertStringContainsString('Team', $en);                       // company signature

        app()->setLocale('id');
        $id = (new ContactAutoReplyMail($message))->render();
        $this->assertStringContainsString($message->reference, $id);
        $this->assertStringContainsString('Terima kasih telah menghubungi', $id);
        $this->assertStringContainsString('satu hari kerja', $id);

        app()->setLocale('en');
    }

    public function test_contact_submission_notifies_admin_and_auto_replies_to_visitor(): void
    {
        $admin = Admin::factory()->create(['status' => 'active']);

        // Make Turnstile pass without a real Cloudflare call.
        config(['services.turnstile.secret_key' => 'test-secret']);
        Http::fake(['*siteverify*' => Http::response(['success' => true])]);

        Queue::fake();
        Notification::fake();

        $this->post('/contact', [
            'name' => 'Jane Visitor',
            'email' => 'jane@example.com',
            'subject' => 'ESIA enquiry',
            'message' => 'I would like to discuss an ESIA for our coastal project.',
            'cf-turnstile-response' => 'dummy-token',
        ])->assertRedirect()->assertSessionHas('success');

        $message = Message::where('email', 'jane@example.com')->firstOrFail();
        $this->assertMatchesRegularExpression('/^EQ-\d{8}-\d{6}$/', $message->reference);

        // 1) Admin notification (office email job + in-app bell) still fires.
        Queue::assertPushed(SendNewMessageNotification::class);
        Notification::assertSentTo($admin, NewContactMessage::class);

        // 2) Visitor auto-reply queued in the active locale.
        Queue::assertPushed(
            SendContactAutoReply::class,
            fn ($job) => $job->message->email === 'jane@example.com' && $job->locale === 'en'
        );
    }
}
