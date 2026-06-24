<?php

/*
|--------------------------------------------------------------------------
| Company Credentials — category registry
|--------------------------------------------------------------------------
|
| Single source of truth for credential categories. Stored on
| company_credentials.category as a plain string (NOT an enum) so a new
| category can be added here WITHOUT a database migration — meeting the
| "future-proof without changing DB structure" requirement.
|
| To add a category: add a key below (+ icon + order) and its label in
| lang/{en,id}/credentials.php under 'categories.<key>'. Validation
| (Rule::in) and the public grouping pick it up automatically.
|
| Keys are lowercase snake_case; labels are localized in lang/.
| `icon` is a Lucide icon name (rendered via <x-icon>).
|
*/

return [

    'categories' => [
        'iso' => ['icon' => 'award', 'order' => 1],
        'lpjp' => ['icon' => 'clipboard-check', 'order' => 2],
        'kbli' => ['icon' => 'briefcase', 'order' => 3],
        'business_license' => ['icon' => 'scroll-text', 'order' => 4],
        'membership' => ['icon' => 'users', 'order' => 5],
        'accreditation' => ['icon' => 'shield-check', 'order' => 6],
        'other' => ['icon' => 'file-badge', 'order' => 99],
    ],

    // A credential is flagged "Expiring Soon" when its expiry is within this
    // many days of today (status badge on admin + public).
    'expiring_soon_days' => 30,

];
