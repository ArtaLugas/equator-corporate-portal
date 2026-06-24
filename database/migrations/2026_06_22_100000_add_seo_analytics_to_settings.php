<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO & Analytics settings — managed via the admin CMS, never hardcoded.
 *   ga4_measurement_id → Google Analytics 4 ID (G-XXXXXXXXXX)
 *   gsc_verification   → Google Search Console verification token (meta tag content)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ga4_measurement_id', 32)->nullable()->after('meta_keywords');
            $table->string('gsc_verification', 191)->nullable()->after('ga4_measurement_id');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['ga4_measurement_id', 'gsc_verification']);
        });
    }
};
