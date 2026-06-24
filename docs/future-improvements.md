# Future Improvements

Deferred, non-blocking items captured from audits so they are not lost. None change current application behaviour. Implement on a future milestone — not now. (Architectural changes still require approval per `docs/architecture-freeze.md`.)

## From the Contextual CTA / Auto-Reply / Lead-CRM audit (2026-06-22)

Deferred **Minor** (no behavioural impact today):

- **m2 — Sequential reference number.** `EQ-YYYYMMDD-000034` exposes the message id to the visitor (in the auto-reply), leaking total lead volume. Consider a non-sequential token if disclosure ever matters. *File: `app/Models/Message.php` (`booted()`).*
- **m5 — DRY in `LeadSource`.** `capture()` and `metadata()` repeat the UTM list and the landing/referrer fallback; could share a single `currentTouch(Request)` helper. *File: `app/Services/LeadSource.php`.*
- **m6 — Two writes per message.** The `created` hook does `saveQuietly()` after INSERT to set the reference (INSERT + UPDATE). Acceptable at contact-form volume; a `creating`-time scheme would avoid the extra write. *File: `app/Models/Message.php` (`booted()`).*

Deferred **Future Improvement**:

- **F1 — `LeadAnalytics::foldBy` scalability.** `topLandingPages` / `topReferrers` group a high-cardinality column (full URLs) then fold in PHP **without a LIMIT** — bounded by message count, fine now. If lead volume grows large, store a normalised (indexed) path/host column or pre-aggregate. *File: `app/Services/LeadAnalytics.php`.*
- **F2 — Cache the analytics dashboard.** `summary()` runs ~6 queries per page load; wrap in `Cache::remember` (same pattern as home/sitemap) busted on new messages. *File: `app/Services/LeadAnalytics.php` / `app/Http/Controllers/Admin/MessageController.php`.*
- **F3 — Auto-reply email polish.** Add a `replyTo` (office email) and a plain-text alternative part to improve deliverability / spam score. *File: `app/Mail/ContactAutoReplyMail.php`.*
- **F4 — Analytics chart on small screens.** The 12-bar "Leads per Month" chart is cramped on narrow admin viewports; consider horizontal scroll or a compact table. *File: `resources/views/admin/messages/analytics.blade.php`.*
- **F5 — CTA prefill crawl budget (SEO).** `?service=` CTA links could carry `rel="nofollow"`; low priority since the canonical already de-dups to `/contact`. *File: CTA usages in `resources/views/public/{services,projects}/show.blade.php`.*
