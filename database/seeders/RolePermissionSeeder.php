<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the RBAC baseline: every permission from App\Support\Rbac, the two roles
 * that mirror today's model (super_admin, admin), and role assignment for any
 * admins that already exist.
 *
 * Idempotent — safe to run on every deploy. super_admin is granted no explicit
 * permissions: it bypasses all checks via the Gate::before in AuthServiceProvider.
 * The admin role gets every content permission, reproducing current behaviour so
 * the existing test suite stays green when enforcement lands in Phase 1.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = Rbac::GUARD;

        foreach (Rbac::permissions() as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $superAdmin = Role::findOrCreate('super_admin', $guard);
        $admin = Role::findOrCreate('admin', $guard);

        // admin = all content permissions (system surfaces stay super-only).
        $admin->syncPermissions(Rbac::contentPermissions());

        // super_admin needs no explicit grants — Gate::before makes it omnipotent —
        // but syncing the full set keeps the matrix UI showing it as all-checked.
        $superAdmin->syncPermissions(Rbac::permissions());

        // Bring existing admins onto the role named by their `role` column (the
        // source of truth). Rows predating RBAC are not re-saved, so the model's
        // saved-event mirror never fired for them — sync here. Unknown role names
        // fall back to `admin` so no account is left permission-less.
        Admin::query()->each(function (Admin $account) {
            $role = Role::where('name', $account->role)->where('guard_name', $guard)->exists()
                ? $account->role
                : 'admin';

            if (! $account->hasRole($role)) {
                $account->syncRoles([$role]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
