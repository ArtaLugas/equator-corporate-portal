// ============================================================
// GENERATOR REGISTRY IKON LUCIDE
// Membaca source of truth: resources/icons/icons.json
// Menghasilkan: resources/js/lucide-icons.generated.js (tree-shakeable)
//
// Dipanggil otomatis lewat npm "predev" & "prebuild".
// Jalankan manual: `npm run icons`
// ============================================================

import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import * as lucide from 'lucide'; // hanya untuk VALIDASI saat build — tidak ikut ke bundle.

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const SOURCE = resolve(root, 'resources/icons/icons.json');
const OUTPUT = resolve(root, 'resources/js/lucide-icons.generated.js');

// kebab-case → PascalCase (cocok dgn nama export Lucide). "building-2" → "Building2".
const toPascal = (kebab) =>
    kebab
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');

const config = JSON.parse(readFileSync(SOURCE, 'utf8'));
const fallback = config.fallback || 'circle-help';

// Kumpulkan nama unik (sertakan fallback agar pasti ter-bundle).
const names = [...new Set([...config.icons.map((i) => i.name), fallback])].sort();

// Validasi: pastikan setiap ikon benar-benar ada di Lucide versi terpasang.
const invalid = names.filter((name) => !lucide[toPascal(name)]);
if (invalid.length) {
    console.error('\n\x1b[31m[icons] Nama ikon berikut TIDAK ADA di Lucide:\x1b[0m');
    for (const name of invalid) console.error(`  - "${name}"  (dicari sebagai "${toPascal(name)}")`);
    console.error('\nPerbaiki resources/icons/icons.json — cek ejaan di https://lucide.dev\n');
    process.exit(1);
}

const pascal = names.map(toPascal);

const file = `// ⚠️  FILE INI DI-GENERATE OTOMATIS — JANGAN DIEDIT MANUAL.
// Sumber kebenaran: resources/icons/icons.json
// Regenerate: npm run icons  (otomatis saat npm run dev / build)

import {
    createIcons,
${pascal.map((p) => `    ${p},`).join('\n')}
} from 'lucide';

export const icons = {
${pascal.map((p) => `    ${p},`).join('\n')}
};

// Daftar nama kebab-case yang valid (untuk sanitizer fallback di runtime).
export const names = ${JSON.stringify(names)};

export const FALLBACK_ICON = ${JSON.stringify(fallback)};

export { createIcons };
`;

mkdirSync(dirname(OUTPUT), { recursive: true });
writeFileSync(OUTPUT, file);
console.log(`\x1b[32m[icons]\x1b[0m ${names.length} ikon → resources/js/lucide-icons.generated.js`);
