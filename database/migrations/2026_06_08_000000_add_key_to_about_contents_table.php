<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom identifier mesin yang stabil (terpisah dari label `title`).
        Schema::table('about_contents', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('section_id');
        });

        // 2. Backfill key dari title yang ada (snake_case).
        foreach (DB::table('about_contents')->get() as $row) {
            DB::table('about_contents')
                ->where('id', $row->id)
                ->update(['key' => Str::snake(Str::lower(trim((string) $row->title)))]);
        }

        // 3. Rename key panjang menjadi identifier pendek & bermakna.
        DB::table('about_contents')
            ->where('key', 'safeguarding_sustainable_future')
            ->update(['key' => 'who_we_are']);

        // 4. Jamin keunikan key dalam satu section (boleh sama antar section berbeda).
        Schema::table('about_contents', function (Blueprint $table) {
            $table->unique(['section_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('about_contents', function (Blueprint $table) {
            $table->dropUnique(['section_id', 'key']);
            $table->dropColumn('key');
        });
    }
};
