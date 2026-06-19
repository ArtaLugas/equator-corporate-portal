<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register authorization gates / policies.
     */
    public function boot(): void
    {
        // Only super admins may view the activity log.
        Gate::define('view-activity-logs', fn (Admin $admin) => $admin->isSuperAdmin());
    }
}
