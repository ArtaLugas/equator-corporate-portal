<?php

use App\Models\ActivityLog;
use App\Models\OfficeLocation;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

if (! function_exists('activity_log')) {

    function activity_log(
        string $module,
        string $description
    ): void {

        try {

            ActivityLog::create([
                'admin_id' => auth('admin')->user()?->id,
                'module' => $module,
                'description' => $description,
                'ip_address' => request()?->ip(),
            ]);

        } catch (Throwable $e) {

            report($e);

        }
    }
}

if (! function_exists('app_setting')) {

    /**
     * Read a value from the single-row settings table (cached).
     */
    function app_setting(string $key, $default = null)
    {
        $value = Setting::current()->{$key} ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }
}

if (! function_exists('primary_office')) {

    /**
     * The canonical contact location — single source of truth for public contact
     * info (address, phone, email, map). Returns the primary OfficeLocation, or the
     * first active one if none is flagged primary. Resolved once per request.
     */
    function primary_office(): ?OfficeLocation
    {
        static $resolved = false;
        static $office = null;

        if ($resolved) {
            return $office;
        }

        $resolved = true;

        if (! Schema::hasTable('office_locations')) {
            return $office;
        }

        // ordered() sorts is_primary DESC first, so first() is the primary office.
        return $office = OfficeLocation::active()->ordered()->first();
    }
}

if (! function_exists('locale_url')) {

    /**
     * Build the URL of the CURRENT page in another locale — used by the language
     * switcher and hreflang tags. Path-based (swaps the locale prefix) so it works
     * regardless of whether the current route is named: the default-locale public
     * routes are intentionally UNNAMED (only the localized routes carry names, to
     * keep route:cache valid), so a route-name approach would break on every
     * English page. The default locale resolves to an unprefixed URL; other
     * locales get their prefix. Query string is intentionally dropped.
     */
    function locale_url(string $locale): string
    {
        $default = config('locales.default');
        $supported = array_keys(config('locales.supported', []));

        // Current path without the leading slash; '' for the homepage.
        $trimmed = trim(request()->path(), '/');
        $segments = $trimmed === '' ? [] : explode('/', $trimmed);

        // Drop an existing leading locale prefix (e.g. /id/services → services).
        if (isset($segments[0]) && in_array($segments[0], $supported, true)) {
            array_shift($segments);
        }

        $base = implode('/', $segments);

        // Default locale stays unprefixed; others are prefixed.
        $path = $locale === $default
            ? '/'.$base
            : '/'.$locale.($base === '' ? '' : '/'.$base);

        return url(rtrim($path, '/') ?: '/');
    }
}

if (! function_exists('cookie_consent')) {

    /**
     * Read the visitor's stored cookie-consent choices (set client-side by the
     * consent banner). This is the server-side extension hook: pass a category
     * id to check whether it was granted.
     *
     *   cookie_consent()             → ['analytics' => true, ...] (or [] if none)
     *   cookie_consent('analytics')  → bool
     *
     * 'necessary' is always granted. Any non-necessary category defaults to
     * false until the visitor explicitly grants it, so a future feature can gate
     * on it safely. NOTE: per the current compliance decision, first-party
     * visitor analytics is disclosed under legitimate interest and is NOT gated
     * by this helper — it exists so gating is a one-line change later.
     */
    function cookie_consent(?string $category = null)
    {
        $name = config('cookie_consent.cookie_name', 'equator_cookie_consent');
        $raw = request()?->cookie($name);
        $data = is_string($raw) ? json_decode($raw, true) : null;

        $granted = is_array($data['categories'] ?? null) ? $data['categories'] : [];

        if ($category === null) {
            return $granted;
        }

        if ($category === 'necessary') {
            return true;
        }

        return (bool) ($granted[$category] ?? false);
    }
}
