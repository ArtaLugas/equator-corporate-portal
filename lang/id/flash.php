<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan error flash admin (penyebab + cara mengatasi)
    |--------------------------------------------------------------------------
    | Dipakai oleh helper friendly_error() (dipetakan dari exception) dan oleh
    | guard eksplisit di controller admin. Tiap pesan menjelaskan MENGAPA gagal
    | dan APA yang harus dilakukan berikutnya.
    */

    // friendly_error() — dipetakan dari exception database/runtime
    'error_fk' => 'Tidak dapat diselesaikan karena data ini masih terhubung dengan data lain. Hapus atau pindahkan data terkait terlebih dahulu, lalu coba lagi.',
    'error_duplicate' => 'Data dengan nilai yang sama (misalnya nama atau slug) sudah ada. Gunakan nilai yang berbeda, lalu coba lagi.',
    'error_too_long' => 'Salah satu nilai terlalu panjang untuk kolomnya. Persingkat lalu coba lagi.',
    'error_db' => 'Terjadi kesalahan basis data saat memproses permintaan Anda. Silakan coba lagi — jika masih berlanjut, hubungi administrator.',
    'error_generic' => 'Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi — jika masih berlanjut, hubungi administrator.',

    // guard eksplisit
    'none_selected' => 'Belum ada item yang dipilih. Centang minimal satu baris terlebih dahulu, lalu coba lagi.',
    'in_use' => 'Tidak dapat dihapus karena masih digunakan oleh data lain (termasuk yang ada di Trash). Hapus atau hapus permanen data terkait terlebih dahulu, lalu coba lagi.',
    'last_super_admin' => 'Ini akun Super Admin terakhir, sehingga tidak dapat dihapus. Jadikan admin lain sebagai Super Admin terlebih dahulu, lalu coba lagi.',

];
