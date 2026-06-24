<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Child items of a credential (e.g. an LPJP's service classifications, a KBLI's
 * business classifications). Cascades with the parent. Created already-localized
 * (title_en anchor NOT NULL); description is plain text (no Purifier).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_credential_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('credential_id')
                ->constrained('company_credentials')
                ->cascadeOnDelete();

            // Translatable (suffix columns). title_en is the required anchor.
            $table->string('title_en', 191);
            $table->string('title_id', 191)->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_id')->nullable();

            $table->smallInteger('display_order')->default(0);

            $table->timestamps();

            $table->index(['credential_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_credential_items');
    }
};
