<?php

return [

    // Shared calls-to-action (used by layouts/error.blade.php).
    'cta_home' => 'Kembali ke Beranda',
    'cta_contact' => 'Hubungi Kami',

    '404' => [
        'eyebrow' => 'Error 404',
        'title' => 'Halaman tidak ditemukan',
        'message' => 'Halaman yang Anda cari tidak ada atau mungkin telah dipindahkan.',
    ],

    '403' => [
        'eyebrow' => 'Error 403',
        'title' => 'Akses ditolak',
        'message' => 'Anda tidak memiliki izin untuk melihat halaman ini.',
    ],

    '419' => [
        'eyebrow' => 'Error 419',
        'title' => 'Sesi Anda berakhir',
        'message' => 'Sesi Anda berakhir demi keamanan. Silakan muat ulang halaman dan coba lagi.',
    ],

    '429' => [
        'eyebrow' => 'Error 429',
        'title' => 'Terlalu banyak permintaan',
        'message' => 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Mohon tunggu sejenak lalu coba lagi.',
    ],

    '500' => [
        'eyebrow' => 'Error 500',
        'title' => 'Terjadi kesalahan',
        'message' => 'Terjadi kesalahan tak terduga di sisi kami. Tim kami telah diberi tahu — silakan coba lagi sebentar lagi.',
    ],

    '503' => [
        'eyebrow' => 'Pemeliharaan',
        'title' => 'Kami segera kembali',
        'message' => 'Situs sedang dalam pemeliharaan terjadwal singkat. Terima kasih atas kesabaran Anda.',
    ],

];
