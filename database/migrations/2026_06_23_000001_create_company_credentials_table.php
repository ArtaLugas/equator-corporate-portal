<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company Credentials — central registry of company credentials (LPJP, ISO,
 * KBLI, business licenses, memberships, accreditations, …).
 *
 * i18n note (Multilingual v1 / Architecture Freeze §3): this is a NEW table, so
 * it is created already in its final multilingual shape — localized suffix
 * columns (`title_en`/`title_id`, …) with the default-locale anchor `title_en`
 * NOT NULL. There is no legacy single-language column, hence no Phase A/B/C
 * (expand→backfill→contract) and no phase-c entry is needed.
 *
 * `category` is a plain string validated against config('credentials.categories')
 * so new categories never require a schema change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_credentials', function (Blueprint $table) {
            $table->id();

            $table->string('category', 50);

            // Translatable (suffix columns). title_en is the required anchor.
            $table->string('title_en', 191);
            $table->string('title_id', 191)->nullable();
            $table->string('issuer_en', 191)->nullable();
            $table->string('issuer_id', 191)->nullable();
            $table->longText('description_en')->nullable();
            $table->longText('description_id')->nullable();

            // Single-language.
            $table->string('credential_number', 191)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('attachment', 500)->nullable();
            $table->string('verification_url', 500)->nullable();
            $table->boolean('featured')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->smallInteger('display_order')->default(1);
            $table->string('slug', 191)->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('category');
            $table->index('expiry_date');
            $table->index(['status', 'featured']);
            $table->index(['status', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_credentials');
    }
};
