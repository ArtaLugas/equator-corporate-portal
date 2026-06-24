<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for the FAQ module: copy each legacy column into its
 * English column. The Indonesian columns stay NULL (explicit translation status +
 * runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('faqs')->update([
            'question_en' => DB::raw('`question`'),
            'answer_en' => DB::raw('`answer`'),
        ]);
    }

    public function down(): void
    {
        DB::table('faqs')->update([
            'question' => DB::raw('`question_en`'),
            'answer' => DB::raw('`answer_en`'),
        ]);
    }
};
