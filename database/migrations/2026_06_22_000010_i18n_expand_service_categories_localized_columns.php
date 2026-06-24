<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Service Categories: add nullable localized columns
 * mirroring the legacy column types exactly:
 *   - name             varchar(191) → string(191)
 *   - description      text         → text
 *   - meta_title       varchar(191) → string(191)
 *   - meta_description varchar(320) → string(320)
 *   - meta_keywords    varchar(255) → string(255)
 *
 * The legacy `name` column is already nullable, so no relax step is needed.
 * Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->string('name_id', 191)->nullable()->after('name_en');

            $table->text('description_en')->nullable()->after('description');
            $table->text('description_id')->nullable()->after('description_en');

            $table->string('meta_title_en', 191)->nullable()->after('meta_title');
            $table->string('meta_title_id', 191)->nullable()->after('meta_title_en');

            $table->string('meta_description_en', 320)->nullable()->after('meta_description');
            $table->string('meta_description_id', 320)->nullable()->after('meta_description_en');

            $table->string('meta_keywords_en', 255)->nullable()->after('meta_keywords');
            $table->string('meta_keywords_id', 255)->nullable()->after('meta_keywords_en');
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn([
                'name_en', 'name_id',
                'description_en', 'description_id',
                'meta_title_en', 'meta_title_id',
                'meta_description_en', 'meta_description_id',
                'meta_keywords_en', 'meta_keywords_id',
            ]);
        });
    }
};
