<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Office Location is now the single source of truth for public contact info.
 * If a site has contact data in the legacy `settings` columns but no office yet,
 * create one primary office so the public site keeps showing the same details.
 *
 * Idempotent & non-destructive:
 *  - Runs only when there are ZERO office locations.
 *  - Leaves the legacy settings columns untouched (kept for rollback safety).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('office_locations') || ! Schema::hasTable('settings')) {
            return;
        }

        // Offices already exist → Office Location is already the source. Skip.
        if (DB::table('office_locations')->exists()) {
            return;
        }

        $s = DB::table('settings')->first();
        if (! $s) {
            return;
        }

        $hasContact = filled($s->address ?? null)
            || filled($s->phone ?? null)
            || filled($s->email ?? null)
            || filled($s->google_maps_embed ?? null);

        if (! $hasContact) {
            return;
        }

        DB::table('office_locations')->insert([
            'name' => filled($s->company_name ?? null) ? $s->company_name : 'Head Office',
            'address' => $s->address ?? null,
            'phone' => $s->phone ?? null,
            'email' => $s->email ?? null,
            'map_embed' => $s->google_maps_embed ?? null,
            'is_primary' => true,
            'display_order' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Data migration — intentionally not reversed, to avoid deleting a legitimate office.
    }
};
