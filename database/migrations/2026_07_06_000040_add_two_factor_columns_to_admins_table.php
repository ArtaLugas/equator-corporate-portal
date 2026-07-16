<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in two-factor authentication (TOTP) for admins. Secret and recovery codes
 * are stored encrypted (model casts). two_factor_confirmed_at is null until the
 * admin verifies a code during enrollment, so a half-set-up secret never gates
 * login.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('admins', 'two_factor_secret')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
