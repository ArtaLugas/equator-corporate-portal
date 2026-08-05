<?php

namespace App\Support;

/**
 * Single source of truth for the RBAC map: every admin module, the abilities it
 * exposes, and the display grouping used by the role-permission matrix UI.
 *
 * A permission name is always "{module}.{ability}" (e.g. "news.update"). Because
 * spatie registers each permission as a Laravel gate, controllers authorize with
 * the same string — $this->authorize('news.update') — and @can('news.view')
 * works in Blade with no extra wiring. The seeder builds roles from this map, so
 * adding a module here is the only edit needed to introduce its permissions.
 *
 * The `admin` guard is used throughout: both the `web` and `admin` auth guards
 * share the `admins` provider, and `web` is defined first, so spatie would
 * otherwise default the Admin model to the `web` guard. Pinning it here and on
 * the Admin model keeps role/permission guard_names aligned and avoids
 * Spatie\Permission\Exceptions\GuardDoesNotMatch at check time.
 */
class Rbac
{
    /** The auth guard every RBAC role and permission is scoped to. */
    public const GUARD = 'admin';

    /** Standard content-module abilities. */
    public const CRUD = ['view', 'create', 'update', 'delete'];

    /**
     * module slug => [label, group, abilities].
     *
     * Grouping mirrors the admin sidebar sections so the permission matrix reads
     * the way editors already navigate. Abilities stay intentionally coarse in
     * this first cut: soft-delete (trash/restore) is folded into `delete`, and
     * list+show are both `view`. Finer abilities can be split out later without
     * breaking existing permission names.
     */
    public static function modules(): array
    {
        return [
            // Note: the dashboard is intentionally absent — it is the universal
            // landing page and is never permission-gated (see DashboardController).

            // ---- Konten utama ----
            'news' => ['label' => 'News', 'group' => 'Konten', 'abilities' => self::CRUD],
            'news-category' => ['label' => 'News Category', 'group' => 'Konten', 'abilities' => self::CRUD],
            'service' => ['label' => 'Services', 'group' => 'Konten', 'abilities' => self::CRUD],
            'service-category' => ['label' => 'Service Category', 'group' => 'Konten', 'abilities' => self::CRUD],
            'project' => ['label' => 'Projects', 'group' => 'Konten', 'abilities' => self::CRUD],
            'faq' => ['label' => 'FAQ', 'group' => 'Konten', 'abilities' => self::CRUD],

            // ---- Profil perusahaan ----
            'hero-banner' => ['label' => 'Hero Banner', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'about-section' => ['label' => 'About Section', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'about-content' => ['label' => 'About Content', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'about-history' => ['label' => 'About History', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'core-value' => ['label' => 'Core Values', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'key-metric' => ['label' => 'Key Metrics', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'team' => ['label' => 'Teams', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'partner' => ['label' => 'Partners', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'company-credential' => ['label' => 'Company Credentials', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'company-document' => ['label' => 'Company Documents', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'office-location' => ['label' => 'Office Locations', 'group' => 'Perusahaan', 'abilities' => self::CRUD],
            'social-link' => ['label' => 'Social Links', 'group' => 'Perusahaan', 'abilities' => self::CRUD],

            // ---- Interaksi ----
            'message' => ['label' => 'Messages', 'group' => 'Interaksi', 'abilities' => ['view', 'reply', 'archive', 'spam', 'delete']],

            // ---- Sistem ----
            'translation-progress' => ['label' => 'Translation Progress', 'group' => 'Sistem', 'abilities' => ['view']],
            'setting' => ['label' => 'Settings', 'group' => 'Sistem', 'abilities' => ['view', 'update']],
            'administrator' => ['label' => 'Administrators', 'group' => 'Sistem', 'abilities' => self::CRUD],
            'role' => ['label' => 'Roles & Permissions', 'group' => 'Sistem', 'abilities' => self::CRUD],
            'activity-log' => ['label' => 'Activity Log', 'group' => 'Sistem', 'abilities' => ['view']],
        ];
    }

    /** Flat list of every permission name ("{module}.{ability}"). */
    public static function permissions(): array
    {
        $out = [];

        foreach (self::modules() as $slug => $meta) {
            foreach ($meta['abilities'] as $ability) {
                $out[] = "{$slug}.{$ability}";
            }
        }

        return $out;
    }

    /**
     * Permissions granted to the base `admin` role — everything a plain admin can
     * already do today, so the existing suite stays green once enforcement is on.
     *
     * Only three surfaces are super-admin-only in the current code and are thus
     * withheld: administrator management (AdminPolicy::before denies non-supers),
     * the activity log (the `view-activity-logs` gate), and role management (new
     * in this feature). Settings are intentionally INCLUDED: SettingController
     * has no authorization today, so every admin can edit them — withholding it
     * would be a behaviour change. The super-only *permanent* delete of company
     * credentials stays enforced by the untouched CompanyCredentialPolicy, not by
     * this list.
     */
    public static function contentPermissions(): array
    {
        $systemOnly = ['administrator', 'role', 'activity-log'];

        return array_values(array_filter(
            self::permissions(),
            fn ($perm) => ! in_array(explode('.', $perm)[0], $systemOnly, true)
        ));
    }
}
