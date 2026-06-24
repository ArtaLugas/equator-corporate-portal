# Admin Visual Verification Checklist (manual — you must be logged in)

The admin panel is auth-gated, so these checks need a human login. Run them in
the browser after deploy (or against local Laragon). Each item names **where**,
**what to do**, and **pass criteria**. Tied to last session's UI changes:
static/responsive tables, select-all bulk-trash + confirm modal, return-to-list
redirect, and the homepage credentials band.

> If admin CSS/JS looks unstyled locally: check `public/hot`. Vite's dev server
> sometimes writes `[::1]` (IPv6), which can fail in the browser — either run
> `npm run build` (use built assets) or fix the host in the dev server, then hard-reload.

---

## A. Static, non-truncated tables + responsive column-hiding

Test each dense table: **News, Services, Company Documents, Teams, Company
Credentials** (plus any other index you changed).

For **each** module:
- [ ] Open the index page. Table fits the content area — **no horizontal scrollbar / no clipped right edge** at normal desktop width.
- [ ] The table is **static** (doesn't jump/reflow oddly on load; no layout shift).
- [ ] Narrow the browser to ~tablet (~768px) and ~mobile (~400px): **secondary columns hide progressively**; the row stays readable; primary column + actions remain visible.
- [ ] Action buttons (Edit / Trash) stay aligned and clickable at every width.
- [ ] Long multilingual values (ID titles are often longer than EN) wrap or truncate gracefully — they don't blow out the column.

Quick way to hit all widths: DevTools → device toolbar, or just drag the window.

---

## B. Select-all checkbox + "Move to Trash" confirm modal

Only on the **soft-delete modules** (the ones with a Trash/bulk action — not the
hard-delete ones). For each:
- [ ] A **header checkbox** selects/deselects **all rows** on the page.
- [ ] Selecting any row reveals the bulk **"Move to Trash"** control.
- [ ] Clicking it opens a **confirmation modal** (not an instant delete).
- [ ] **Cancel** closes the modal and changes nothing.
- [ ] **Confirm** moves the selected rows to Trash; a success flash appears; rows leave the active list.
- [ ] Open **Trash** for that module → the items are there (soft-deleted, recoverable), not gone.
- [ ] Confirm with **zero rows selected** the bulk action is disabled/no-op (no empty submit).

---

## C. Return-to-list redirect after edit (pagination + filters preserved)

Pick a module with enough rows to paginate (e.g. **News**):
- [ ] Go to **page 2** (and/or apply a filter/search).
- [ ] Edit any row → **Save**.
- [ ] After save you land back on **the same page 2 / same filter**, not page 1 — and a success flash shows.
- [ ] Repeat on one more module (e.g. **Services** or **Company Documents**) to confirm it's wired broadly.

---

## D. Homepage "Trusted Credentials" band (public — no login needed)

- [ ] Open `https://<domain>/` (or local home). Scroll to the **Trusted Credentials** band.
- [ ] Cards render as a **centered, wrapping row** (flex-wrap), each card ~`w-36`.
- [ ] With many credentials, cards **wrap to the next line and stay centered** — no overflow, no awkward left-justified last row gap that looks broken.
- [ ] Resize to mobile: cards wrap cleanly; nothing overlaps or clips.
- [ ] Check `/id` too — band renders the same with Indonesian labels.

---

## E. Cross-cutting sanity (quick)

- [ ] No JS console errors on any admin page (open DevTools console).
- [ ] No broken images / missing icons (assets resolve from `public/build`).
- [ ] Flash/error messages are the friendly multilingual ones (EN on `/`, ID on `/id`), not raw exceptions.
- [ ] After any destructive test, the affected module still loads cleanly on refresh.

---

### If something fails
Note the **module + width + exact step**, screenshot it, and report back — that's
enough to pin the Blade/partial responsible. Don't fix blind; the failing
(module, breakpoint) pair localizes it fast.
