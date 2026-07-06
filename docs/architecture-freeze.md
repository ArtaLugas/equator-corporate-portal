# Architecture Freeze — Project Constitution

> **Status:** 🔒 FROZEN
> **Milestone:** **Multilingual v1 — Completed**
> **Freeze date:** 2026-06-22
> **Scope of freeze:** the entire internationalization (i18n) subsystem and the per-module multilingual pattern.

This document is the authoritative reference after the Multilingual v1 milestone. It defines what is frozen, what may still change, what was deliberately deferred, and how every future change must be classified. When in doubt, this document wins. Companion docs: [`docs/multilingual-guide.md`](multilingual-guide.md) (authoritative how-to), [`docs/multilingual-checklist.md`](multilingual-checklist.md) (per-module standard), [`docs/phase-c-execution.md`](phase-c-execution.md) (go-live runbook).

---

## 1. Project status at freeze

| Item | State |
|---|---|
| Milestone | **Multilingual v1 — Completed** |
| Default locale | English (`en`), served at root (`/services`) |
| Additional locale | Indonesian (`id`), URL-prefixed (`/id/services`) |
| Translatable modules | **14** — Services, Service Categories, Projects, News, About Sections, About Contents, About Histories, FAQ, Core Values, Teams, Hero Banners, Key Metrics, Company Documents, Office Locations |
| Translation strategy | Suffix columns (`name_en` / `name_id`) |
| Phase A/B (expand + backfill) | ✅ Run on all 14 tables |
| Phase C (contract) | ⛔ Prepared & complete for all 14 tables, **gated, NOT run** (`database/migrations/phase-c/`) |
| Translation content (ID) | First 8 modules populated; 6 newer modules at 0% (tracked — see Deferred) |
| Monitoring | `php artisan i18n:status` + admin **Translation Progress** page cover all 14 modules |
| Test baseline | **8 failed / 203 passed** — the 8 are **pre-existing** (Turnstile-gated: AuthTest ×3, contact ×2; plus KeyMetric homepage ×3 cache/string). "Green" = these 8 only. |

A change is only "regression-free" if the failing set stays exactly these 8.

---

## 2. Architecture decisions — DO NOT change without explicit approval

These are load-bearing. Changing any of them is an **Architectural Change** (§6) and requires explicit written approval before any code is written.

1. **Suffix-column translations.** Per-locale columns `<field>_<locale>` (e.g. `name_en`, `name_id`). **NOT** `spatie/laravel-translatable`, **NOT** JSON columns, **NOT** a translations side-table.
2. **English is the default locale at the root.** Indonesian is the additional, URL-prefixed locale. EN URLs are canonical (`/about`); `/en/...` redirects to root.
3. **Slug stays a single column**, generated from the **default-locale** field (`*_en`). Public URLs are locale-stable and identical across locales. Slug regeneration is gated by `config('cms.auto_regenerate_slug')`.
4. **`config/translatable.php` is the single source of truth** for which fields are translatable and which are HTML (Purifier-sanitized) per table. Nothing else hardcodes this list.
5. **`config/locales.php` is the locale registry** (default + supported, with `native`/`dir`/`iso`).
6. **Fallback mechanism is fixed:** a blank non-default translation falls back to the default locale (`*_en`). This is intentional and must not be "fixed" away.
7. **Routing = Plain + Localized (P+L) dual registration.** A plain **unnamed** group matches default-locale URLs; a `{locale?}`-prefixed **named** group owns route names. `URL::defaults(['locale' => ''])` keeps positional `route()` calls aligned. Do not collapse these into one group.
8. **All-or-nothing per-locale validation.** If a non-default locale is started for a record, every default-locale-filled field must be translated. Lives in `ValidatesTranslations`.
9. **Per-field language designations are deliberate** and must not be flipped casually: e.g. `Team.name` is **single-language**; `FAQ.answer` is **plain text** (not HTML); `CoreValue.description`, `AboutContent.content`, `*.description`/`content` rich fields are **HTML**; `KeyMetric.value` and `OfficeLocation.phone/email/map_embed` stay single-language.
10. **Phase A → B → C migration workflow** (expand → backfill → contract). Phase C is **destructive and gated**: it lives in `database/migrations/phase-c/` so auto-discovery never runs it. Moving it out of that folder requires explicit approval.
11. **191-character ceiling** on single-line translatable fields (`varchar(191)` + `max:191`). Indonesian text is often longer than English — translations must fit, they do not get a longer column.
12. **Deliberate simplicity:** no `LocaleManager`, no `TranslationService`. Resolution lives entirely in the `HasTranslations` trait, driven by config.

---

## 3. Gold-standard components (the canonical implementation)

New work copies these; it does not reinvent them.

| Component | File | Role |
|---|---|---|
| **HasTranslations** | `app/Models/Concerns/HasTranslations.php` | Sole resolver. `$model->name` → active locale (+ fallback); auto-appends `*_en`/`*_id` to `$fillable`; Purifies HTML columns on write; `scopeSearchTranslatable`; `translationProgress()`. **A translatable field must NOT have a native `get<Field>Attribute()` accessor.** |
| **ValidatesTranslations** | `app/Http/Requests/Concerns/ValidatesTranslations.php` | Shared FormRequest trait. Each request declares `translatableSpecs()`; first spec key = required default-locale anchor; emits all-or-nothing summary under `translation_<locale>`. |
| **TranslationProgress** | `app/Support/TranslationProgress.php` | Roll-up over the `MODULES` registry (all 14). Source of truth for `i18n:status` **and** the admin Translation Progress page. Adding a module = add one row here. |
| **AppliesTranslations** | `database/seeders/Concerns/AppliesTranslations.php` | Seeder helper; fills `*_id` via Eloquent so HTML sanitization runs. Used by `IndonesianTranslationSeeder`. |
| **Locale resolution** | `app/Http/Middleware/SetLocale.php`, `locale_url()` in `app/helpers.php` | Resolves `{locale?}`, canonical redirect, path-based switcher URLs. |
| **Migration pattern** | `database/migrations/2026_06_22_*_i18n_expand_*` / `*_backfill_*` + `phase-c/` | Expand (add nullable `*_en`/`*_id`, relax legacy NOT-NULL anchor) → backfill (`legacy → *_en`) → contract (drop legacy, enforce anchors). Explicit, frozen snapshots. |
| **Admin UX** | `resources/views/components/admin/lang-tabs.blade.php`, `translation-status.blade.php`; per-module `_form.blade.php` | Language tabs + per-locale panels + all-or-nothing summary. |
| **Public UX** | `resources/views/components/public/lang-switcher.blade.php`; `layouts/public.blade.php` | Switcher, `<html lang>`, hreflang/canonical/og:locale. |
| **Reference module** | **Service** (model, `ServiceRequest`, controller slug logic, `_form`, `ServiceI18nTest`) | The template to mirror when adding a translatable module. |

**The standard procedure to add a translatable module** (see `docs/multilingual-checklist.md`): register it in `config/translatable.php` → expand + backfill migration (relax legacy NOT-NULL anchor) → `use HasTranslations` (fillable = non-translatable only, remove any HTML mutator) → FormRequest with `ValidatesTranslations` → wire controller (slug from `*_en`) → localize **index search/sort** to `*_{locale}` → lang-tabs in `_form` → seeder writes `*_en` → add to `TranslationProgress::MODULES` → add to `phase-c` `$schema` → i18n test → audit for legacy reads.

---

## 4. Allowed exceptions during freeze

The freeze blocks **architectural** change, not maintenance. The following are permitted **without** lifting the freeze, provided they follow the gold standard and stay within the frozen decisions:

- **Bug Fix** — a genuine defect that affects application behavior (e.g. a query reading a legacy column, a seeder producing blank content, a 500). Must include before/after evidence and keep the test baseline at 8.
- **Security Fix** — patching a vulnerability (advisory, sanitization gap, authz hole). May proceed promptly; document the advisory.
- **Compatibility Fix** — framework/dependency/PHP/MySQL upgrades and the minimal adaptations they force (e.g. a deprecation like `strip_tags(null)`).
- **Production Issue** — incident remediation on a running environment.

Anything that changes a §2 decision is **not** an exception — it is an Architectural Change and needs approval first.

---

## 5. Deliberately deferred items (Future Improvements — keep on roadmap)

Recorded so they are not lost. None are blockers; none are started.

1. **Fill Indonesian content** for all 14 modules — the 6 newer modules (Service Categories, Hero Banners, Key Metrics, About Histories, Company Documents, Office Locations) are at **0%** (tabs exist, content empty). Fill via admin or extend `IndonesianTranslationSeeder`.
2. **Cosmetic legacy `orderBy('name')`** (ordering only, no data loss): `Admin/ProjectController` (Service multiselect ×3), `Public/ProjectController` (services list), `Admin/AboutContentController` (AboutSection dropdown ×3). Localize to `name_<default>`.
3. **KeyMetric homepage tests** (3 pre-existing) — align seeder/test label string (`'Years Experience'` vs `HomeController` `'Years of Experience'`) **and** `Cache::flush()` in setUp to remove `public.home.data` cross-test leakage.
4. **HomeController default metric labels** (hardcoded EN fallback when no CMS metrics) → move to `lang/` keys.
5. **`strip_tags(null)` deprecation** on the service detail page (PHP 8.1+) — guard nulls.
6. **CompanyDocument seeder** — none exists; add for demo-data parity.
7. **Pre-existing Turnstile test failures** (admin login + contact, in test env) — out of i18n scope.

---

## 6. Change classification rule (mandatory after freeze)

**Every** change proposed after this freeze must declare its category up front. State the category, the rationale, and the affected gold-standard components.

| Category | Definition | Approval |
|---|---|---|
| **Bug Fix** | Corrects behavior that is demonstrably wrong; no new capability; no decision changed. | Allowed under §4. Show evidence + keep baseline green. |
| **Enhancement** | Adds capability **within** the frozen architecture (e.g. a new translatable module via the §3 procedure, a new lang string, content). | Allowed if it strictly follows the gold standard and touches no §2 decision. Note it explicitly. |
| **Refactor** | Restructures code with **no** behavior change (rename, extract, dedupe). | Allowed if behavior-preserving and the test baseline is unchanged. Must not alter a §2 decision. |
| **Architectural Change** | Touches any §2 decision, the gold-standard contracts in §3, the fallback/routing/slug/migration strategy, or the locale set. | **Blocked.** Requires explicit written approval **before** code is written; updates this document. |

Default assumption: if a change cannot be clearly placed in Bug Fix / Enhancement / Refactor, treat it as an **Architectural Change** and ask first.

---

## 7. Post-freeze changelog

Accepted changes after the freeze, with their §6 classification. Each must stay within the frozen decisions (§2) and gold-standard contracts (§3).

| Date | Change | Category | Notes |
|---|---|---|---|
| 2026-06-22 | **Error Pages v1** — branded 404/403/419/429/500/503 on a dependency-free error layout (`layouts/error.blade.php`), `lang/{en,id}/errors.php`, tests, `docs/error-pages.md`. | **Enhancement** | No exception-handler, routing, i18n, or public-layout change. Self-contained (no DB/`@vite`) for 5xx resilience. |
| 2026-06-22 | **Privacy & Cookie Consent v1** — Privacy Policy + Cookie Policy pages, config-driven cookie-consent banner, footer legal links, contact data-use disclosure, `cookie_consent()` helper. `docs/privacy-cookie-consent.md`. | **Enhancement** | UU PDP / GDPR aligned. Adds public routes + an additive banner include; `TrackVisitor` unchanged (disclose-only). No i18n decision changed. Policy texts pending legal review. |
| 2026-06-22 | **SEO & GA4 v1** — consent-gated Google Analytics 4 (5 events), Search Console verification, CMS-managed IDs (`settings.ga4_measurement_id`/`gsc_verification`), CSP allowlist, NewsArticle structured data, hreflang sitemap. `docs/seo-analytics.md`. | **Enhancement** | GA4 gated on Analytics consent; IDs from CMS (never hardcoded). Additive head includes + CSP extension; Cookie Policy updated. No i18n decision changed. |
| 2026-06-22 | **Backup & DR v1** — dependency-free tiered backups (`backup:run/clean/verify`), mysqldump + pure-PHP fallback, retention, off-site disk, integrity checks; rides the existing scheduler. `docs/backup-recovery.md`. | **Enhancement** | No new packages; no app-behaviour change. Adds 3 console commands + scheduler entries + `config/backup.php`. |
| 2026-06-22 | **Contextual lead CTA** — extracted the duplicated Service/Project detail CTA bands into a shared `<x-public.lead-cta>` component; localized the previously hardcoded project button; updated CTA copy. | **Refactor** (+ i18n fix) | DRY only — reuses the existing `?service=` contact prefill and the existing CTA design. No new pattern, no architecture/route change. |
| 2026-06-22 | **Contact auto-reply** — visitor confirmation email (bilingual, locale-aware) + unique `messages.reference` (EQ-YYYYMMDD-NNNNNN). Mailable + queued Job + Blade, mirroring the existing mail pattern. | **Enhancement** | Adds a column + Mailable/Job/view/lang; admin notification unchanged. Reuses Brevo configurator + queue. No i18n decision changed. |
| 2026-06-22 | **Lead CRM v1** — auto-captured lead-source attribution (landing_page/referrer/locale/utm_*/gclid/fbclid) via `CaptureLeadSource` middleware + `LeadSource` service; admin "Lead Information" panel + "Lead Analytics" dashboard (`LeadAnalytics` service, single source of truth). | **Enhancement** | Additive columns + service/middleware/view; `StoreContactMessageRequest` unchanged (metadata is server-derived, not user input). No i18n decision changed. |
| 2026-06-22 | **Audit remediation** (CTA/auto-reply/lead-CRM) — M1 queue async + `docs/deployment.md`; M2 `CaptureLeadSource` fail-safe try/catch; M3 privacy/cookie disclosure of lead-source; minor: drop dead `tone`, `messages.created_at` index, CTA/analytics focus-visible + aria. Deferred items → `docs/future-improvements.md`. | **Bug Fix** (+ docs) | No behaviour change beyond fail-safe + disclosure; no new feature; no i18n decision changed. |
| 2026-07-06 | **News Categories → translatable** — made the `news_categories` taxonomy bilingual following the §3 procedure: registered in `config/translatable.php` (only `name`; slug stays single, derived from `name_en`), Phase A/B expand+backfill migrations (`name_en`/`name_id`; legacy `name` already nullable → no relax step), phase-c `$schema` entry, `HasTranslations` on the model, `NewsCategoryRequest` (ValidatesTranslations), controller slug-from-`name_en` + localized index search/sort, `_form` lang-tabs, seeder → `name_en`, `TranslationProgress::MODULES` (now **17**), `NewsCategoryI18nTest`. Audited & fixed legacy reads: `NewsController` category dropdown `orderBy` ×3 + admin news-index category-name search now localized. | **Enhancement** | Mirrors **Service Categories**; touches no §2 decision. Baseline unchanged (8 failed / 273 passed). ID content left empty (fills via admin, EN fallback) — consistent with §5 deferred-content pattern. |
| 2026-06-23 | **Company Credentials module** — central registry of company credentials (LPJP/ISO/KBLI/licenses/…) with translatable title/issuer/description + plain-text child items, string-backed `category` (config-driven, no enum), full admin CRUD (FormRequest + Policy + `ImageService` + PDF attachment + inline-item repeater + trash), seeder. Surfaced on the public site **inside the About page** (`/about#credentials`, grouped by category with items inline) + a homepage "Trusted Credentials" band linking to it — **no standalone public page** (deliberately consolidated; too thin to stand alone). Two tables added to `config/translatable.php` + `TranslationProgress::MODULES` (now 16). | **Enhancement** | Strictly follows the §3 procedure mirroring **Service**; touches no §2 decision. New tables created already in final multilingual shape (anchor `title_en` NOT NULL) → **no Phase A/B/C and no phase-c entry** (no legacy column to contract). Baseline kept at 8 failed / 253 passed. |

## 8. Closing

Multilingual v1 is **complete and frozen**. The architecture above is the baseline for all subsequent milestones. To lift or amend the freeze, update this document in the same change that introduces the approved architectural change, and record who approved it.

*Authored at the close of Multilingual v1, 2026-06-22.*
