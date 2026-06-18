<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Jobs\SendNewMessageNotification;
use App\Models\Admin;
use App\Models\Message;
use App\Models\OfficeLocation;
use App\Models\SocialLink;
use App\Notifications\NewContactMessage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class ContactController extends Controller
{
    /**
     * Show the public contact form.
     */
    public function create()
    {
        // Guard defensif: bila migration belum dijalankan, halaman tetap tampil
        // (jatuh ke fallback) daripada 500 "table doesn't exist".
        $offices = Schema::hasTable('office_locations')
            ? OfficeLocation::active()->ordered()->get()
            : collect();

        $socials = Schema::hasTable('social_links')
            ? SocialLink::where('status', 'active')->orderBy('display_order')->get()
            : collect();

        return view('contact', compact('offices', 'socials'));
    }

    /**
     * Handle a contact-form submission from a website visitor.
     */
    public function store(StoreContactMessageRequest $request)
    {
        $message = Message::create([
            ...$request->safe()->only([
                'name', 'email', 'phone', 'company', 'subject', 'message',
            ]),
            'status' => Message::STATUS_UNREAD,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        activity_log(
            'Messages',
            'Message Received from ' . $message->email . ' — ' . $message->subject
        );

        // Notify the office inbox (queued, via Brevo SMTP from settings).
        SendNewMessageNotification::dispatch($message);

        // In-app notification for all active admins (bell in the CMS topbar).
        Notification::send(
            Admin::where('status', 'active')->get(),
            new NewContactMessage($message)
        );

        return back()->with(
            'success',
            "Thank you — we've received your message. Our team will get back to you within one business day."
        );
    }
}
