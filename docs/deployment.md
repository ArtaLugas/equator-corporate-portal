# Deployment & Production Configuration

Shared cPanel hosting (Laravel 11, MySQL 5.7). This is the operational baseline the queued features (contact auto-reply, admin notifications, contact replies, backups) depend on.

## Required production `.env`

| Key | Value | Why |
|---|---|---|
| `APP_ENV` | `production` | enables prod behaviour |
| `APP_DEBUG` | `false` | **mandatory** — else stack traces leak and the custom 500 page is bypassed (see `docs/error-pages.md`) |
| `QUEUE_CONNECTION` | **`database`** | makes mail/jobs **asynchronous** so a contact submit returns instantly (see Queue below) |
| `SESSION_DRIVER` | `database` (or `file`) | lead-source first-touch attribution is stored in the session |
| `CACHE_STORE` | `database` (or `file`) | home/sitemap/settings caches |
| Mail (Brevo SMTP) | via **Admin → Settings → Email** | `BrevoMailConfigurator` reads SMTP from the CMS, not `.env` |
| `services.turnstile.*` | site + secret key | contact/login CAPTCHA |
| GA4 / GSC | via **Admin → Settings → General → SEO & Analytics** | not hardcoded |

`.env.example` already ships `QUEUE_CONNECTION=database` — do **not** copy the local dev value (`sync`) to production.

## Queue (critical) — the single cron drives everything

One cron entry powers the scheduler, the queue worker, backups, and analytics pruning:

```
* * * * * cd /home/USER/app && php artisan schedule:run >> /dev/null 2>&1
```

`routes/console.php` schedules `queue:work --stop-when-empty --tries=3 --max-time=50` **every minute**, so with `QUEUE_CONNECTION=database` queued jobs (auto-reply email, new-message notification, reply email, backups) are drained within ~1 minute — no Supervisor/daemon needed (shared-hosting friendly).

### Fallback if the queue is not running

- **Cron present + `QUEUE_CONNECTION=database` (recommended):** fully asynchronous. Verify with `php artisan schedule:list` (the `queue:work` line appears) and watch the `jobs` / `failed_jobs` tables.
- **Cron missing:** queued jobs pile up in the `jobs` table and **emails never send** (silent). The single cron above is therefore **mandatory** — it also drives backups and pruning.
- **No cron available at all (last resort):** set `QUEUE_CONNECTION=sync`. Mail then sends **inline during the request** — the contact submit blocks until SMTP responds (slower; a slow/down SMTP can stall the submit). Acceptable only for very low volume with no cron.

Monitoring: a growing `jobs` table = the worker isn't running; rows in `failed_jobs` = inspect with `php artisan queue:failed`.

## Post-deploy checklist

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link` (if the public symlink is missing)
- [ ] `php artisan optimize` (config/route/view cache)
- [ ] Single cron entry installed; `php artisan schedule:list` shows backups + `queue:work`
- [ ] Send a test contact submission → confirm the visitor auto-reply and the office notification arrive (queue working)
- [ ] `QUEUE_CONNECTION=database`, `APP_DEBUG=false` confirmed
- [ ] Off-site backup disk configured (see `docs/backup-recovery.md`)

## Related docs
`docs/backup-recovery.md` (backups & DR), `docs/error-pages.md`, `docs/privacy-cookie-consent.md`, `docs/seo-analytics.md`, `docs/architecture-freeze.md`.
