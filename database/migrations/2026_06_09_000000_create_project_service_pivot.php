<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pivot many-to-many project <-> service.
        Schema::create('project_service', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'service_id']);
        });

        // 2. Backfill dari kolom service_id lama (termasuk project yang soft-deleted).
        if (Schema::hasColumn('projects', 'service_id')) {
            foreach (DB::table('projects')->whereNotNull('service_id')->get(['id', 'service_id']) as $row) {
                DB::table('project_service')->insertOrIgnore([
                    'project_id' => $row->id,
                    'service_id' => $row->service_id,
                ]);
            }

            // 3. Lepas kolom service_id (relasi kini lewat pivot).
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            });
        }
    }

    public function down(): void
    {
        // Kembalikan kolom service_id (nullable) lalu isi dari service pertama tiap project.
        if (! Schema::hasColumn('projects', 'service_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('service_id')->nullable()->after('id')
                    ->constrained('services')->nullOnDelete();
            });

            foreach (DB::table('project_service')->orderBy('project_id')->get() as $row) {
                DB::table('projects')->where('id', $row->project_id)
                    ->whereNull('service_id')
                    ->update(['service_id' => $row->service_id]);
            }
        }

        Schema::dropIfExists('project_service');
    }
};
