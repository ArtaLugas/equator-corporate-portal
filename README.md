# Equator Group — Corporate Portal

Corporate website and content-management portal for **Equator Group**, a social &
environmental advisory firm working across sustainability, ESG, resilience,
engineering, and development sectors.

Built with **Laravel 11**, **Blade**, **Tailwind CSS**, **Alpine.js**, and **Vite**.

---

## Requirements

| Tool | Version |
|------|---------|
| PHP | `^8.2` |
| Composer | `2.x` |
| Node.js | `18+` (LTS recommended) |
| MySQL | `8.0+` (or MariaDB 10.4+) |

PHP extensions: the standard Laravel set — `pdo_mysql`, `mbstring`, `openssl`,
`tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`.

---

## Local setup

```bash
# 1. Clone
git clone <your-repo-url> equator-corporate-portal
cd equator-corporate-portal

# 2. Install PHP & JS dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure the database in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD),
#    create the database, then run migrations + seeders
php artisan migrate --seed

# 5. Link the public storage symlink (for uploaded images)
php artisan storage:link

# 6. Run the dev servers (two terminals)
php artisan serve      # http://localhost:8000
npm run dev            # Vite (HMR)
```

> **Note:** `npm run dev` and `npm run build` automatically regenerate the Lucide
> icon registry (`scripts/generate-icons.mjs`) via the `predev` / `prebuild` hooks.
> The generated file is not committed.

---

## Configuration notes

- **Cloudflare Turnstile** (contact-form CAPTCHA): set `TURNSTILE_SITE_KEY` and
  `TURNSTILE_SECRET_KEY` in `.env`. Keys are issued at
  [dash.cloudflare.com → Turnstile](https://dash.cloudflare.com/?to=/:account/turnstile).
- **Mail**: defaults to the `log` driver locally. Configure SMTP credentials in
  `.env` for real delivery.
- **Uploaded media** (logos, service/team images) lives in `storage/app/public`
  and is **not** committed. A fresh clone starts with no media — upload via the
  admin panel or restore from a backup.

---

## Production build

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

---

## Project structure

```
app/
├── Http/Controllers/
│   ├── Admin/        # CMS / admin panel controllers
│   └── Public/       # Public-facing site controllers
├── Models/           # Eloquent models
resources/
├── views/
│   ├── admin/        # Admin panel Blade views
│   ├── public/       # Public site Blade views
│   └── components/   # Reusable Blade components
config/
└── social_platforms.php   # Social brand-color metadata (single source of truth)
```

---

## License

Proprietary — © Equator Group. All rights reserved.
