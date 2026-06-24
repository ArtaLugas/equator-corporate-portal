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

];
