<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Teams: add nullable position_en/_id (varchar 191)
 * and bio_en/_id (text). Only `position` and `bio` are translatable — a team
 * member's `name` is intentionally single-language.
 *
 * The legacy `position` column is NOT NULL, so it is relaxed to nullable for the
 * transition (new code writes only position_en/_id). `bio` is already nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('position_en', 191)->nullable()->after('position');
            $table->string('position_id', 191)->nullable()->after('position_en');

            $table->text('bio_en')->nullable()->after('bio');
            $table->text('bio_id')->nullable()->after('bio_en');
        });

        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('teams', 'position')) {
            DB::statement('ALTER TABLE `teams` MODIFY `position` VARCHAR(191) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['position_en', 'position_id', 'bio_en', 'bio_id']);
        });
    }
};
