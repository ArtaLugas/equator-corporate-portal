// ============================================================
// Runtime Lucide bersama (public + admin).
// - Registry di-generate dari resources/icons/icons.json (tree-shakeable).
// - Sanitizer: nama ikon tak dikenal diganti fallback SEBELUM render
//   → mencegah error "icon name was not found in the provided icons object".
// ============================================================

import { createIcons, icons, names, FALLBACK_ICON } from './lucide-icons.generated';

const allowed = new Set(names);

// Ganti data-lucide yang tidak ada di whitelist dengan ikon fallback.
const sanitize = (root = document) => {
    root.querySelectorAll('[data-lucide]').forEach((el) => {
        const name = el.getAttribute('data-lucide');
        if (name && !allowed.has(name)) {
            el.setAttribute('data-lucide', FALLBACK_ICON);
        }
    });
};

const render = (opts = {}) => {
    sanitize();
    return createIcons({ icons, ...opts });
};

// API global yang kompatibel dengan pemakaian lama: window.lucide.createIcons()
window.lucide = { createIcons: render };

if (document.readyState !== 'loading') {
    render();
} else {
    document.addEventListener('DOMContentLoaded', render);
}

export { render as createIcons, icons };
