<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Core Values: add nullable title_en/_id (varchar 191)
 * and description_en/_id (text), mirroring the legacy columns. Both legacy columns
 * are already nullable, so no relax step is needed. Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_values', function (Blueprint $table) {
            $table->string('title_en', 191)->nullable()->after('title');
            $table->string('title_id', 191)->nullable()->after('title_en');

            $table->text('description_en')->nullable()->after('description');
            $table->text('description_id')->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('core_values', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_id', 'description_en', 'description_id']);
        });
    }
};
