<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalize existing rows so display_order is sequential & unique
        //    (existing data may contain duplicates, e.g. several "1"s).
        $rows = DB::table('about_sections')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get(['id']);

        $order = 1;
        foreach ($rows as $row) {
            DB::table('about_sections')->where('id', $row->id)->update(['display_order' => $order++]);
        }

        // 2. Now the unique constraint can be safely added.
        Schema::table('about_sections', function (Blueprint $table) {
            $table->unique('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_sections', function (Blueprint $table) {
            $table->dropUnique(['display_order']);
        });
    }
};
