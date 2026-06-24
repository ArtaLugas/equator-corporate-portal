<?php

/*
|--------------------------------------------------------------------------
| Supported Locales
|--------------------------------------------------------------------------
|
| Single source of truth for the site's languages. Every part of the i18n
| layer (routing, middleware, models, helpers, language switcher, hreflang)
| reads from here — nothing hardcodes language codes. Adding a third
| language is: add a key below, scaffold its DB columns, translate.
|
| 'default' is served at the root (e.g. /services) and is the SEO canonical
| + the fallback used when a translation is missing. Non-default locales are
| URL-prefixed (e.g. /id/services).
|
*/

return [

    'default' => 'en',

    'supported' => [

        'en' => [
            'name' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
            'iso' => 'en-US', // used in <html lang> + hreflang
        ],

        'id' => [
            'name' => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'dir' => 'ltr',
            'iso' => 'id-ID',
        ],

    ],

];
