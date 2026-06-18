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

            if (! Schema::hasColumn('projects', 'service_id')) {

                // Nullable at DB level so existing rows are not violated;
                // requirement ("each project must belong to a service") is
                // enforced at the validation layer in ProjectController.
                $table->foreignId('service_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('services')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            if (Schema::hasColumn('projects', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }
};
