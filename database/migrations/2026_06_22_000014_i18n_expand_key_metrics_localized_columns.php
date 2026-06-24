<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Key Metrics: add nullable label_en/_id (varchar 191),
 * mirroring the legacy `label` column type exactly. Only `label` is translatable;
 * value, icon, display_order, status and is_featured stay single-language. Explicit,
 * frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('key_metrics', function (Blueprint $table) {
            $table->string('label_en', 191)->nullable()->after('label');
            $table->string('label_id', 191)->nullable()->after('label_en');

            // Relax the legacy NOT NULL anchor so inserts that write only the
            // localized columns (label_en/label_id) during the Phase A/B window
            // don't fail with "Field 'label' doesn't have a default value".
            $table->string('label', 191)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('key_metrics', function (Blueprint $table) {
            $table->dropColumn(['label_en', 'label_id']);
            $table->string('label', 191)->nullable(false)->change();
        });
    }
};
