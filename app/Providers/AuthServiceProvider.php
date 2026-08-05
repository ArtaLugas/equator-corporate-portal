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
        // NOTE: deliberately NO global Gate::before super-admin bypass. AdminPolicy
        // relies on its own before() returning null (not true) for super admins so
        // that delete() can still block self-deletion — a blanket true would short-
        // circuit that guard. Super admins instead hold every permission via the
        // super_admin role (see RolePermissionSeeder), which satisfies the string
        // permission gates without overriding the model policies' protective logic.

        // Only super admins may view the activity log.
        Gate::define('view-activity-logs', fn (Admin $admin) => $admin->isSuperAdmin());
    }
}
