# Deployment — Shared Hosting (cPanel)

Target environment: **cPanel shared hosting** (Apache/LiteSpeed + `.htaccess`, no
root, no daemons/Supervisor, typically no Redis). Adjust names to your account.

> Why no Nginx/systemd files here? On shared hosting you don't control a vhost or
> run background services — so HSTS is emitted by the app
> (`App\Http\Middleware\SecurityHeaders`, HTTPS-only) and the queue runs via cron
> (below) instead of a worker daemon.

## 1. PHP & extensions (MultiPHP)

In cPanel → **MultiPHP Manager** set the domain to **PHP 8.2**. In **MultiPHP INI
Editor / “Select PHP Extensions”** enable: `pdo_mysql`, `mbstring`, `openssl`,
`tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (GD is required for
image optimization). Raise `upload_max_filesize` / `post_max_size` to ≥ 20M.

## 2. Build assets locally, then upload

Shared hosting usually has no Node.js, so **build on your machine or in CI** and
upload the result — never run `npm` on the server:

```bash
npm ci && npm run build      # produces public/build (committed by CI artifact or uploaded)
composer install --no-dev --optimize-autoloader
```

Upload the project, including `vendor/` and `public/build/`, via cPanel Git
Version Control, FTP, or the File Manager.

## 3. Document root

Laravel must serve from its `public/` folder, not the project root. Pick one:

- **Preferred:** point the domain’s Document Root to `…/your-app/public`
  (cPanel → **Domains** → manage → Document Root), or
- Put the app outside `public_html`, move the contents of `public/` into
  `public_html`, and edit `public_html/index.php` so the two `require` paths point
  to the app folder (`__DIR__.'/../your-app/vendor/autoload.php'` etc.).

Never let the project root (with `.env`) be web-accessible.

## 4. Environment & one-time setup

Set production values in `.env` (see the checklist at the top of `.env.example`):
`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`,
`SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`.

Via cPanel **Terminal** (or a one-off cron) run:

```bash
php artisan key:generate          # only if APP_KEY is empty
php artisan migrate --force
php artisan storage:link          # if symlinks are blocked, see note below
php artisan config:cache route:cache view:cache
```

> **storage:link on shared hosting:** if symlink creation is disabled, create the
> link from cPanel Terminal manually (`ln -s …/storage/app/public …/public/storage`)
> or ask the host to enable it. Uploaded media won’t show until this link exists.

## 5. Cron (scheduler + queue) — required

Add **one** cron job in cPanel → **Cron Jobs** (every minute). It drives both the
analytics pruning and the queue (transactional email/notifications), so no daemon
is needed:

```cron
* * * * * /usr/local/bin/php /home/USER/your-app/artisan schedule:run >> /dev/null 2>&1
```

(Confirm the PHP binary path in cPanel; it’s often `/usr/local/bin/ea-php82`.)

If you prefer the very simplest setup and email volume is low, you may instead set
`QUEUE_CONNECTION=sync` in `.env` to send mail inline and skip the queue entirely —
at the cost of a slightly slower contact-form response.

## Security — dependency advisories (Laravel 11)

`composer audit` is clean except for advisories in `laravel/framework` whose
fixes only ship in Laravel 12. We intentionally stay on Laravel 11, so these are
handled at the application layer rather than by upgrading:

- **Temporary Signed URL Path Confusion** — *not applicable*: the app uses no signed URLs.
- **CRLF injection in the default `email` rule** (incl. the HIGH one) — *mitigated*:
  the public contact form validates with `email:filter` + `not_regex:/[\r\n]/`
  (rejects CR/LF), and Symfony Mailer rejects CR/LF in addresses as a second layer.
  Admin email fields are trusted input behind auth and are likewise mailer-protected.

`guzzlehttp/*` and `symfony/*` advisories were resolved by updating to patched
releases within the Laravel 11 constraints.

**Framework policy:** this project stays on **Laravel 11**; a major upgrade to
Laravel 12 is intentionally **out of scope**. Any future advisory is handled with
an L11-compatible patch, an application-layer mitigation (as above), or a scoped
dependency update — never a major framework upgrade.

A baseline **Content-Security-Policy** is emitted by `App\Http\Middleware\SecurityHeaders`
(allowlists Cloudflare Turnstile + the admin-login Alpine CDN; everything else is
self-hosted). Extend the `POLICY` constant if you add a new third-party origin.

## Monitoring / observability

No paid APM is required for this deployment. Error visibility relies on:

- **Rotating logs** — `LOG_CHANNEL=stack`, `LOG_STACK=daily`, `LOG_DAILY_DAYS=14`
  (`storage/logs`). Set `LOG_LEVEL=warning` in production.
- **Critical-error alerts (optional, zero-cost)** — set `LOG_SLACK_WEBHOOK_URL`
  and use `LOG_STACK=daily,slack`; Laravel/Monolog then pushes `critical`+ errors
  to Slack. No extra package needed.

If a hosted error tracker (Sentry/Flare) is adopted later, wire it in
`bootstrap/app.php` `->withExceptions()`. Because the project stays on Laravel 11
(see framework policy above), prefer an L11-compatible tracker version; if Composer
blocks an install against the open framework advisory, use a scoped, documented
advisory-ignore rather than upgrading the framework.

## 6. Redeploys

Re-upload changed files, then via Terminal:

```bash
php artisan migrate --force
php artisan config:cache route:cache view:cache
```

Config/route/view caches are rebuilt; the cron-driven `queue:work --stop-when-empty`
picks up new code on its next run automatically.
