<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for Company Documents: add nullable title_en/_id
 * (varchar 191) and description_en/_id (text), mirroring the legacy columns.
 * Legacy `title` is NOT NULL, so it is relaxed to nullable here (the localized
 * columns become the source of truth). `description` is already nullable.
 * Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->string('title', 191)->nullable()->change();

            $table->string('title_en', 191)->nullable()->after('title');
            $table->string('title_id', 191)->nullable()->after('title_en');

            $table->text('description_en')->nullable()->after('description');
            $table->text('description_id')->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_id', 'description_en', 'description_id']);

            $table->string('title', 191)->nullable(false)->change();
        });
    }
};
