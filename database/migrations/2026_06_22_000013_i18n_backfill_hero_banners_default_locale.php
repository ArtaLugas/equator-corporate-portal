<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Hero Banners: copy each legacy column into its
 * English column. The Indonesian columns stay NULL (explicit translation status +
 * runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hero_banners')->update([
            'title_en' => DB::raw('`title`'),
            'subtitle_en' => DB::raw('`subtitle`'),
            'button_text_en' => DB::raw('`button_text`'),
        ]);
    }

    public function down(): void
    {
        DB::table('hero_banners')->update([
            'title' => DB::raw('`title_en`'),
            'subtitle' => DB::raw('`subtitle_en`'),
            'button_text' => DB::raw('`button_text_en`'),
        ]);
    }
};
