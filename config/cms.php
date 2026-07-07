<?php

/*
|--------------------------------------------------------------------------
| CMS Behaviour
|--------------------------------------------------------------------------
|
| Tunables for content-management behaviour that may change between the
| pre-launch and live phases of the site.
|
*/

return [

    /*
    | Auto-regenerate a record's slug from its default-locale name on UPDATE.
    |
    | true  (pre-launch): the slug always tracks name_en, keeping titles and
    |        permalinks consistent while content is still being shaped.
    | false (after go-live): slugs become permanent once URLs carry SEO value
    |        and backlinks — editing a title no longer changes the permalink.
    |
    | Slugs are always generated on CREATE regardless of this flag.
    */
    'auto_regenerate_slug' => env('CMS_AUTO_REGENERATE_SLUG', true),

    /*
    | Admin audit-log (activity_logs) retention, in days. The scheduled
    | `model:prune` command deletes entries older than this so the table stays
    | bounded. Audit logs are append-only and are never cleared by hand — a
    | manual "wipe" would defeat the trail — so pruning is strictly time-based.
    |
    | Default 365 (one year). Set to 0 (or below) to retain indefinitely
    | (pruning disabled).
    */
    'activity_log_retention_days' => (int) env('ACTIVITY_LOG_RETENTION_DAYS', 365),

];
