# Phase C Execution Runbook (i18n Contract)

> **Phase C is the FINAL, DESTRUCTIVE step of the multilingual rollout.** It drops
> the legacy single-language columns and enforces `NOT NULL` on the required
> default-locale columns. It is part of **production readiness / go-live**, not
> feature implementation.

- **Migration:** `database/migrations/phase-c/2026_06_22_000009_i18n_contract_drop_legacy_and_enforce.php`
- **Why a subfolder:** Laravel's migrator scans only `database/migrations` (non-recursive), so this file is **never** run by `php artisan migrate`, `migrate:fresh`, or the test suite's `RefreshDatabase`. It runs **only** when invoked explicitly with `--path`.
- **Reference:** `docs/multilingual-guide.md` §7.

---

## 0. Gate — ALL must be ✅ before running

- [ ] Every translatable module has adopted the trait and is live: **Service, Service Categories, Project, News, About (sections + contents + histories), FAQ, Core Values, Teams, Hero Banners, Key Metrics, Company Documents, Office Locations**. *(All 14 registry tables — Multilingual v1 shipped the first 8; v1.1 added Service Categories, Hero Banners, Key Metrics, About Histories, Company Documents, Office Locations.)*

> **Not part of Phase C:** `company_credentials` and `company_credential_items` (added 2026-06-23) are **new** tables created already in their final multilingual shape — the anchor `title_en` is `NOT NULL` from creation and no legacy single-language column ever existed. There is nothing to expand → backfill → contract, so they have **no Phase A/B/C migrations and no entry in this runbook**. They are registered in `config/translatable.php` and `TranslationProgress::MODULES` like every other module.

- [ ] Every admin form, FormRequest, public view, and search is localized.
- [ ] Static UI strings extracted to `lang/` (done in v1).
- [ ] Verified on **staging**: EN renders identically to pre-i18n; ID content filled where expected; fallback (ID → EN) works on every module.
- [ ] **UAT signed off** by the business owner.
- [ ] Confirmed there are no remaining reads/writes of any legacy column anywhere (app code, seeders, exports, reports, queued jobs).
- [ ] **Full database backup taken and its restorability verified.**
- [ ] Final go-ahead approval recorded.

> If any box is unchecked, **do not run Phase C.** The legacy columns remain the rollback safety net until every box is checked.

---

## 1. Pre-execution

1. [ ] Schedule a short **maintenance window** (the ALTERs are fast on this data size, but plan for it).
2. [ ] Put the app in maintenance mode: `php artisan down --render="errors::503"`.
3. [ ] Take a **fresh, final DB backup** immediately before running (label it `pre-phase-c`).
4. [ ] Confirm the deployed application code is the i18n version (models use `HasTranslations`; nothing reads legacy columns).
5. [ ] Dry-run on a **staging copy of production data** first and complete §3 verification there.

## 2. Execute

```bash
php artisan migrate --path=database/migrations/phase-c --force
```

Expected output: the single `..._i18n_contract_drop_legacy_and_enforce` migration runs `DONE`.

## 3. Post-execution verification

- [ ] `php artisan migrate:status --path=database/migrations/phase-c` → shows the migration as **Ran**.
- [ ] Legacy columns are **gone** and required `*_en` columns are **NOT NULL**, e.g.:
  ```bash
  php artisan tinker --execute="echo Schema::hasColumn('services','name') ? 'LEGACY STILL PRESENT' : 'dropped';"
  ```
  (Expect `dropped`. Repeat for a few: `projects.name`, `news.title`, `faqs.answer`, `teams.position`.)
- [ ] Smoke test the public site in **EN and ID**: home, about, services (+detail), projects (+detail), news (+detail), faq. All `200`; content + fallback correct.
- [ ] Smoke test the **admin CMS**: open + save one record per module; confirm create/edit/translation-status still work.
- [ ] Run the automated suite against a staging DB if available (it uses its own schema via `RefreshDatabase`, so it is unaffected, but it confirms nothing else regressed).
- [ ] `php artisan up` (exit maintenance mode).

## 4. Rollback (if a problem is found)

The migration's `down()` is a **true, lossless rollback**: it re-creates the legacy columns (nullable), copies `*_en` back into them, and relaxes the enforced `*_en` columns to nullable again.

```bash
php artisan migrate:rollback --path=database/migrations/phase-c --force
```

Then verify:
- [ ] Legacy columns are back (e.g. `Schema::hasColumn('services','name')` → true) and populated from `*_en`.
- [ ] Required `*_en` columns are nullable again.

> **If `down()` cannot run** (e.g. a partial failure mid-migration) or data integrity is in doubt, **restore the `pre-phase-c` backup**. That is why the backup in §1.3 is mandatory.

## 5. After a successful Phase C

- [ ] The legacy columns are permanently gone; rollback is no longer "free" (it would require the backup). Note this in the deployment log.
- [ ] (Optional, separate change) The relaxed-nullable legacy columns no longer exist, so any leftover defensive `?? ''`/`(string)` casts tied to them can be revisited — track under Future Improvements, not here.

---

### Quick reference

| Action | Command |
|---|---|
| Run Phase C | `php artisan migrate --path=database/migrations/phase-c --force` |
| Roll back Phase C | `php artisan migrate:rollback --path=database/migrations/phase-c --force` |
| Status | `php artisan migrate:status --path=database/migrations/phase-c` |

**NOT NULL is enforced on the required anchors only:** `services.name_en`, `projects.name_en`, `news.title_en`, `about_sections.name_en`, `faqs.question_en`, `faqs.answer_en`, `core_values.title_en`, `teams.position_en`, `service_categories.name_en`, `hero_banners.title_en`, `key_metrics.label_en`, `about_histories.title_en`, `company_documents.title_en`, `office_locations.name_en`. Optional translatable columns (descriptions, bodies, meta, bio, subtitle, button_text, address, about_contents.title/content) stay nullable.
