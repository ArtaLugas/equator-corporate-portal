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
        Schema::table('teams', function (Blueprint $table) {

            // Add email column (after bio) if it does not exist yet.
            if (! Schema::hasColumn('teams', 'email')) {
                $table->string('email', 191)
                    ->nullable()
                    ->after('bio');
            }

            // Add soft deletes support.
            if (! Schema::hasColumn('teams', 'deleted_at')) {
                $table->softDeletes();
            }

            // Align display order default with the rest of the system.
            $table->smallInteger('display_order')
                ->default(1)
                ->change();

            // Make the primary identity fields required.
            $table->string('name', 191)->nullable(false)->change();
            $table->string('position', 191)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {

            if (Schema::hasColumn('teams', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('teams', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            $table->smallInteger('display_order')
                ->default(0)
                ->change();

            $table->string('name', 191)->nullable()->change();
            $table->string('position', 191)->nullable()->change();
        });
    }
};
