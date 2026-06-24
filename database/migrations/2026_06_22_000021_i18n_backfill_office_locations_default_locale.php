<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Office Locations: copy each legacy column into
 * its English column. The Indonesian columns stay NULL (explicit translation
 * status + runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('office_locations')->update([
            'name_en' => DB::raw('`name`'),
            'address_en' => DB::raw('`address`'),
        ]);
    }

    public function down(): void
    {
        DB::table('office_locations')->update([
            'name' => DB::raw('`name_en`'),
            'address' => DB::raw('`address_en`'),
        ]);
    }
};
