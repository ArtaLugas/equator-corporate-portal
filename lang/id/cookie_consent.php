<?php

return [

    'title' => 'Kami menghargai privasi Anda',
    'body' => 'Kami menggunakan cookie untuk menjaga keamanan situs, mengingat pilihan Anda, dan memahami cara situs digunakan. Anda dapat menerima semua, menolak yang opsional, atau memilih apa yang diizinkan.',
    'policy_link' => 'Baca Kebijakan Cookie kami',

    'accept_all' => 'Terima semua',
    'reject_optional' => 'Tolak opsional',
    'customize' => 'Sesuaikan',
    'save' => 'Simpan preferensi',
    'back' => 'Kembali',

    'preferences' => 'Preferensi Cookie',
    'always_on' => 'Selalu aktif',

    // Keyed by the category ids in config/cookie_consent.php.
    'categories' => [
        'necessary' => [
            'label' => 'Necessary',
            'description' => 'Diperlukan agar situs berfungsi — sesi, keamanan, anti-spam, dan menyimpan pilihan Anda.',
        ],
        'analytics' => [
            'label' => 'Analytics',
            'description' => 'Memungkinkan kami memakai Google Analytics (GA4) untuk memahami penggunaan situs secara agregat. Hanya dimuat jika Anda mengizinkannya.',
        ],
        'marketing' => [
            'label' => 'Marketing',
            'description' => 'Tidak digunakan saat ini. Disediakan untuk pengukuran iklan atau kampanye di masa depan.',
        ],
        'preferences' => [
            'label' => 'Preferences',
            'description' => 'Mengingat pilihan yang mempersonalisasi pengalaman Anda.',
        ],
    ],

];
