<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead-source attribution for contact messages (mini-CRM). All fields are
 * captured automatically — the visitor never fills them. ip_address & user_agent
 * already exist on the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('landing_page', 500)->nullable()->after('user_agent');
            $table->string('referrer', 500)->nullable()->after('landing_page');
            $table->string('locale', 8)->nullable()->after('referrer');
            $table->string('utm_source', 191)->nullable()->after('locale');
            $table->string('utm_medium', 191)->nullable()->after('utm_source');
            $table->string('utm_campaign', 191)->nullable()->after('utm_medium');
            $table->string('utm_content', 191)->nullable()->after('utm_campaign');
            $table->string('utm_term', 191)->nullable()->after('utm_content');
            $table->string('gclid', 255)->nullable()->after('utm_term');
            $table->string('fbclid', 255)->nullable()->after('gclid');

            // Speeds up the analytics group-bys as lead volume grows.
            $table->index('utm_campaign');
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['utm_campaign']);
            $table->dropIndex(['locale']);
            $table->dropColumn([
                'landing_page', 'referrer', 'locale',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
                'gclid', 'fbclid',
            ]);
        });
    }
};
