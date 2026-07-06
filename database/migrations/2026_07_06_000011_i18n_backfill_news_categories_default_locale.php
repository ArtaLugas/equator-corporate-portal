<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for News Categories: copy the legacy `name` column
 * into its English column. The Indonesian column stays NULL (explicit translation
 * status + runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('news_categories')->update([
            'name_en' => DB::raw('`name`'),
        ]);
    }

    public function down(): void
    {
        DB::table('news_categories')->update([
            'name' => DB::raw('`name_en`'),
        ]);
    }
};
