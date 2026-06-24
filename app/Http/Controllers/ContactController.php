<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Jobs\SendContactAutoReply;
use App\Jobs\SendNewMessageNotification;
use App\Models\Admin;
use App\Models\Message;
use App\Models\OfficeLocation;
use App\Models\SocialLink;
use App\Notifications\NewContactMessage;
use App\Services\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Show the public contact form.
     */
    public function create(Request $request)
    {
        // Guard defensif: bila migration belum dijalankan, halaman tetap tampil
        // (jatuh ke fallback) daripada 500 "table doesn't exist".
        $offices = Schema::hasTable('office_locations')
            ? OfficeLocation::active()->ordered()->get()
            : collect();

        $socials = Schema::hasTable('social_links')
            ? SocialLink::where('status', 'active')->orderBy('display_order')->get()
            : collect();

        // Pre-fill Subject when the visitor arrives from a service page
        // (e.g. /contact?service=Environmental+Impact+Assessment).
        // Guard against non-string input (?service[]=x) before trimming.
        $serviceParam = $request->query('service');
        $prefillSubject = is_string($serviceParam) && trim($serviceParam) !== ''
            ? 'Service enquiry: '.Str::limit(trim($serviceParam), 120, '')
            : null;

        return view('contact', compact('offices', 'socials', 'prefillSubject'));
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
            // Auto-captured lead-source attribution (landing page, referrer,
            // locale, UTM, gclid/fbclid, ip, user-agent) — see LeadSource.
            ...app(LeadSource::class)->metadata($request),
            'status' => Message::STATUS_UNREAD,
        ]);

        activity_log(
            'Messages',
            'Message Received from '.$message->email.' — '.$message->subject
        );

        // Notify the office inbox (queued, via Brevo SMTP from settings).
        SendNewMessageNotification::dispatch($message);

        // In-app notification for all active admins (bell in the CMS topbar).
        Notification::send(
            Admin::where('status', 'active')->get(),
            new NewContactMessage($message)
        );

        // Auto-reply confirmation to the visitor, in the website's active locale.
        SendContactAutoReply::dispatch($message, app()->getLocale());

        return back()->with(
            'success',
            "Thank you — we've received your message. Our team will get back to you within one business day."
        );
    }
}
