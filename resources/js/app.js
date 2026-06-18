// ============================================================
// PUBLIC bundle — situs publik.
// Sengaja TANPA CKEditor & ApexCharts (keduanya admin-only) agar
// pengunjung publik tidak mengunduh dependensi berat yang tak dipakai.
// ============================================================

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
// Lucide: registry tree-shakeable (resources/icons/icons.json) + sanitizer fallback.
import './lucide';

window.Alpine = Alpine;

Alpine.plugin(collapse);
Alpine.plugin(intersect);

Alpine.start();
