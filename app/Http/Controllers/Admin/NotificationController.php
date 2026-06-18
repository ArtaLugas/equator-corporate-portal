<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Full notifications list.
     */
    public function index()
    {
        $notifications = auth('admin')->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Open a notification: mark read, then redirect to its target.
     */
    public function read(string $id)
    {
        $notification = auth('admin')->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('admin.notifications.index');

        return redirect($url);
    }

    /**
     * Mark all as read.
     */
    public function readAll(Request $request)
    {
        auth('admin')->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Clear (delete) all notifications.
     */
    public function clear(Request $request)
    {
        auth('admin')->user()->notifications()->delete();

        return back()->with('success', 'Notifications cleared.');
    }
}
