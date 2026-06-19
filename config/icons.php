<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Daftar ikon Lucide — DIBACA dari satu source of truth.
|--------------------------------------------------------------------------
| Sumber: resources/icons/icons.json (sama dengan yang dipakai bundle JS).
| JANGAN edit array di sini — tambah ikon cukup di icons.json lalu jalankan
| `npm run dev` / `npm run build`.
|
|  - 'lucide' : hanya entri "cms": true → untuk DROPDOWN admin.
|  - 'all'    : SEMUA ikon ter-bundle (termasuk cms:false) → untuk VALIDASI
|               komponen <x-icon> saat me-render (mis. ikon UI arrow-up-right).
|
| Catatan: di production jalankan `php artisan config:cache` agar file JSON
| tidak dibaca setiap request.
*/

$path = resource_path('icons/icons.json');
$data = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];

$all = [];
$lucide = [];
foreach ($data['icons'] ?? [] as $icon) {
    $name = $icon['name'] ?? null;
    if (! $name) {
        continue;
    }
    $label = $icon['label'] ?? Str::headline(str_replace('-', ' ', $name));

    $all[$name] = $label;

    if (($icon['cms'] ?? true) !== false) {
        $lucide[$name] = $label; // ditawarkan di CMS.
    }
}

return [
    // Ikon fallback bila nama tidak dikenal (server & client).
    'fallback' => $data['fallback'] ?? 'circle-help',

    // Subset untuk dropdown admin: kebab-name => Label.
    'lucide' => $lucide,

    // Semua ikon ter-bundle (untuk validasi render <x-icon>).
    'all' => $all,
];
