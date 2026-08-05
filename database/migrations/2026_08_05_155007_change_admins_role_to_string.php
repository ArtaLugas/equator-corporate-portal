<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen admins.role from ENUM('super_admin','admin') to a short VARCHAR so it can
 * name any RBAC role (editor, author, …), not just the two original tiers. The
 * column stays the single source of truth for an admin's role: an Admin model
 * event mirrors it into the spatie role assignment on every save.
 *
 * Raw ALTER (both environments are MySQL) preserves the existing index and default.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `admins` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'admin'");
    }

    public function down(): void
    {
        // Any value outside the original set would fail the enum cast; fold custom
        // roles back to 'admin' first so the rollback cannot lose rows.
        DB::table('admins')->whereNotIn('role', ['super_admin', 'admin'])->update(['role' => 'admin']);

        DB::statement("ALTER TABLE `admins` MODIFY `role` ENUM('super_admin','admin') NOT NULL DEFAULT 'admin'");
    }
};
