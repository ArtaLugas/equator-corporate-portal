# Go-Live Operator Checklist (D1–D5)

Operator sign-off list for production deployment on **shared cPanel hosting**
(Apache/LiteSpeed, single server, no daemons). These are **deployment-environment
responsibilities** — the application code is already in place; nothing here
requires a code change. For the full deploy walkthrough see
[`deploy/README.md`](../deploy/README.md); for production config rationale see
[`deployment.md`](deployment.md); for backups see [`backup-recovery.md`](backup-recovery.md).

> Scope note: every item below is operator/host configuration. The repository
> ships sane defaults (`config/session.php`, `bootstrap/app.php`,
> `routes/console.php`, `config/backup.php`) — go-live is a matter of setting the
> environment, **not** patching code.

---

## D1 — Trusted Proxy

**Decision required from the operator. No code change by default.**

The app reads the client IP (`$request->ip()` in `TrackVisitor`) and the HTTPS
flag (`$request->secure()` drives the HSTS header and `SESSION_SECURE_COOKIE`).
Both are correct **only if Laravel sees the real connecting address/scheme**.

- **Direct cPanel hosting (no CDN in front) — default, recommended.** PHP receives
  the true `REMOTE_ADDR` and HTTPS from the web server. **No trusted-proxy
  configuration is needed; leave it unset.** This is the assumed topology.
- **A reverse proxy / CDN in front (e.g. Cloudflare orange-cloud, a load
  balancer).** The connecting address becomes the proxy's, and the original
  client IP/scheme arrive in `X-Forwarded-For` / `X-Forwarded-Proto`. Without
  trusting that proxy, visitor analytics log the proxy IP and HTTPS detection can
  be wrong. **Only then** configure Laravel 11 trusted proxies in
  `bootstrap/app.php` (`->withMiddleware(fn ($m) => $m->trustProxies(at: [...], headers: ...))`)
  with the proxy's IP range — and treat that as a scoped, approved change.

- [ ] Confirm the production topology (direct host **or** fronting proxy).
- [ ] If a fronting proxy is used, configure trusted proxies for its IP range; otherwise leave unset.

## D2 — HTTPS

**Operator/host configuration. No code change.**

HTTPS enforcement is environment-level; the app already emits HSTS over HTTPS
(`SecurityHeaders`, HTTPS-only) and reads cookie/scheme settings from `.env`.

- [ ] TLS certificate installed (cPanel AutoSSL / Let's Encrypt) and valid.
- [ ] Host redirects `http://` → `https://` (cPanel "Force HTTPS Redirect" or `.htaccess`).
- [ ] `APP_URL=https://your-domain` (canonical URLs, sitemap, mail links).
- [ ] `SESSION_SECURE_COOKIE=true` (cookie never sent over HTTP; default in `.env.example` is `false` for local dev).
- [ ] After first HTTPS hit, confirm the `Strict-Transport-Security` header is present.

## D3 — Scheduler

**Operator/host configuration. No code change.**

The schedule is fully defined in `routes/console.php` (queue worker, analytics
prune, backups daily/weekly/monthly, clean, verify). It needs the **single**
cPanel cron entry — nothing else.

```cron
* * * * * /usr/local/bin/ea-php82 /home/USER/your-app/artisan schedule:run >> /dev/null 2>&1
```

- [ ] One cron entry installed (every minute), PHP binary path confirmed in cPanel.
- [ ] `php artisan schedule:list` shows `queue:work`, `model:prune`, and the three `backup:run` lines.
- [ ] `jobs` table drains (worker running); watch `failed_jobs` for errors.

## D4 — Off-site Backup

**Operator/host configuration. Status today: NOT active by default.**

`config/backup.php` → `offsite_disk` reads `BACKUP_OFFSITE_DISK`, which is **unset
by default** — backups are written **locally only** (`storage/app/backups`), which
is **not** true disaster recovery (a lost host loses the backups too). Local
tiered backups + integrity verification already run via the scheduler.

- [ ] Decide whether off-site DR is required for go-live (recommended for production).
- [ ] If yes: set `BACKUP_OFFSITE_DISK` (e.g. `s3` / a defined `ftp`/`sftp` disk) + credentials + `BACKUP_OFFSITE_PATH`, then run a test `backup:run` and confirm the zip lands off-site.
- [ ] Until off-site is configured, **backup encryption (R2) stays Deferred** — see the audit notes; there is no off-site target to encrypt to yet.

## D5 — Production `.env` Audit

**Operator/host configuration. Never edit production `.env` from the repo.**

Confirm each key on the live server before go-live (full rationale in
[`deployment.md`](deployment.md) and the header of `.env.example`):

| Key | Required production value | Why |
|---|---|---|
| `APP_ENV` | `production` | enables production behaviour |
| `APP_DEBUG` | `false` | **mandatory** — else stack traces leak and the custom 500 page is bypassed |
| `LOG_LEVEL` | `warning` | avoid verbose/sensitive logs (`stack`→`daily`, 14-day rotation) |
| `CACHE_STORE` | `file` (or `database`) | home/sitemap/settings caches; no `cache` table by default → `file` |
| `SESSION_SECURE_COOKIE` | `true` | cookie only over HTTPS (see D2) |
| `MAIL` | Brevo SMTP via **Admin → Settings → Email** | `BrevoMailConfigurator` reads SMTP from the CMS, not `.env` |
| `QUEUE_CONNECTION` | `database` | asynchronous mail/jobs; do **not** ship the dev `sync` value |
| `CACHE` (`CACHE_PREFIX`) | per-site value if sharing a store | namespaces cache keys on shared hosting |

- [ ] All eight rows confirmed on the live server.
- [ ] `APP_KEY` set (`php artisan key:generate` if empty) and stored off-server (vault/password manager).
- [ ] `php artisan config:cache route:cache view:cache` run after finalizing `.env`.

---

## Final operator sign-off

- [ ] D1 topology decided (trusted proxy set only if a fronting proxy exists).
- [ ] D2 HTTPS enforced end-to-end; HSTS observed.
- [ ] D3 single cron installed; `schedule:list` correct; queue draining.
- [ ] D4 off-site DR decision made (configured, or accepted as deferred).
- [ ] D5 all eight `.env` keys confirmed; caches rebuilt.
- [ ] Post-deploy smoke test (public pages, admin login, a file download, a test contact submit → auto-reply + office notification arrive).
