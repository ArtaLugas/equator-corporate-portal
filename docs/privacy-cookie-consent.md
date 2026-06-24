# Privacy, Cookie Policy & Consent

Compliance layer added before go-live: a **Privacy Policy**, a **Cookie Policy**, and a **cookie-consent banner**, aligned to Indonesia's **UU PDP** and, where relevant, **GDPR**. Introduced after the Multilingual v1 freeze as an **Enhancement** (no i18n/architecture change).

> ⚠️ **Legal review required.** The policy texts are a strong, standards-aligned **template** — not legal advice. Before go-live, have counsel confirm: the exact legal entity name, a data-request contact (or DPO), the supervisory authority, retention periods, and the list of processors. The author of this code is not a lawyer.

## Audit that prompted this

| Check | Before |
|---|---|
| Privacy Policy | none |
| Cookie Policy | none |
| Contact form data disclosure | only a generic "never shared" reassurance, no policy link (and inaccurate — processors exist) |
| Visitor tracking disclosure | **none** — `TrackVisitor` stores full IP + user-agent + URL + referer on every public GET (90-day retention) with no disclosure or consent |

## Decisions taken

- **Content delivery:** static Blade + `lang/{en,id}/legal.php` (version-controlled, bilingual). Not a CMS module.
- **Visitor tracking:** **disclose-only** — `TrackVisitor` is unchanged. The first-party analytics (incl. IP, 90-day retention) is disclosed in both policies under *legitimate interest*, with the user's right to object/delete. It is **not** gated on consent. (See "Gating later".)

## What ships

| File | Role |
|---|---|
| `config/cookie_consent.php` | Single source of truth: categories (Necessary/Analytics/Marketing/Preferences), version, cookie name, lifetime. **Add a category = one entry here + its lang strings.** |
| `lang/{en,id}/legal.php` | Privacy + Cookie policy content (structured sections + cookie table). |
| `lang/{en,id}/cookie_consent.php` | Banner copy + category labels/descriptions. |
| `app/Http/Controllers/Public/LegalController.php` + routes `privacy`, `cookies` | The two pages (localized: `/privacy`, `/id/privacy`, …). |
| `resources/views/legal/{privacy,cookies}.blade.php` (+ `_sections`) | Renders the policies on the public layout. |
| `resources/views/components/public/cookie-consent.blade.php` | The banner (Alpine). Included once in `layouts/public.blade.php`. |
| `app/helpers.php` → `cookie_consent()` | Server-side read of the visitor's choices (extension hook). |
| `bootstrap/app.php` | `equator_cookie_consent` excluded from cookie encryption (set client-side). |

Footer now links **Privacy Policy · Cookie Policy · Cookie Preferences**; the contact form shows a data-use notice linking to the Privacy Policy.

## How consent is stored

On a choice, the banner writes cookie **`equator_cookie_consent`** (and mirrors to `localStorage`):

```json
{ "version": 1, "categories": { "necessary": true, "analytics": false, "marketing": false, "preferences": false }, "ts": 1750000000000 }
```

- `path=/; max-age=180 days; SameSite=Lax` (+ `Secure` on HTTPS).
- **Versioning:** bump `config('cookie_consent.version')` after a material policy change — older stored consent is treated as absent, so the banner re-prompts everyone.
- The banner shows on first visit; **Accept all / Reject optional / Customize** (per-category toggles, Necessary locked). Re-open anytime via **Cookie Preferences** (footer or Cookie Policy page) — it dispatches the `open-cookie-preferences` window event.

## Adding a category (extensibility)

1. Add it to `config/cookie_consent.php` (`'required' => false`).
2. Add `categories.<id>.label` + `.description` to both `lang/*/cookie_consent.php`.
   The banner, the stored payload, and `cookie_consent()` pick it up automatically.

## Gating a feature on consent (when ready)

`cookie_consent('analytics')` returns the visitor's choice server-side (`necessary` is always `true`; optional categories default `false` until granted). To later gate first-party analytics, add one guard in `TrackVisitor::shouldTrack()`:

```php
if (! cookie_consent('analytics')) {
    return false;
}
```

That single line flips the current disclose-only posture to consent-gated — no other change needed.

## Compliance mapping (summary)

- **UU PDP / GDPR rights** — access, rectification, erasure, objection/restriction, withdraw consent, complaint — listed in the Privacy Policy with a contact route.
- **Lawful bases** — consent (contact form, optional cookies), legitimate interest (security + aggregate analytics), legal obligation.
- **Processors disclosed** — Cloudflare (Turnstile), Brevo (email), hosting; international-transfer note.
- **Retention** — contact messages (as needed), visitor analytics (90 days, matches `Visitor::RETENTION_DAYS`).

## Accessibility & SEO

- Banner: `role="region"` + `aria-label`, real `<input type=checkbox>` toggles with `aria-label`, `:focus-visible` rings, server-rendered markup (works with JS toggling visibility only).
- Policy pages: normal indexable public pages, `<title>` + meta description per page, semantic headings, bilingual via the existing i18n.

## Testing

`tests/Feature/PrivacyComplianceTest.php` — policy pages render in both locales, cookie table present, footer legal links + banner present, contact data-use disclosure, `cookie_consent()` behavior, config-driven categories. Full suite baseline unchanged (zero regression).

## Deliberately NOT changed

- `TrackVisitor` behavior (per the disclose-only decision).
- The i18n subsystem, public layout structure (only an additive banner include + footer legal row), and Multilingual v1 freeze.
