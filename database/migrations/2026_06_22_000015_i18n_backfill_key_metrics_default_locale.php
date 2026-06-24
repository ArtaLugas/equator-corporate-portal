<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * i18n — Phase B (backfill) for Key Metrics: copy the legacy `label` column into
 * its English column. The Indonesian column stays NULL (explicit translation
 * status + runtime fallback to English).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('key_metrics')->update([
            'label_en' => DB::raw('`label`'),
        ]);
    }

    public function down(): void
    {
        DB::table('key_metrics')->update([
            'label' => DB::raw('`label_en`'),
        ]);
    }
};
