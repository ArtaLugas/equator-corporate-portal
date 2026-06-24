<?php

/*
|--------------------------------------------------------------------------
| Cookie Consent
|--------------------------------------------------------------------------
|
| Single source of truth for the consent banner. Category LABELS/DESCRIPTIONS
| live in lang/{locale}/cookie_consent.php (keyed by the category id below), so
| this file stays logic-only and bilingual.
|
| To add a category: add an entry here + its lang strings. The banner, the
| stored consent payload, and the cookie_consent() helper pick it up
| automatically — nothing else to touch.
|
| 'required' => true  → always granted, toggle locked on (Necessary).
|
| Bump 'version' whenever the policy materially changes; a stored consent with
| an older version is treated as absent, so the banner re-prompts.
|
*/

return [

    'cookie_name' => 'equator_cookie_consent',

    'version' => 1,

    'lifetime_days' => 180,

    'categories' => [
        'necessary' => ['required' => true],
        'analytics' => ['required' => false],
        'marketing' => ['required' => false],
        'preferences' => ['required' => false],
    ],

];
