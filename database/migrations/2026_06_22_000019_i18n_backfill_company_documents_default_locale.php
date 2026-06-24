<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Company Documents: copy each legacy column into
 * its English column. The Indonesian columns stay NULL (explicit translation
 * status + runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('company_documents')->update([
            'title_en' => DB::raw('`title`'),
            'description_en' => DB::raw('`description`'),
        ]);
    }

    public function down(): void
    {
        DB::table('company_documents')->update([
            'title' => DB::raw('`title_en`'),
            'description' => DB::raw('`description_en`'),
        ]);
    }
};
