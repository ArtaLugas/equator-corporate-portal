<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Teams: copy the legacy position/bio into their
 * English columns. The Indonesian columns stay NULL (explicit translation status +
 * runtime fallback to English). `name` is not translated and is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('teams')->update([
            'position_en' => DB::raw('`position`'),
            'bio_en' => DB::raw('`bio`'),
        ]);
    }

    public function down(): void
    {
        DB::table('teams')->update([
            'position' => DB::raw('`position_en`'),
            'bio' => DB::raw('`bio_en`'),
        ]);
    }
};
