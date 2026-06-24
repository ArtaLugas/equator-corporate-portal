# Error Pages

Branded, resilient HTTP error pages for the public site — **404, 403, 419, 429, 500, 503** — introduced after the Multilingual v1 freeze as an **Enhancement** (no architectural change; see `docs/architecture-freeze.md`).

## What ships

| File | Role |
|---|---|
| `resources/views/layouts/error.blade.php` | The single error layout. Self-contained: **no DB, no `@vite`, no JS** — all CSS is inline. Carries the Equator identity (brand bar, palette `#263592`/`#006CCD`/`#FFB74D`, Poppins stack) and the two CTAs. |
| `resources/views/errors/{404,403,419,429,500,503}.blade.php` | One thin view per status; each `@extends('layouts.error')` and fills `code` / `eyebrow` / `error_title` / `error_message`. |
| `lang/en/errors.php`, `lang/id/errors.php` | All copy (titles, messages, CTA labels). Follows the project's static-strings-in-`lang/` convention. |
| `tests/Feature/ErrorPagesTest.php` | Renders every view (brand + SEO + a11y), asserts **zero DB queries**, and checks the live 404. |

## Behaviour map

| Code | When it shows |
|---|---|
| 404 | Unmatched URL / `abort(404)` / missing model. |
| 403 | `abort(403)` / failed authorization. |
| 419 | Expired session / CSRF token mismatch (`TokenMismatchException`). |
| 429 | Rate limit exceeded (`throttle` middleware). |
| 500 | Any unhandled exception **when `APP_DEBUG=false`**. |
| 503 | Maintenance mode (`php artisan down`). |

## Why the error layout is self-contained

An error page must render **even when the cause is the database, the cache, or the asset pipeline**. The public layout (`layouts/public.blade.php`) queries the DB in its footer/navbar (`ServiceCategory`, `app_setting()`, `primary_office()`), so reusing it would make a DB-outage 500 fail to render and fall back to a bare framework page. The error layout therefore:

- makes **no database calls**,
- uses **inline CSS** instead of `@vite` (survives a broken/missing asset build),
- uses `url('/')` / `url('/contact')` instead of `route()` (no route resolution),
- uses **no JavaScript**.

Verified by `ErrorPagesTest::test_error_views_perform_no_database_queries`.

## Production requirements

- **`APP_DEBUG=false` in production.** With `APP_DEBUG=true`, Laravel shows the Ignition stack-trace page for 500s (leaking internals) and bypasses `errors/500`. The 404/403/419/429/503 pages render regardless of debug (they are `HttpException`s); only the 500 page depends on debug being off.
- **CSP compatible.** `SecurityHeaders` enforces CSP in production; `style-src 'self' 'unsafe-inline'` permits the inline `<style>` block, and the pages load no external/`@vite` assets, so `default-src 'self'` is satisfied.
- **Maintenance (503).** `php artisan down --render="errors::503"` renders the branded 503 during downtime. (Plain `php artisan down` uses Laravel's pre-rendered maintenance template; pass `--render` to use this page.)

## SEO & Accessibility

- **SEO:** `<meta name="robots" content="noindex, follow">`, correct HTTP status per page, per-code `<title>`, `<html lang>` set from the active locale's ISO.
- **A11y:** `role="main"` landmark, a single `<h1>` carrying the human message (the large numeric code is `aria-hidden`), the status is conveyed in the text eyebrow, on-brand `:focus-visible` outlines, decorative dot `aria-hidden`, and `prefers-reduced-motion` disables transitions.

## Previewing locally

- Visit any non-existent URL on the running app (e.g. `http://localhost:8000/anything`) to see the **404** live — it renders even with `APP_DEBUG=true`.
- To preview the **500** locally, set `APP_DEBUG=false` temporarily and trigger an error (or render `view('errors.500')` in tinker).
- All six pages share one layout, so the 404 is representative of the whole set; only the copy differs.

## Deliberately NOT changed

- **No exception-handler change** — Laravel auto-discovers `resources/views/errors/{code}.blade.php`; `bootstrap/app.php` stays untouched.
- **No `Route::fallback()`** — the 404 path already covers unmatched URLs.
- **No i18n / public-layout / shared-partial changes** — the freeze on Multilingual v1 is preserved.
