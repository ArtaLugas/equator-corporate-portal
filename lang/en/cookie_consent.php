<?php

return [

    'title' => 'We value your privacy',
    'body' => 'We use cookies to keep the site secure, remember your choices, and understand how it is used. You can accept all, reject optional cookies, or choose what to allow.',
    'policy_link' => 'Read our Cookie Policy',

    'accept_all' => 'Accept all',
    'reject_optional' => 'Reject optional',
    'customize' => 'Customize',
    'save' => 'Save preferences',
    'back' => 'Back',

    'preferences' => 'Cookie Preferences',
    'always_on' => 'Always on',

    // Keyed by the category ids in config/cookie_consent.php.
    'categories' => [
        'necessary' => [
            'label' => 'Necessary',
            'description' => 'Required for the site to work — sessions, security, anti-spam, and remembering your choices.',
        ],
        'analytics' => [
            'label' => 'Analytics',
            'description' => 'Lets us use Google Analytics (GA4) to understand aggregate site usage. Loads only if you allow it.',
        ],
        'marketing' => [
            'label' => 'Marketing',
            'description' => 'Not used at this time. Reserved for any future advertising or campaign measurement.',
        ],
        'preferences' => [
            'label' => 'Preferences',
            'description' => 'Remembers choices that personalize your experience.',
        ],
    ],

];
