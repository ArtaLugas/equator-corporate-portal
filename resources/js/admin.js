// ============================================================
// ADMIN bundle — panel admin/CMS.
// Memuat semua yang dibutuhkan admin: Alpine, Lucide, CKEditor, ApexCharts, axios.
// ============================================================

import './bootstrap';
import './ckeditor';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import ApexCharts from 'apexcharts';
// Lucide: registry tree-shakeable (resources/icons/icons.json) + sanitizer fallback.
import './lucide';

// Expose ApexCharts globally for inline dashboard scripts (no CDN).
window.ApexCharts = ApexCharts;

window.Alpine = Alpine;

Alpine.plugin(collapse);
Alpine.plugin(intersect);

Alpine.start();
