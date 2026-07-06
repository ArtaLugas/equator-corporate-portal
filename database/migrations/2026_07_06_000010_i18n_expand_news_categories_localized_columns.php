<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for News Categories: add nullable localized columns
 * mirroring the legacy column type exactly:
 *   - name  varchar(191) → string(191)
 *
 * Only `name` is translatable (slug/timestamps stay single). The legacy `name`
 * column is already nullable, so no relax step is needed. Explicit, frozen
 * snapshot — mirrors the service_categories expand migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_categories', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('news_categories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_id']);
        });
    }
};
