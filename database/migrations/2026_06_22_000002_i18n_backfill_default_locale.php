<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill): copy each legacy column into its English column
 * (e.g. name -> name_en), since the existing content is English. The Indonesian
 * columns are intentionally left NULL so translation status stays explicit and
 * the runtime fallback (id -> en) keeps the public site complete.
 *
 * One bulk UPDATE per table — cheap even on shared hosting. Explicit and frozen
 * (no config reads), matching the Phase A migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->update([
            'name_en' => DB::raw('`name`'),
            'short_description_en' => DB::raw('`short_description`'),
            'description_en' => DB::raw('`description`'),
            'meta_title_en' => DB::raw('`meta_title`'),
            'meta_description_en' => DB::raw('`meta_description`'),
            'meta_keywords_en' => DB::raw('`meta_keywords`'),
        ]);

        DB::table('projects')->update([
            'name_en' => DB::raw('`name`'),
            'short_description_en' => DB::raw('`short_description`'),
            'description_en' => DB::raw('`description`'),
            'meta_title_en' => DB::raw('`meta_title`'),
            'meta_description_en' => DB::raw('`meta_description`'),
            'meta_keywords_en' => DB::raw('`meta_keywords`'),
        ]);

        DB::table('news')->update([
            'title_en' => DB::raw('`title`'),
            'content_en' => DB::raw('`content`'),
            'meta_title_en' => DB::raw('`meta_title`'),
            'meta_description_en' => DB::raw('`meta_description`'),
            'meta_keywords_en' => DB::raw('`meta_keywords`'),
        ]);

        DB::table('about_sections')->update([
            'name_en' => DB::raw('`name`'),
        ]);

        DB::table('about_contents')->update([
            'title_en' => DB::raw('`title`'),
            'content_en' => DB::raw('`content`'),
        ]);
    }

    public function down(): void
    {
        // Reverse copy so a rollback before Phase C stays lossless.
        DB::table('services')->update([
            'name' => DB::raw('`name_en`'),
            'short_description' => DB::raw('`short_description_en`'),
            'description' => DB::raw('`description_en`'),
            'meta_title' => DB::raw('`meta_title_en`'),
            'meta_description' => DB::raw('`meta_description_en`'),
            'meta_keywords' => DB::raw('`meta_keywords_en`'),
        ]);

        DB::table('projects')->update([
            'name' => DB::raw('`name_en`'),
            'short_description' => DB::raw('`short_description_en`'),
            'description' => DB::raw('`description_en`'),
            'meta_title' => DB::raw('`meta_title_en`'),
            'meta_description' => DB::raw('`meta_description_en`'),
            'meta_keywords' => DB::raw('`meta_keywords_en`'),
        ]);

        DB::table('news')->update([
            'title' => DB::raw('`title_en`'),
            'content' => DB::raw('`content_en`'),
            'meta_title' => DB::raw('`meta_title_en`'),
            'meta_description' => DB::raw('`meta_description_en`'),
            'meta_keywords' => DB::raw('`meta_keywords_en`'),
        ]);

        DB::table('about_sections')->update([
            'name' => DB::raw('`name_en`'),
        ]);

        DB::table('about_contents')->update([
            'title' => DB::raw('`title_en`'),
            'content' => DB::raw('`content_en`'),
        ]);
    }
};
