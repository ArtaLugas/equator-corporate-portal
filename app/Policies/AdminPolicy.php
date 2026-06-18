<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    /**
     * Only super admins may manage admin accounts. Returning null lets the
     * specific ability methods run (needed so delete() can check self-deletion).
     */
    public function before(Admin $admin, string $ability): ?bool
    {
        return $admin->isSuperAdmin() ? null : false;
    }

    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Admin $target): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return true;
    }

    /**
     * An admin can never delete their own account.
     */
    public function delete(Admin $admin, Admin $target): bool
    {
        return $admin->id !== $target->id;
    }
}
