<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a unique, human-readable reference to contact messages
 * (e.g. EQ-20260715-000034). Generated from the row id in the Message model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('reference', 32)->nullable()->unique()->after('id');
        });

        // Backfill existing rows (MySQL).
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "UPDATE messages
                 SET reference = CONCAT('EQ-', DATE_FORMAT(created_at, '%Y%m%d'), '-', LPAD(id, 6, '0'))
                 WHERE reference IS NULL"
            );
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
