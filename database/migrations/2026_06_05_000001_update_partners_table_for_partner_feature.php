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
        Schema::table('partners', function (Blueprint $table) {

            // Add publishing status (after website) if it does not exist yet.
            if (! Schema::hasColumn('partners', 'status')) {
                $table->enum('status', [
                    'active',
                    'inactive',
                ])
                    ->default('active')
                    ->after('website');
            }

            // Add soft deletes support.
            if (! Schema::hasColumn('partners', 'deleted_at')) {
                $table->softDeletes();
            }

            // Align display order default with the rest of the system.
            $table->smallInteger('display_order')
                ->default(1)
                ->change();

            // Make the partner name required.
            $table->string('name', 191)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {

            if (Schema::hasColumn('partners', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('partners', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            $table->smallInteger('display_order')
                ->default(0)
                ->change();

            $table->string('name', 191)->nullable()->change();
        });
    }
};
