# SEO & Analytics (GA4)

Technical SEO layer added before go-live: consent-gated **Google Analytics 4**, **Search Console verification**, richer **structured data**, and a **hreflang sitemap** — all IDs managed in the CMS, never hardcoded. Introduced after the Multilingual v1 freeze as an **Enhancement**.

## Audit baseline (before)

| Item | Before |
|---|---|
| GA4 / GTM / verification meta / event tracking | ❌ none |
| Sitemap | ✅ existed, but **EN-only**, no hreflang, missing legal pages |
| Robots | ✅ valid |
| Open Graph | ✅ complete (layout + per-page) |
| Structured data (JSON-LD) | ✅ home/about/faq/services/projects — **News articles missing** |
| CSP | blocked Google domains |

## What ships

| Concern | Where |
|---|---|
| GA4 (consent-gated) | `resources/views/public/partials/analytics.blade.php`, included in `layouts/public.blade.php` |
| GA4 / GSC IDs (CMS) | `settings.ga4_measurement_id`, `settings.gsc_verification` → admin **Settings → General → SEO & Analytics** |
| Search Console meta | `layouts/public.blade.php` (renders only when a token is set) |
| CSP allowlist | `app/Http/Middleware/SecurityHeaders.php` (googletagmanager.com + *.google-analytics.com) |
| News structured data | `resources/views/public/news/show.blade.php` (NewsArticle + BreadcrumbList) |
| Sitemap hreflang | `app/Http/Controllers/Public/SitemapController.php` |
| Cookie disclosure | `lang/{en,id}/legal.php` + `cookie_consent.php` (GA4 + `_ga` cookies) |

## How GA4 works (best practice)

- **Configured in the CMS** — paste the GA4 Measurement ID (`G-XXXXXXXXXX`) in Settings. Empty = GA4 fully disabled (nothing renders). **No ID is ever hardcoded.**
- **Consent-gated** — `gtag.js` loads **only** after the visitor accepts the *Analytics* cookie category (the consent banner from the privacy work). No requests fire before consent. When the visitor grants consent, the banner dispatches `cookie-consent-updated` and GA4 loads immediately (no reload). `anonymize_ip` is on.
- **Events tracked:**
  | Event | Trigger |
  |---|---|
  | `page_view` | automatic (`gtag config`) |
  | `contact_form_submit` | submit of `form[data-track-form="contact"]` |
  | `cta_click` | click on any element with `data-track="cta"` (+ optional `data-track-label`) |
  | `file_download` | click on links ending in pdf/doc/xls/ppt/zip/csv/dwg/dxf/kml/… (auto) |
  | `external_link_click` | click on links to another host (auto) |
- **Extending CTA tracking:** add `data-track="cta" data-track-label="…"` to any link/button. Downloads and external links need no tagging.

## Configuration (go-live checklist)

1. Create a GA4 property → copy its Measurement ID.
2. Admin → **Settings → General → SEO & Analytics** → paste **GA4 Measurement ID**.
3. In Search Console choose the *HTML tag* method → paste the token into **Google Search Console Verification** → verify.
4. Submit `https://<domain>/sitemap.xml` in Search Console.
5. Confirm GA4 only fires after accepting Analytics cookies (DebugView + the banner).

## Sitemap & structured data

- **Sitemap** (`/sitemap.xml`, cached 1h): every page emitted per locale with `xhtml:link` **hreflang** alternates (`en`, `id`, `x-default`); includes static pages, Privacy/Cookie policy, and all published services/projects/news.
- **Structured data**: per-page JSON-LD — Organization/WebSite (home), AboutPage, FAQPage, Service, CreativeWork (projects), and now **NewsArticle + BreadcrumbList** (news). All blocks validated as parseable JSON.

## SEO review (Lighthouse-equivalent, run on this build)

All green on `/`: valid `<title>`, meta description, canonical, hreflang en/id/x-default, Open Graph + Twitter card, viewport, `<html lang>`, indexable (no stray `noindex`), GA4 **not** loaded without consent. News JSON-LD valid (NewsArticle + BreadcrumbList). Sitemap well-formed with hreflang (216 URLs on seeded data); robots.txt valid. Run a live Lighthouse pass on the deployed URL for field Core-Web-Vitals.

## Privacy alignment

GA4 is third-party analytics with cookies, so it is **consent-gated** and disclosed: the Cookie Policy now lists `_ga`/`_ga_*` (Analytics, up to 2 years, set only after consent) and the prior "no third-party analytics" wording was removed. See `docs/privacy-cookie-consent.md`.

## Testing & what's NOT changed

- `tests/Feature/SeoAnalyticsTest.php` — GA4 absent until configured, consent-gated + CMS-driven ID, GSC meta from CMS, sitemap hreflang + legal + detail pages, robots, NewsArticle JSON-LD.
- Not changed: exception handling, i18n decisions, the first-party `TrackVisitor` (unchanged), public layout structure (additive head includes only). Multilingual v1 freeze preserved.
