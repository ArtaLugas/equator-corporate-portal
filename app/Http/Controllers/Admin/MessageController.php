<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageReplyRequest;
use App\Jobs\SendContactReply;
use App\Models\Message;
use App\Services\LeadAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    private const PAGINATION = 15;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->authorize('viewAny', Message::class);

        $messages = Message::query()
            ->search($request->search)
            ->status($request->status)
            ->latest()
            ->paginate(self::PAGINATION)
            ->withQueryString();

        // Per-status counters for the filter tabs.
        $counts = Message::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts['all'] = (int) $counts->sum();

        return view('admin.messages.index', compact('messages', 'counts'));
    }

    /*
    |--------------------------------------------------------------------------
    | LEAD ANALYTICS (mini-CRM dashboard)
    |--------------------------------------------------------------------------
    */

    public function analytics(LeadAnalytics $analytics)
    {
        $this->authorize('viewAny', Message::class);

        $data = $analytics->summary();

        return view('admin.messages.analytics', compact('data'));
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW (auto mark as read)
    |--------------------------------------------------------------------------
    */

    public function show(Message $message)
    {
        $this->authorize('view', $message);

        // Auto-transition unread → read on open.
        if ($message->isUnread()) {
            $message->update(['status' => Message::STATUS_READ]);
            activity_log('Messages', 'Message Viewed: #'.$message->id.' from '.$message->email);
        }

        $message->load('replies.admin');

        return view('admin.messages.show', compact('message'));
    }

    /*
    |--------------------------------------------------------------------------
    | REPLY
    |--------------------------------------------------------------------------
    */

    public function reply(SendMessageReplyRequest $request, Message $message)
    {
        $validated = $request->validated();

        try {

            DB::transaction(function () use ($message, $validated) {

                $now = now();

                $message->replies()->create([
                    'admin_id' => auth('admin')->id(),
                    'subject' => $validated['subject'],
                    'reply_message' => $validated['reply_message'],
                    'sent_at' => $now,
                ]);

                $message->update([
                    'status' => Message::STATUS_REPLIED,
                    'replied_at' => $now,
                ]);
            });

            // Send the reply through Brevo SMTP (queued).
            SendContactReply::dispatch(
                $message,
                $validated['subject'],
                $validated['reply_message'],
            );

            activity_log('Messages', 'Message Replied: #'.$message->id.' to '.$message->email);

            return back()->with('success', 'Reply sent successfully.');

        } catch (\Throwable $e) {

            report($e);

            return back()->with('error', friendly_error($e));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ARCHIVE / UNARCHIVE
    |--------------------------------------------------------------------------
    */

    public function archive(Message $message)
    {
        $this->authorize('archive', $message);

        $message->update([
            'status' => Message::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);

        activity_log('Messages', 'Message Archived: #'.$message->id);

        return back()->with('success', 'Message archived.');
    }

    public function unarchive(Message $message)
    {
        $this->authorize('archive', $message);

        $message->update([
            'status' => Message::STATUS_READ,
            'archived_at' => null,
        ]);

        activity_log('Messages', 'Message Unarchived: #'.$message->id);

        return back()->with('success', 'Message moved back to inbox.');
    }

    /*
    |--------------------------------------------------------------------------
    | SPAM / RESTORE FROM SPAM
    |--------------------------------------------------------------------------
    */

    public function markSpam(Message $message)
    {
        $this->authorize('spam', $message);

        $message->update(['status' => Message::STATUS_SPAM]);

        activity_log('Messages', 'Marked as Spam: #'.$message->id);

        return back()->with('success', 'Message marked as spam.');
    }

    public function markUnread(Message $message)
    {
        $this->authorize('spam', $message);

        $message->update(['status' => Message::STATUS_UNREAD]);

        activity_log('Messages', 'Message restored to inbox (unread): #'.$message->id);

        return back()->with('success', 'Message restored to inbox.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY (soft delete → trash) — Super Admin only
    |--------------------------------------------------------------------------
    */

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        activity_log('Messages', 'Message Deleted (trashed): #'.$message->id);

        // Redirect to the inbox, NOT back(): this action is triggered from the
        // message detail page, and back() would return to that same detail URL —
        // which now 404s because the message is soft-deleted and the show route's
        // implicit binding excludes trashed records.
        return redirect()->route('admin.messages.index')
            ->with('success', 'Message moved to trash.');
    }

    /*
    |--------------------------------------------------------------------------
    | BULK DESTROY
    |--------------------------------------------------------------------------
    */

    public function bulkDestroy()
    {
        $ids = array_map('intval', (array) request()->input('ids', []));

        if (empty($ids)) {
            return back()->with('error', __('flash.none_selected'));
        }

        // Authorize each message exactly like single-delete (Super Admin gate),
        // so a non-permitted admin is blocked (403) before anything is trashed.
        $messages = Message::whereIn('id', $ids)->get();
        $count = 0;
        foreach ($messages as $message) {
            $this->authorize('delete', $message);
            $message->delete();
            $count++;
        }

        activity_log('Message', "Bulk moved {$count} message(s) to trash.");

        return back()->with('success', "{$count} message(s) moved to trash.");
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH — Super Admin only
    |--------------------------------------------------------------------------
    */

    public function trash(Request $request)
    {
        $this->authorize('viewTrash', Message::class);

        $messages = Message::onlyTrashed()
            ->search($request->search)
            ->latest('deleted_at')
            ->paginate(self::PAGINATION)
            ->withQueryString();

        return view('admin.messages.trash', compact('messages'));
    }

    public function restore(int $id)
    {
        $message = Message::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $message);

        $message->restore();

        activity_log('Messages', 'Message Restored: #'.$message->id);

        return back()->with('success', 'Message restored.');
    }

    public function forceDelete(int $id)
    {
        $message = Message::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $message);

        $message->forceDelete();

        activity_log('Messages', 'Message Force Deleted: #'.$id);

        return back()->with('success', 'Message permanently deleted.');
    }
}
