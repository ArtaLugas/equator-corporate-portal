<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Service Categories: copy each legacy column into
 * its English column. The Indonesian columns stay NULL (explicit translation
 * status + runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_categories')->update([
            'name_en' => DB::raw('`name`'),
            'description_en' => DB::raw('`description`'),
            'meta_title_en' => DB::raw('`meta_title`'),
            'meta_description_en' => DB::raw('`meta_description`'),
            'meta_keywords_en' => DB::raw('`meta_keywords`'),
        ]);
    }

    public function down(): void
    {
        DB::table('service_categories')->update([
            'name' => DB::raw('`name_en`'),
            'description' => DB::raw('`description_en`'),
            'meta_title' => DB::raw('`meta_title_en`'),
            'meta_description' => DB::raw('`meta_description_en`'),
            'meta_keywords' => DB::raw('`meta_keywords_en`'),
        ]);
    }
};
