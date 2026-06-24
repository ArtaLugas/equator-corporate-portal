# RUNBOOK — Safe Overwrite Deploy to cPanel (FileZilla)

Replacing an **existing live website** on cPanel shared hosting with the Equator
Corporate Portal, deploying via **FileZilla (FTP)** + cPanel File Manager.

> **Golden rules**
> - **Never delete the old site until the new one is verified.** Rename, don't overwrite.
> - **Build a brand-new MySQL database.** Do not reuse the old site's DB.
> - **Upload one ZIP, extract on the server.** Per-file FTP of `vendor/` (thousands
>   of tiny files) is slow and drops connections.
> - **Document root must point at `…/public`** — never expose the project root (it holds `.env`).
>
> Companion docs: [`deploy/README.md`](README.md) (canonical setup),
> [`docs/go-live-checklist.md`](../docs/go-live-checklist.md) (D1–D5 sign-off),
> [`docs/backup-recovery.md`](../docs/backup-recovery.md) (restore SOP),
> [`deploy/env.production.template`](env.production.template) (fill-ready `.env`).

---

## Phase 0 — Pre-flight (on your machine, before touching the server)

- [ ] Working tree is clean and pushed (`git status`), on the commit you intend to deploy.
- [ ] Build assets and prod dependencies locally (cPanel has no Node):
  ```bash
  npm ci && npm run build                          # → public/build (manifest + hashed assets)
  composer install --no-dev --optimize-autoloader  # → prod vendor/
  ```
  Verify `public/build/manifest.json` exists and `vendor/` was rebuilt without dev packages.
- [ ] Fill [`deploy/env.production.template`](env.production.template) values you already
      know (APP_URL, Turnstile keys). DB name/user/password come in Phase 2.
- [ ] Confirm the host's PHP is **8.2** and required extensions are enabled
      (cPanel → MultiPHP Manager + INI Editor): `pdo_mysql, mbstring, openssl,
      tokenizer, xml, ctype, json, bcmath, fileinfo, gd`. Raise
      `upload_max_filesize`/`post_max_size` to ≥ 20M.

### Build the upload ZIP

Create one ZIP of the project **including `vendor/` and `public/build/`** but
**excluding** local-only junk. From the project root:

```bash
# Exclude VCS, local env, node sources, local storage state, and OS cruft.
zip -r ../equator-deploy.zip . \
  -x ".git/*" ".env" "node_modules/*" \
     "storage/logs/*" "storage/framework/cache/*" \
     "storage/framework/sessions/*" "storage/framework/views/*" \
     "storage/app/backups/*" ".DS_Store" "*/.DS_Store"
```

> Keep the `storage/` **folder structure** (Laravel needs the empty
> `framework/{cache,sessions,views}` dirs). The excludes above drop only their
> *contents*. If your zip tool flattens empty dirs, recreate them on the server
> in Phase 5.

---

## Phase 1 — Full backup of the EXISTING site (do not skip)

Even though the old site is being retired, back it up so a rollback is possible.

- [ ] **Files:** cPanel → **File Manager** → select `public_html` → Compress → `public_html_backup_<date>.zip`. Download it via FileZilla. (Or use **Backup Wizard → Full Account Backup** if quota allows.)
- [ ] **Database (old site):** cPanel → **phpMyAdmin** → select the old DB → **Export** → *Quick / SQL* → download the `.sql`.
- [ ] Note the old site's DB name/user (in case anything references it) — but you will **not** reuse it.
- [ ] Store both downloads off the server (local disk + cloud). Verify the ZIP opens and the `.sql` is non-empty.

---

## Phase 2 — Create a NEW database (never reuse the old one)

cPanel → **MySQL Databases**:

- [ ] Create database: `<cpaneluser>_equator`.
- [ ] Create user: `<cpaneluser>_equator` with a strong password.
- [ ] **Add user to database → grant ALL PRIVILEGES.**
- [ ] Record name / user / password into your filled `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

---

## Phase 3 — Rename the old site, stage the new one

The app should live **outside** `public_html`; only its `public/` is web-exposed.
This keeps `.env` and source code off the web.

- [ ] In File Manager, **rename** `public_html` → `public_html_OLD` (instant rollback path; nothing deleted).
- [ ] Create a fresh empty `public_html`.
- [ ] Create an app directory **beside** `public_html`, e.g. `/home/<cpaneluser>/equator-app`.

> **Heads-up:** while `public_html` is renamed, the old site is offline. If you
> need zero downtime, instead deploy fully into `equator-app`, verify via a temp
> subdomain, then flip the document root last (Phase 6, option A). The rename
> approach below is simplest and fine for a planned maintenance window.

---

## Phase 4 — Upload + extract the ZIP

- [ ] FileZilla → upload `equator-deploy.zip` into `/home/<cpaneluser>/equator-app/`.
- [ ] File Manager → select the ZIP → **Extract** into `equator-app/`.
- [ ] Confirm `equator-app/artisan`, `equator-app/vendor/`, `equator-app/public/build/` are present.
- [ ] Delete the ZIP from the server once extracted.

---

## Phase 5 — Document root → `…/public`

Pick **one** (Option A is cleaner):

**Option A — repoint Document Root (preferred).**
cPanel → **Domains** → manage the domain → set **Document Root** to
`/home/<cpaneluser>/equator-app/public`. The empty `public_html` from Phase 3 is
then unused (you may leave or remove it).

**Option B — serve from `public_html`.**
Move the **contents** of `equator-app/public/` into `public_html/`, then edit
`public_html/index.php` so its two `require` paths point at the app folder:
```php
require __DIR__.'/../equator-app/vendor/autoload.php';
$app = require_once __DIR__.'/../equator-app/bootstrap/app.php';
```
(Also ensure `public_html/build/` and `public_html/storage` resolve correctly.)

- [ ] Verify the document root resolves to Laravel's `public/` (the one with `index.php` + `build/`).

---

## Phase 6 — One-time server setup (cPanel → Terminal)

```bash
cd /home/<cpaneluser>/equator-app

cp deploy/env.production.template .env     # then edit .env with real DB creds etc.
nano .env                                  # fill DB_*, APP_URL, Turnstile, SESSION_SECURE_COOKIE=true

php artisan key:generate                   # only if APP_KEY is empty
php artisan migrate --force                # builds schema in the NEW empty DB

# --- Seed initial content (FIRST/EMPTY install only) ---------------------------
# IMPORTANT: do NOT use a bare `php artisan db:seed`. DatabaseSeeder also runs
# MessageSeeder (legacy inbox messages) — we want a CLEAN production inbox — and
# it does NOT run the Indonesian translations, so a plain seed yields an
# English-only site. Run the content seeders explicitly (skips MessageSeeder),
# then the aggregate Indonesian seeder for the *_id columns:
for S in AdminSeeder SettingSeeder ServiceCategorySeeder ServiceSeeder \
  NewsCategorySeeder NewsSeeder PartnerSeeder TeamSeeder HeroBannerSeeder \
  CoreValueSeeder AboutHistorySeeder ProjectSeeder KeyMetricSeeder \
  OfficeLocationSeeder FaqSeeder CompanyCredentialSeeder; do
  php artisan db:seed --class="$S" --force
done
php artisan db:seed --class=IndonesianTranslationSeeder --force   # fills all *_id content
# -------------------------------------------------------------------------------

php artisan storage:link                   # public/storage → storage/app/public
php artisan config:cache route:cache view:cache
```

> **Why not `db:seed`?** `DatabaseSeeder::run()` seeds only English (`*_en`) base
> content and also loads legacy inbox messages. The Indonesian columns are filled
> by `IndonesianTranslationSeeder` (which is **not** wired into `DatabaseSeeder`).
> The loop above seeds the real company content in dependency order, omits
> `MessageSeeder` (clean inbox), and the final line adds the EN+ID translations.
> All seeders are idempotent (`updateOrCreate`), so a re-run is safe.

> **Phase C (optional, separate, gated):** dropping the legacy single-language
> columns is a **distinct, destructive** go-live step that `migrate --force` does
> **not** run. Do it only after UAT sign-off, following its own runbook
> [`docs/phase-c-execution.md`](../docs/phase-c-execution.md). A fresh install can
> defer it indefinitely — the app runs fine with the legacy columns present.

- [ ] If `storage:link` is blocked (symlinks disabled), create it manually:
      `ln -s /home/<cpaneluser>/equator-app/storage/app/public /home/<cpaneluser>/equator-app/public/storage`
- [ ] If your ZIP dropped the empty framework dirs, recreate + permission them:
      `mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache`
- [ ] Set the admin password (or rotate the seeded one) before sharing the URL.

---

## Phase 7 — Scheduler cron (drives queue + backups + prune)

cPanel → **Cron Jobs** → add **one** entry, every minute (confirm the PHP binary path):

```cron
* * * * * /usr/local/bin/ea-php82 /home/<cpaneluser>/equator-app/artisan schedule:run >> /dev/null 2>&1
```

- [ ] `php artisan schedule:list` shows `queue:work`, `model:prune`, and the three `backup:run` lines.
- [ ] Submit a test contact form (Phase 8) and confirm the `jobs` table drains and mail sends.

---

## Phase 8 — Smoke test (before announcing go-live)

- [ ] `https://<domain>/` — homepage renders; CSS/JS load (assets from `public/build`); **Trusted Credentials** band displays as a centered wrapping row.
- [ ] `https://<domain>/id` — Indonesian locale renders; language switch works.
- [ ] Mixed-content check: page is fully HTTPS (no `http://` asset warnings in console).
- [ ] **Admin login** works; dashboard + one list page (e.g. News) render with styled assets.
- [ ] **Contact form submit** → visitor **auto-reply** arrives **and** office **notification** arrives (confirms Brevo + queue + cron).
- [ ] Download one **Company Document** — file serves via the storage symlink.
- [ ] Response headers include `Strict-Transport-Security` (HSTS) and a `Content-Security-Policy`.
- [ ] `APP_DEBUG=false` confirmed — force a 404/500 and verify the custom error page (no stack trace).

---

## Phase 9 — Decommission the old site

Only **after** smoke tests pass:

- [ ] Keep `public_html_OLD` and the old DB for a grace period (e.g. 7–14 days).
- [ ] After the grace period, remove `public_html_OLD` and drop the old DB if nothing depends on them.

---

## Future re-deploys (code updates) — DO NOT overwrite these

When pushing later code changes, re-upload changed files **but never overwrite**:

- 🔒 **`.env`** — live secrets (APP_KEY, DB, Turnstile).
- 🔒 **`storage/app/public/`** — all uploaded media (images, documents).
- 🔒 **`storage/app/backups/`** — generated backups.
- 🔒 **`public/storage`** symlink — leave as-is.

Re-deploy steps (Terminal):
```bash
php artisan migrate --force
php artisan config:cache route:cache view:cache
```
The cron-driven `queue:work` picks up new code on its next run. Do **not** run
`db:seed` on an existing populated install (it is first-install only).

---

## Quick rollback (if Phase 8 fails badly)

1. File Manager → rename new `public_html` (or the app's document-root binding) aside.
2. Rename `public_html_OLD` → `public_html` (or repoint Document Root back).
3. The old site is live again; the old DB was never touched.
4. Investigate, fix, re-attempt — the new DB and `equator-app/` remain intact for a retry.
