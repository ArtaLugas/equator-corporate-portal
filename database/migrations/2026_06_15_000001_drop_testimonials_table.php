<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel testimonials secara permanen (fitur testimonial dihentikan).
     */
    public function up(): void
    {
        Schema::dropIfExists('testimonials');

        // Bersihkan catatan migration lama — file create-nya sudah dihapus dari repo.
        DB::table('migrations')
            ->where('migration', '2026_05_19_073921_create_testimonials_table')
            ->delete();
    }

    /**
     * Tidak dibuat ulang: fitur testimonial telah dihapus dari aplikasi.
     */
    public function down(): void
    {
        // no-op
    }
};
