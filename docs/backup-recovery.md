# Backup & Disaster Recovery

Dependency-free backup system for Laravel 11 on **shared cPanel hosting** (MySQL 5.7, public storage). Database is dumped (mysqldump, with a pure-PHP fallback when `exec` is disabled), uploaded files are archived, `.env` is captured, retention is tiered, and integrity is verified — all driven by the **single cron entry the app already uses**. Introduced after the Multilingual v1 freeze as an operational **Enhancement**.

## Strategy at a glance

| Tier | Contents | Schedule | Retention (default) |
|---|---|---|---|
| **Daily** | Database + `.env` | 02:00 every day | keep **7** |
| **Weekly** | Database + **uploaded files** (`storage/app/public`) + `.env` | Sunday 03:00 | keep **5** |
| **Monthly** | Full (DB + files + `.env`) | 1st of month, 04:00 | keep **12** |
| `backup:clean` | prune beyond retention | 05:00 daily | — |
| `backup:verify` | integrity + freshness (exit ≠ 0 → cron emails you) | 05:30 daily | — |

**Why DB-daily but files-weekly:** the database changes constantly and is tiny; uploaded files change slowly and are large. Daily full-file zips would strain shared hosting. Set `BACKUP_DAILY_FILES=true` to include files daily if you prefer.

Each run produces **one restorable zip** — `backup-{tier}-{Y-m-d-His}.zip`:

```
database.sql.gz     mysqldump (or pure-PHP fallback)
env                 a copy of .env (APP_KEY, DB & mail credentials)
files/…             storage/app/public (weekly/monthly)
manifest.json       type, timestamp, php, db, sizes, strategy
+ a .sha256 sidecar for integrity
```

## Commands

```bash
php artisan backup:run --type=daily      # or weekly | monthly
php artisan backup:clean                 # enforce retention
php artisan backup:verify                # newest backup fresh & sound? (exit code)
```

## Setup (cPanel)

1. **Cron** — already present (one entry drives everything):
   ```
   * * * * * cd /home/USER/app && php artisan schedule:run >> /dev/null 2>&1
   ```
   No extra cron is needed; the backup schedule lives in `routes/console.php`.
2. **Off-site (REQUIRED for true DR)** — local-only backups die with the server. Add an off-site disk and point backups at it:
   ```env
   BACKUP_OFFSITE_DISK=s3          # or an 'ftp'/'sftp' disk you define in config/filesystems.php
   BACKUP_OFFSITE_PATH=equator-backups
   # plus the disk's credentials (AWS_*, etc.)
   ```
   Each backup is copied there automatically. Cheapest options: an S3-compatible bucket (Backblaze B2, Wasabi, idCloudHost) or a second FTP account on a different host.
3. **Retention overrides** (optional): `BACKUP_KEEP_DAILY`, `BACKUP_KEEP_WEEKLY`, `BACKUP_KEEP_MONTHLY`.
4. **Tune for very large data**: keep `mysqldump` available (faster than the PHP fallback); exclude regenerable dirs (already excludes the Purifier cache).

Backups land in `storage/app/backups/` (git-ignored).

---

## SOP — Restore

> Goal: bring the site back from a backup zip. Time: ~10–20 min. Always restore to a **staging copy first** when possible.

1. **Get the zip** — newest from `storage/app/backups/{tier}/` (or your off-site disk).
2. **Verify integrity** before trusting it:
   ```bash
   sha256sum -c backup-daily-XXXX.zip.sha256      # must say: OK
   unzip -l backup-daily-XXXX.zip                 # shows database.sql.gz, env, manifest.json, files/…
   ```
3. **Maintenance mode:** `php artisan down`.
4. **Restore the database:**
   ```bash
   unzip backup-daily-XXXX.zip -d restore/
   gunzip -c restore/database.sql.gz | mysql -u DBUSER -p DBNAME
   ```
   (The dump sets `FOREIGN_KEY_CHECKS=0/1` around the data, so order is safe.)
5. **Restore uploaded files** (weekly/monthly zips):
   ```bash
   cp -a restore/files/. storage/app/public/
   php artisan storage:link        # if the public symlink is missing
   ```
6. **Restore `.env`** only if config/secrets were lost: `cp restore/env .env` — then confirm `APP_KEY`, `DB_*`, `APP_URL`.
7. **Rebuild caches:** `php artisan optimize:clear` (then `optimize` in production).
8. **Bring it up:** `php artisan up`. Smoke-test: homepage, an article, admin login, a file download.

---

## Disaster Recovery Checklist (server lost / data corrupted)

- [ ] Declare the incident; note time and suspected cause.
- [ ] Provision a host (same PHP 8.2+ / MySQL 5.7, cPanel).
- [ ] Pull the latest **off-site** backup (the on-host copy may be gone).
- [ ] `sha256sum -c` the zip — integrity confirmed.
- [ ] Create the database + DB user; deploy the codebase (git) + `composer install --no-dev`.
- [ ] Restore DB → files → `.env` (SOP above).
- [ ] `php artisan migrate` (in case the backup predates a migration), `storage:link`, `optimize`.
- [ ] Point DNS / domain; verify HTTPS + `APP_URL`.
- [ ] Re-add the cron entry; confirm `schedule:run` works.
- [ ] Smoke test (public + admin + email + file upload/download).
- [ ] Run a fresh `backup:run --type=weekly` and `backup:verify`.
- [ ] Post-mortem: RPO/RTO actuals, gaps, follow-ups.

## Backup Verification Checklist (do monthly — a backup is only real once restored)

- [ ] `php artisan backup:verify` passes (cron already runs it daily).
- [ ] Newest daily, weekly, and monthly archives exist with sane sizes.
- [ ] Off-site copies exist and match (count + checksum).
- [ ] **Test restore** the newest weekly into a scratch database; row counts look right.
- [ ] Files extract and open (pick a few PDFs/images).
- [ ] `.env` in the archive has the correct `APP_KEY` and DB name.
- [ ] Retention is correct (≤ 7 daily / 5 weekly / 12 monthly).

## Rollback Checklist (a bad deploy/migration, data still mostly good)

- [ ] `php artisan down`.
- [ ] **Code:** `git checkout <previous-tag>` (or redeploy the prior release); `composer install --no-dev`.
- [ ] **Database:** prefer `php artisan migrate:rollback` for a clean, reversible migration; otherwise restore the **pre-deploy daily DB** (SOP step 4). *(Phase C i18n migration is excluded from auto-discovery — see `docs/phase-c-execution.md`.)*
- [ ] Restore files only if the deploy mutated uploads.
- [ ] `php artisan optimize:clear`; clear OPcache if enabled.
- [ ] `php artisan up`; smoke test the regressed area.
- [ ] Record what broke and why the migration/deploy wasn't reversible.

## Production Checklist (before go-live)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] Off-site disk configured (`BACKUP_OFFSITE_DISK`) and a test `backup:run` lands there.
- [ ] Cron entry installed; `backup:run/clean/verify` appear in `php artisan schedule:list`.
- [ ] One **full manual restore rehearsal** completed on staging (timed → your RTO).
- [ ] `mysqldump` confirmed available on the host (else the PHP fallback is used — verify a dump succeeds).
- [ ] `.env` stored securely off-server too (password manager / vault) — it holds `APP_KEY`.
- [ ] Alerting: cron emails on `backup:verify` failure go to a monitored inbox.
- [ ] Retention values reviewed against disk quota.

## Notes

- **Restore equivalence:** `gunzip -c database.sql.gz | mysql …` works whether the dump came from `mysqldump` or the PHP fallback — both emit standard SQL.
- **Security:** the archive contains `.env` and a full DB dump — treat backups as secret. Keep the off-site bucket private; never expose `storage/app/backups`.
- **Not changed:** no new dependencies, no i18n/architecture change. Backups ride the existing scheduler.
