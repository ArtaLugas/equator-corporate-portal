<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register view composers.
     */
    public function boot(): void
    {
        // Share notification data with the admin topbar.
        // Wrapped defensively so a missing table / transient DB issue degrades
        // gracefully (empty notifications) instead of crashing every admin page.
        View::composer('admin.partials.topbar', function ($view) {
            $unreadCount = 0;
            $items = collect();

            try {
                /** @var Admin|null $admin */
                $admin = auth('admin')->user();

                if ($admin) {
                    $unreadCount = $admin->unreadNotifications()->count();
                    $items = $admin->notifications()->latest()->take(8)->get();
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $view->with([
                'notifUnreadCount' => $unreadCount,
                'notifItems' => $items,
            ]);
        });
    }
}
