<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand): add nullable <field>_en / <field>_id columns for the
 * core public models, mirroring each legacy column's exact type/length.
 *
 * Additive and reversible. The legacy single-language columns are kept until
 * Phase C so the transition has a safe rollback path. Column lengths match the
 * originals exactly: 191 for indexed string columns (the utf8mb4 index-safe
 * length), 255 / 320 for the rest, longText for rich-text bodies.
 *
 * Expand-contract: a legacy translatable column that is NOT NULL with no default
 * would block inserts from the new code (which writes only *_en / *_id). The only
 * such column among the core tables is about_sections.name, so it is relaxed to
 * nullable for the transition window; Phase C drops it entirely.
 *
 * This migration is an explicit, frozen snapshot — it does NOT read config, so
 * re-running migrate:fresh always reproduces the same schema regardless of any
 * later changes to config/translatable.php or config/locales.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');

            $table->string('short_description_en', 255)->nullable()->after('short_description');
            $table->string('short_description_id', 255)->nullable()->after('short_description_en');

            $table->longText('description_en')->nullable()->after('description');
            $table->longText('description_id')->nullable()->after('description_en');

            $table->string('meta_title_en', 191)->nullable()->after('meta_title');
            $table->string('meta_title_id', 191)->nullable()->after('meta_title_en');

            $table->string('meta_description_en', 320)->nullable()->after('meta_description');
            $table->string('meta_description_id', 320)->nullable()->after('meta_description_en');

            $table->string('meta_keywords_en', 255)->nullable()->after('meta_keywords');
            $table->string('meta_keywords_id', 255)->nullable()->after('meta_keywords_en');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');

            $table->string('short_description_en', 255)->nullable()->after('short_description');
            $table->string('short_description_id', 255)->nullable()->after('short_description_en');

            $table->longText('description_en')->nullable()->after('description');
            $table->longText('description_id')->nullable()->after('description_en');

            $table->string('meta_title_en', 191)->nullable()->after('meta_title');
            $table->string('meta_title_id', 191)->nullable()->after('meta_title_en');

            $table->string('meta_description_en', 320)->nullable()->after('meta_description');
            $table->string('meta_description_id', 320)->nullable()->after('meta_description_en');

            $table->string('meta_keywords_en', 255)->nullable()->after('meta_keywords');
            $table->string('meta_keywords_id', 255)->nullable()->after('meta_keywords_en');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('title_en', 191)->nullable()->after('title');
            $table->string('title_id', 191)->nullable()->after('title_en');

            $table->longText('content_en')->nullable()->after('content');
            $table->longText('content_id')->nullable()->after('content_en');

            $table->string('meta_title_en', 191)->nullable()->after('meta_title');
            $table->string('meta_title_id', 191)->nullable()->after('meta_title_en');

            $table->string('meta_description_en', 320)->nullable()->after('meta_description');
            $table->string('meta_description_id', 320)->nullable()->after('meta_description_en');

            $table->string('meta_keywords_en', 255)->nullable()->after('meta_keywords');
            $table->string('meta_keywords_id', 255)->nullable()->after('meta_keywords_en');
        });

        Schema::table('about_sections', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');
        });

        // Relax the only NOT-NULL legacy translatable column so the new code can
        // insert without it during the transition (Phase C drops it). Native
        // SQL keeps the exact MySQL type; skipped on other drivers (already nullable).
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('about_sections', 'name')) {
            DB::statement('ALTER TABLE `about_sections` MODIFY `name` VARCHAR(191) NULL');
        }

        Schema::table('about_contents', function (Blueprint $table) {
            $table->string('title_en', 191)->nullable()->after('title');
            $table->string('title_id', 191)->nullable()->after('title_en');

            $table->longText('content_en')->nullable()->after('content');
            $table->longText('content_id')->nullable()->after('content_en');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'name_en', 'name_id',
                'short_description_en', 'short_description_id',
                'description_en', 'description_id',
                'meta_title_en', 'meta_title_id',
                'meta_description_en', 'meta_description_id',
                'meta_keywords_en', 'meta_keywords_id',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'name_en', 'name_id',
                'short_description_en', 'short_description_id',
                'description_en', 'description_id',
                'meta_title_en', 'meta_title_id',
                'meta_description_en', 'meta_description_id',
                'meta_keywords_en', 'meta_keywords_id',
            ]);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'title_id',
                'content_en', 'content_id',
                'meta_title_en', 'meta_title_id',
                'meta_description_en', 'meta_description_id',
                'meta_keywords_en', 'meta_keywords_id',
            ]);
        });

        Schema::table('about_sections', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_id']);
        });

        Schema::table('about_contents', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_id', 'content_en', 'content_id']);
        });
    }
};
