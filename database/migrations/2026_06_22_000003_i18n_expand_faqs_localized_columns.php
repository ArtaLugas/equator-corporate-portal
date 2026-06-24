<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * i18n — Phase A (expand) for the FAQ module: add nullable question_en/_id and
 * answer_en/_id, mirroring the legacy `text` columns. Both legacy columns are
 * already nullable, so no relax step is needed. Explicit, frozen snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->text('question_en')->nullable()->after('question');
            $table->text('question_id')->nullable()->after('question_en');

            $table->text('answer_en')->nullable()->after('answer');
            $table->text('answer_id')->nullable()->after('answer_en');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question_en', 'question_id', 'answer_en', 'answer_id']);
        });
    }
};
