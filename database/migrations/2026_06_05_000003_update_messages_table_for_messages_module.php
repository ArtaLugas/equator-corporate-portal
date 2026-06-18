<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {

            if (! Schema::hasColumn('messages', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }

            if (! Schema::hasColumn('messages', 'company')) {
                $table->string('company', 191)->nullable()->after('phone');
            }

            if (! Schema::hasColumn('messages', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('messages', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('replied_at');
            }

            // Original migration only created `created_at`; add `updated_at`
            // so Eloquent timestamps work as expected.
            if (! Schema::hasColumn('messages', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }

            if (! Schema::hasColumn('messages', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Extend the status enum to include "archived".
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('status', [
                'unread',
                'read',
                'replied',
                'archived',
                'spam',
            ])->default('unread')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {

            foreach (['phone', 'company', 'replied_at', 'archived_at', 'updated_at'] as $column) {
                if (Schema::hasColumn('messages', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('messages', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->enum('status', [
                'unread',
                'read',
                'replied',
                'spam',
            ])->default('unread')->change();
        });
    }
};
