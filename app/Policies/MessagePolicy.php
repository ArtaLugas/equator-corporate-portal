<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Message;

class MessagePolicy
{
    /**
     * Super admins bypass all checks.
     */
    public function before(Admin $admin, string $ability): ?bool
    {
        return $admin->isSuperAdmin() ? true : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Shared abilities (Admin + Super Admin)
    |--------------------------------------------------------------------------
    */

    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, Message $message): bool
    {
        return true;
    }

    public function reply(Admin $admin, Message $message): bool
    {
        return true;
    }

    public function archive(Admin $admin, Message $message): bool
    {
        return true;
    }

    public function spam(Admin $admin, Message $message): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin only (handled by before(); these are explicit fallbacks)
    |--------------------------------------------------------------------------
    */

    public function delete(Admin $admin, Message $message): bool
    {
        return false;
    }

    public function restore(Admin $admin, Message $message): bool
    {
        return false;
    }

    public function forceDelete(Admin $admin, Message $message): bool
    {
        return false;
    }

    public function viewTrash(Admin $admin): bool
    {
        return false;
    }
}
