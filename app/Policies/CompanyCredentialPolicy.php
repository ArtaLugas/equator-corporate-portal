<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CompanyCredential;

/**
 * Authorization for Company Credentials. Auto-discovered by Laravel
 * (CompanyCredential → CompanyCredentialPolicy).
 *
 * Content-management abilities are open to every authenticated admin (matching
 * the other CMS modules); permanent deletion is reserved for super admins.
 */
class CompanyCredentialPolicy
{
    /** Super admins bypass all checks. */
    public function before(Admin $admin, string $ability): ?bool
    {
        return $admin->isSuperAdmin() ? true : null;
    }

    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    public function view(Admin $admin, CompanyCredential $credential): bool
    {
        return true;
    }

    public function create(Admin $admin): bool
    {
        return true;
    }

    public function update(Admin $admin, CompanyCredential $credential): bool
    {
        return true;
    }

    public function delete(Admin $admin, ?CompanyCredential $credential = null): bool
    {
        return true;
    }

    public function restore(Admin $admin): bool
    {
        return true;
    }

    public function viewTrash(Admin $admin): bool
    {
        return true;
    }

    /** Permanent deletion — super admin only (granted via before()). */
    public function forceDelete(Admin $admin): bool
    {
        return false;
    }
}
