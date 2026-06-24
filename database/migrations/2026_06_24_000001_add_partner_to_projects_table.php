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
        Schema::table('projects', function (Blueprint $table) {

            if (! Schema::hasColumn('projects', 'partner')) {

                // Partner organisation (e.g. funding/implementing partner). Plain,
                // non-translatable varchar — mirrors `client_name`. Nullable so
                // existing rows are not violated.
                $table->string('partner', 191)->nullable()->after('client_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            if (Schema::hasColumn('projects', 'partner')) {
                $table->dropColumn('partner');
            }
        });
    }
};
