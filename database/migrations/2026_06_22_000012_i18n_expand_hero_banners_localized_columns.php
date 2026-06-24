<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Hero Banners: add nullable title_en/_id (varchar 191),
 * subtitle_en/_id (varchar 255) and button_text_en/_id (varchar 100), mirroring the
 * legacy column types exactly. All three legacy columns are already nullable, so no
 * relax step is needed. Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_banners', function (Blueprint $table) {
            $table->string('title_en', 191)->nullable()->after('title');
            $table->string('title_id', 191)->nullable()->after('title_en');

            $table->string('subtitle_en', 255)->nullable()->after('subtitle');
            $table->string('subtitle_id', 255)->nullable()->after('subtitle_en');

            $table->string('button_text_en', 100)->nullable()->after('button_text');
            $table->string('button_text_id', 100)->nullable()->after('button_text_en');
        });
    }

    public function down(): void
    {
        Schema::table('hero_banners', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'title_id',
                'subtitle_en', 'subtitle_id',
                'button_text_en', 'button_text_id',
            ]);
        });
    }
};
